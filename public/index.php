<?php

// Initialize the language system, which also handles session security
require_once 'language_init.php';

session_start();

// Define a constant to grant access to the bootstrap file.
define('APP_LOADED', true);

// Load the bootstrap file to get the configuration path.
if (!file_exists('bootstrap.php')) {
	// If bootstrap exists, but not install.php, redirect to a maintenance page.
	if (!file_exists('install.php')) {
		header('Location: maintenance.php');
		exit;
	}
	// Otherwise, redirect to the installation page.
	header('Location: install.php');
	exit;
}
require_once 'bootstrap.php';

// Now, use the CONFIG_PATH to load the actual configuration and database files.
require_once CONFIG_PATH . '/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
	header('Location: login.php');
	exit;
}

require_once CONFIG_PATH . '/Database.php';

$db = new Database();
$pdo = $db->getConnection();

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'list';

// Handle CSV import
if ($action === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
		$csvFile = $_FILES['csv_file']['tmp_name'];

		if (($handle = fopen($csvFile, "r")) !== FALSE) {
			// BOM detection and removal
			$bom = "\xef\xbb\xbf";
			if (fgets($handle, 4) !== $bom) {
				rewind($handle);
			}
			$pdo->beginTransaction();
			try {
				// Skip header row
				fgetcsv($handle, 1000, ",");

				if ($page === 'students') {
					// Prepare statements for getting promo and section IDs
					$promo_stmt = $pdo->prepare("SELECT id FROM am_promos WHERE title = ?");
					$section_stmt = $pdo->prepare("SELECT id FROM am_sections WHERE title = ?");

					$student_stmt = $pdo->prepare("INSERT INTO am_students (first_name, last_name, email, barcode, promo_id, section_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");

					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						// CSV columns: first_name, last_name, email, promo_name, section_name, barcode, categorie
						$first_name = $data[0];
						$last_name = $data[1];
						$email = $data[2];
						$promo_name = $data[3];
						$section_name = $data[4];
						$barcode = $data[5];
						$status = $data[6];

						// Get promo ID
						$promo_stmt->execute([$promo_name]);
						$promo_id = $promo_stmt->fetchColumn() ?: null;

						// Get section ID
						$section_stmt->execute([$section_name]);
						$section_id = $section_stmt->fetchColumn() ?: null;

						$student_stmt->execute([$first_name, $last_name, $email, $barcode, $promo_id, $section_id, $status]);
					}
				} elseif ($page === 'materials') {
					// Helper function to clean strings
					function clean_string($str)
					{
						// Remove non-printable characters and trim whitespace
						return trim(preg_replace('/[[:^print:]]/', '', $str));
					}
					// Fetch all valid category IDs into an array for efficient lookup.
					$valid_category_ids_stmt = $pdo->query("SELECT id FROM am_materials_categories");
					$valid_category_ids = $valid_category_ids_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

					// Fetch all material statuses into an associative array for mapping.
					$statuses_stmt = $pdo->query("SELECT title, id FROM am_materials_status");
					$db_statuses = $statuses_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

					// Create a comprehensive map for all possible CSV values.
					$status_map = array_change_key_case($db_statuses, CASE_LOWER); // 'available' => 1, etc.
					$status_map['disponible'] = $status_map['available'] ?? 1;
					$status_map['emprunté'] = $status_map['loaned'] ?? 2;
					$status_map['en maintenance'] = $status_map['maintenance'] ?? 3;


					$material_stmt = $pdo->prepare("INSERT INTO am_materials (name, description, barcode, material_categories_id, material_status_id) VALUES (?, ?, ?, ?, ?)");

					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

						// Clean all data
						$name = clean_string($data[0]);
						$description = clean_string($data[1]);
						$status_from_csv = strtolower(clean_string($data[2])); // Standardize to lowercase
						$barcode = clean_string($data[3]);
						$category_id_from_csv = filter_var(clean_string($data[4]), FILTER_VALIDATE_INT);

						// Map status to ID, default to 1 ('available') if not found
						$status_id = $status_map[$status_from_csv] ?? 1;

						// Check if the provided category ID is valid. If not, default to 1.
						$final_category_id = in_array($category_id_from_csv, $valid_category_ids) ? $category_id_from_csv : 1;

						$material_stmt->execute([$name, $description, $barcode, $final_category_id, $status_id]);
					}
				}

				$pdo->commit();
				$_SESSION['success_message'] = t('import_success');
			} catch (Exception $e) {
				$pdo->rollBack();
				$_SESSION['error_message'] = str_replace('{error_message}', $e->getMessage(), t('import_error'));
			}
			fclose($handle);
		} else {
			$_SESSION['error_message'] = t('csv_open_error');
		}
	} else {
		$_SESSION['error_message'] = t('no_file_uploaded');
	}
	header('Location: ?page=' . $page);
	exit;
}

// Handle all CSV exports before any HTML output
if ($action === 'export') {
	$items = [];
	$filename = 'export.csv';
	$headers = [];

	switch ($page) {
		case 'students':
			$stmt = $pdo->query("
                SELECT s.first_name, s.last_name, s.email, p.title as promo_name, sec.title as section_name, s.barcode, s.status
                FROM am_students s
                LEFT JOIN am_promos p ON s.promo_id = p.id
                LEFT JOIN am_sections sec ON s.section_id = sec.id
                ORDER BY s.last_name, s.first_name
            ");
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$filename = 'etudiants.csv';
			$headers = ['Prénom', 'Nom', 'Email', 'Promo', 'Section', 'Code-barres', 'Status_Id'];
			break;

		case 'materials':
			$stmt = $pdo->query("
                SELECT m.name, m.description, ms.title as status, m.barcode, mc.title as category_name
                FROM am_materials m
                JOIN am_materials_status ms ON m.material_status_id = ms.id
                JOIN am_materials_categories mc ON m.material_categories_id = mc.id
                ORDER BY m.name
            ");
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$filename = 'materiels.csv';
			$headers = ['Nom', 'Description', 'Statut', 'Code-barres', 'Catégorie'];
			break;

		case 'agents':
			if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
				die(t('unauthorized_access', 'Accès non autorisé.'));
			}
			$stmt = $pdo->query("SELECT id, first_name, last_name, email, role FROM am_users WHERE role = 'agent' ORDER BY last_name, first_name");
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$filename = 'agents.csv';
			$headers = ['ID', 'Prénom', 'Nom', 'Email', 'Rôle'];
			break;

		case 'history':
			$sql = "
				SELECT
					CONCAT(am_students.first_name, ' ', am_students.last_name) as student_name,
					am_materials.name as material_name,
					am_loans.loan_date,
					CONCAT(loan_user.first_name, ' ', loan_user.last_name) as loan_user_name,
					am_loans.return_date,
					CONCAT(return_user.first_name, ' ', return_user.last_name) as return_user_name
				FROM am_loans
				JOIN am_students ON am_loans.student_id = am_students.id
				JOIN am_materials ON am_loans.material_id = am_materials.id
				LEFT JOIN am_users AS loan_user ON am_loans.loan_user_id = loan_user.id
				LEFT JOIN am_users AS return_user ON am_loans.return_user_id = return_user.id
				ORDER BY am_loans.loan_date DESC
			";
			$stmt = $pdo->query($sql);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$filename = 'history.csv';
			$headers = ['Étudiant', 'Matériel', 'Date d\'emprunt', 'Agent d\'emprunt', 'Date de retour', 'Agent de retour'];
			break;
	}

	if (!empty($items)) {
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		$output = fopen('php://output', 'w');

		// Handle headers
		fputcsv($output, $headers);

		// Handle rows
		if ($page === 'materials') {
			foreach ($items as $item) {
				// Manually build the CSV line to ensure order and avoid quotes
				$line = [
					$item['name'],
					$item['description'],
					$item['status'],
					$item['barcode'],
					$item['material_categories_id']
				];
				echo implode(',', $line) . "\n";
			}
		} else {
			foreach ($items as $item) {
				fputcsv($output, $item);
			}
		}

		fclose($output);
		exit;
	}
}

// Handle POST actions (create, edit) and GET actions (delete, toggle_status) before any HTML is output
if (($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'create' || $action === 'edit')) || in_array($action, ['delete', 'toggle_status'])) {
	switch ($page) {
		case 'students':
			if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
				header('Location: ?page=dashboard');
				exit;
			}
			if ($action === 'toggle_status') {
				$id = intval($_GET['id']);
				// First, check the current status
				$stmt = $pdo->prepare("SELECT status FROM am_students WHERE id = ?");
				$stmt->execute([$id]);
				$current_status = $stmt->fetchColumn();

				// Toggle the status
				$new_status = $current_status == 1 ? 0 : 1;
				$stmt = $pdo->prepare("UPDATE am_students SET status = ? WHERE id = ?");
				$stmt->execute([$new_status, $id]);
				$_SESSION['success_message'] = t('student_status_updated');
			} elseif ($action === 'delete') {
				$id = intval($_GET['id']);
				// Check for active loans
				$stmt = $pdo->prepare("SELECT COUNT(*) FROM am_loans WHERE student_id = ? AND return_date IS NULL");
				$stmt->execute([$id]);
				if ($stmt->fetchColumn() > 0) {
					$_SESSION['error_message'] = t('student_delete_error_loans');
				} else {
					// Only delete if status is 0 (inactive)
					$stmt = $pdo->prepare("DELETE FROM am_students WHERE id = ? AND status = 0");
					$stmt->execute([$id]);
					if ($stmt->rowCount() > 0) {
						$_SESSION['success_message'] = t('student_deleted');
					} else {
						$_SESSION['error_message'] = t('student_delete_error_active');
					}
				}
			} else { // POST request for create or edit
				$first_name = $_POST['first_name'];
				$last_name = $_POST['last_name'];
				$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
				$barcode = $_POST['barcode'];
				$promo_id = !empty($_POST['promo_id']) ? intval($_POST['promo_id']) : null;
				$section_id = !empty($_POST['section_id']) ? intval($_POST['section_id']) : null;
				$status = isset($_POST['status']) ? 1 : 0;

				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$_SESSION['error_message'] = t('invalid_student_email');
					header('Location: ?page=students&action=' . $action . (isset($_POST['id']) ? '&id=' . $_POST['id'] : ''));
					exit;
				}

				if ($action === 'create') {
					$stmt = $pdo->prepare("INSERT INTO am_students (first_name, last_name, email, barcode, promo_id, section_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
					$stmt->execute([$first_name, $last_name, $email, $barcode, $promo_id, $section_id, $status]);
					$_SESSION['success_message'] = t('student_created');
				} elseif ($action === 'edit') {
					$id = intval($_POST['id']);
					$stmt = $pdo->prepare("UPDATE am_students SET first_name = ?, last_name = ?, email = ?, barcode = ?, promo_id = ?, section_id = ?, status = ? WHERE id = ?");
					$stmt->execute([$first_name, $last_name, $email, $barcode, $promo_id, $section_id, $status, $id]);
					$_SESSION['success_message'] = t('student_updated');
				}
			}
			header('Location: ?page=students');
			exit;

		case 'materials':
			if ($action === 'delete') {
				$id = intval($_GET['id']);
				// Check material status by joining with the status table
				$stmt = $pdo->prepare("
                    SELECT ms.title
                    FROM am_materials m
                    JOIN am_materials_status ms ON m.material_status_id = ms.id
                    WHERE m.id = ?
                ");
				$stmt->execute([$id]);
				$status_title = $stmt->fetchColumn();

				if ($status_title !== 'available') {
					$_SESSION['error_message'] = str_replace('{status}', htmlspecialchars($status_title), t('material_delete_error_status'));
				} else {
					$stmt = $pdo->prepare("DELETE FROM am_materials WHERE id = ?");
					$stmt->execute([$id]);
					$_SESSION['success_message'] = t('material_deleted');
				}
			} else { // POST request for create or edit
				$name = $_POST['name'];
				$description = $_POST['description'];
				$barcode = $_POST['barcode'];
				$material_categories_id = $_POST['material_categories_id'];
				$material_status_id = $_POST['material_status_id']; // New field

				if ($action === 'create') {
					$stmt = $pdo->prepare("INSERT INTO am_materials (name, description, barcode, material_categories_id, material_status_id) VALUES (?, ?, ?, ?, ?)");
					$stmt->execute([$name, $description, $barcode, $material_categories_id, $material_status_id]);
					$_SESSION['success_message'] = t('material_created');
				} elseif ($action === 'edit') {
					$id = intval($_POST['id']);
					$stmt = $pdo->prepare("UPDATE am_materials SET name = ?, description = ?, barcode = ?, material_categories_id = ?, material_status_id = ? WHERE id = ?");
					$stmt->execute([$name, $description, $barcode, $material_categories_id, $material_status_id, $id]);
					$_SESSION['success_message'] = t('material_updated');
				}
			}
			header('Location: ?page=materials');
			exit;

		case 'agents':
			if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
				header('Location: ?page=dashboard');
				exit;
			}
			if ($action === 'toggle_status') {
				$id = intval($_GET['id']);
				if ($id === $_SESSION['user_id']) {
					$_SESSION['error_message'] = t('cannot_change_own_status');
					header('Location: ?page=agents');
					exit;
				}
				$stmt = $pdo->prepare("SELECT status FROM am_users WHERE id = ?");
				$stmt->execute([$id]);
				$current_status = $stmt->fetchColumn();
				$new_status = $current_status == 1 ? 0 : 1;
				$stmt = $pdo->prepare("UPDATE am_users SET status = ? WHERE id = ?");
				$stmt->execute([$new_status, $id]);
				$_SESSION['success_message'] = t('agent_status_updated');
			} elseif ($action === 'delete') {
				$id = intval($_GET['id']);
				if ($id === $_SESSION['user_id']) {
					$_SESSION['error_message'] = t('cannot_delete_own_account');
					header('Location: ?page=agents');
					exit;
				}
				$stmt = $pdo->prepare("DELETE FROM am_users WHERE id = ? AND status = 0");
				$stmt->execute([$id]);
				if ($stmt->rowCount() > 0) {
					$_SESSION['success_message'] = t('agent_deleted');
				} else {
					$_SESSION['error_message'] = t('agent_delete_error_active');
				}
			} else { // POST request for create or edit
				$first_name = $_POST['first_name'];
				$last_name = $_POST['last_name'];
				$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
				$role = $_POST['role'] ?? 'agent';

				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$_SESSION['error_message'] = t('invalid_agent_email');
					header('Location: ?page=agents&action=' . $action . (isset($_POST['id']) ? '&id=' . $_POST['id'] : ''));
					exit;
				}

				if ($action === 'create') {
					$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
					$stmt = $pdo->prepare("INSERT INTO am_users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
					$stmt->execute([$first_name, $last_name, $email, $password, $role]);
					$_SESSION['success_message'] = t('agent_created');
				} elseif ($action === 'edit') {
					$id = intval($_POST['id']);
					$sql = "UPDATE am_users SET first_name = ?, last_name = ?, email = ?";
					$params = [$first_name, $last_name, $email];
					if (!empty($_POST['password'])) {
						$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
						$sql .= ", password = ?";
						$params[] = $password;
					}
					if ($_SESSION['user_role'] === 'adminsys') {
						$sql .= ", role = ?";
						$params[] = $role;
					}
					$sql .= " WHERE id = ?";
					$params[] = $id;

					$stmt = $pdo->prepare($sql);
					$stmt->execute($params);

					$_SESSION['success_message'] = t('agent_updated');
				}
			}
			header('Location: ?page=agents');
			exit;

		case 'promos':
			if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
				header('Location: ?page=dashboard');
				exit;
			}

			if ($action === 'delete') {
				$id = intval($_GET['id']);
				$stmt = $pdo->prepare("DELETE FROM am_promos WHERE id = ?");
				$stmt->execute([$id]);
				$_SESSION['success_message'] = t('promo_deleted');
			} else { // POST request for create or edit
				$title = $_POST['title'];

				if ($action === 'create') {
					$stmt = $pdo->prepare("INSERT INTO am_promos (title) VALUES (?)");
					$stmt->execute([$title]);
					$_SESSION['success_message'] = t('promo_created');
				} elseif ($action === 'edit') {
					$id = intval($_POST['id']);
					$stmt = $pdo->prepare("UPDATE am_promos SET title = ? WHERE id = ?");
					$stmt->execute([$title, $id]);
					$_SESSION['success_message'] = t('promo_updated');
				}
			}
			header('Location: ?page=promos');
			exit;

		case 'sections':
			if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
				header('Location: ?page=dashboard');
				exit;
			}

			if ($action === 'delete') {
				$id = intval($_GET['id']);
				$stmt = $pdo->prepare("DELETE FROM am_sections WHERE id = ?");
				$stmt->execute([$id]);
				$_SESSION['success_message'] = t('section_deleted');
			} else { // POST request for create or edit
				$title = $_POST['title'];

				if ($action === 'create') {
					$stmt = $pdo->prepare("INSERT INTO am_sections (title) VALUES (?)");
					$stmt->execute([$title]);
					$_SESSION['success_message'] = t('section_created');
				} elseif ($action === 'edit') {
					$id = intval($_POST['id']);
					$stmt = $pdo->prepare("UPDATE am_sections SET title = ? WHERE id = ?");
					$stmt->execute([$title, $id]);
					$_SESSION['success_message'] = t('section_updated');
				}
			}
			header('Location: ?page=sections');
			exit;
	}
}

// If we reach here, it's a normal page view, so we include the header.
require_once CONFIG_PATH . '/templates/header.php';

// Display success/error messages
if (isset($_SESSION['success_message'])) {
	echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">';
	echo '<strong class="font-bold">' . t('success', 'Succès !') . '</strong>';
	echo '<span class="block sm:inline">' . htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') . '</span>';
	echo '</div>';
	unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
	echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
	echo '<strong class="font-bold">' . t('error', 'Erreur !') . '</strong>';
	echo '<span class="block sm:inline">' . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') . '</span>';
	echo '</div>';
	unset($_SESSION['error_message']);
}

// The rest of the page logic for displaying HTML
switch ($page) {
	case 'dashboard':
		// --- All data fetching for the dashboard is done here ---

		// Fetch user's first name for the welcome message
		$stmt = $pdo->prepare("SELECT first_name FROM am_users WHERE id = ?");
		$stmt->execute([$_SESSION['user_id']]);
		$user = $stmt->fetch();

		// Main stats cards
		$student_count = $pdo->query("SELECT count(*) FROM am_students")->fetchColumn();
		$material_count = $pdo->query("SELECT count(*) FROM am_materials")->fetchColumn();
		$loaned_count = $pdo->query("SELECT count(*) FROM am_loans WHERE return_date IS NULL")->fetchColumn();

		// Sections
		$total_sections_stmt = $pdo->query("SELECT COUNT(*) FROM am_sections");
		$total_sections = $total_sections_stmt->fetchColumn();
		$used_sections_stmt = $pdo->query("SELECT COUNT(DISTINCT section_id) FROM am_students WHERE section_id IS NOT NULL");
		$used_sections = $used_sections_stmt->fetchColumn();

		// Promos
		$total_promos_stmt = $pdo->query("SELECT COUNT(*) FROM am_promos");
		$total_promos = $total_promos_stmt->fetchColumn();
		$used_promos_stmt = $pdo->query("SELECT COUNT(DISTINCT promo_id) FROM am_students WHERE promo_id IS NOT NULL");
		$used_promos = $used_promos_stmt->fetchColumn();

		// Top 3 most loaned materials
		$most_loaned_materials_stmt = $pdo->query("
            SELECT m.name, COUNT(l.material_id) AS loan_count
            FROM am_loans l
            JOIN am_materials m ON l.material_id = m.id
            GROUP BY l.material_id, m.name
            ORDER BY loan_count DESC
            LIMIT 3
        ");
		$most_loaned_materials = $most_loaned_materials_stmt->fetchAll(PDO::FETCH_ASSOC);

		// Top 5 students by loan count
		$top_students_by_loans_stmt = $pdo->query("
            SELECT s.first_name, s.last_name, COUNT(l.student_id) AS loan_count
            FROM am_loans l
            JOIN am_students s ON l.student_id = s.id
            GROUP BY l.student_id, s.first_name, s.last_name
            ORDER BY loan_count DESC
            LIMIT 5
        ");
		$top_students_by_loans = $top_students_by_loans_stmt->fetchAll(PDO::FETCH_ASSOC);

		// Top 5 students by loan duration
		$top_students_by_duration_stmt = $pdo->query("
            SELECT
                s.first_name,
                s.last_name,
                SUM(TIMESTAMPDIFF(SECOND, l.loan_date, COALESCE(l.return_date, NOW()))) AS total_duration_seconds
            FROM am_loans l
            JOIN am_students s ON l.student_id = s.id
            GROUP BY l.student_id, s.first_name, s.last_name
            ORDER BY total_duration_seconds DESC
            LIMIT 5
        ");
		$top_students_by_duration = $top_students_by_duration_stmt->fetchAll(PDO::FETCH_ASSOC);

		// Currently loaned materials
		$current_loans = [];
		if (in_array($_SESSION['user_role'], ['admin', 'adminsys', 'agent'])) {
			$stmt = $pdo->query("
				SELECT l.loan_date, s.first_name AS student_first_name, s.last_name AS student_last_name, m.name AS material_name
				FROM am_loans l
				JOIN am_students s ON l.student_id = s.id
				JOIN am_materials m ON l.material_id = m.id
				WHERE l.return_date IS NULL
				ORDER BY l.loan_date ASC
			");
			$current_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		require_once CONFIG_PATH . '/templates/dashboard.php';
		break;

	case 'student':
		if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
			header('Location: ?page=dashboard');
			exit;
		}
		if (isset($_GET['id'])) {
			$student_id = intval($_GET['id']);

			// Fetch student information
			$stmt = $pdo->prepare("
				SELECT s.*, p.title as promo_name, sec.title as section_name
				FROM am_students s
				LEFT JOIN am_promos p ON s.promo_id = p.id
				LEFT JOIN am_sections sec ON s.section_id = sec.id
				WHERE s.id = ?
			");
			$stmt->execute([$student_id]);
			$student = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($student) {
				// Fetch student's loan history
				$stmt = $pdo->prepare("
					SELECT
						m.name AS material_name,
						l.loan_date,
						l.return_date
					FROM am_loans l
					JOIN am_materials m ON l.material_id = m.id
					WHERE l.student_id = ?
					ORDER BY l.loan_date DESC
				");
				$stmt->execute([$student_id]);
				$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

				require_once CONFIG_PATH . '/templates/student_details.php';
			} else {
				echo t('student_not_found');
			}
		} else {
			echo t('student_id_not_provided');
		}
		break;

	case 'material':
		if (isset($_GET['id'])) {
			$material_id = intval($_GET['id']);

			// Fetch material information
			$stmt = $pdo->prepare("SELECT * FROM am_materials WHERE id = ?");
			$stmt->execute([$material_id]);
			$material = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($material) {
				// Fetch material's loan history
				$stmt = $pdo->prepare("
						SELECT
							CONCAT(s.first_name, ' ', s.last_name) AS student_name,
							l.loan_date,
							l.return_date
						FROM am_loans l
						JOIN am_students s ON l.student_id = s.id
						WHERE l.material_id = ?
						ORDER BY l.loan_date DESC
					");
				$stmt->execute([$material_id]);
				$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

				require_once CONFIG_PATH . '/templates/material_details.php';
			} else {
				echo t('material_not_found');
			}
		} else {
			echo t('material_id_not_provided');
		}
		break;
	case 'students':
		if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
			header('Location: ?page=dashboard');
			exit;
		}
		switch ($action) {
			case 'list':
				require_once CONFIG_PATH . '/templates/students.php';
				break;
			case 'create':
			case 'edit':
				// Fetch promos and sections for the form
				$promos_stmt = $pdo->query("SELECT * FROM am_promos ORDER BY title");
				$promos = $promos_stmt->fetchAll(PDO::FETCH_ASSOC);

				$sections_stmt = $pdo->query("SELECT * FROM am_sections ORDER BY title");
				$sections = $sections_stmt->fetchAll(PDO::FETCH_ASSOC);

				require_once CONFIG_PATH . '/templates/student_form.php';
				break;
			default:
				require_once CONFIG_PATH . '/templates/students.php';
				break;
		}
		break;
	case 'materials':
		switch ($action) {
			case 'list':
				require_once CONFIG_PATH . '/templates/materials.php';
				break;
			case 'create':
			case 'edit':
				require_once CONFIG_PATH . '/templates/material_form.php';
				break;
			default:
				require_once CONFIG_PATH . '/templates/materials.php';
				break;
		}
		break;
	case 'agents':
		if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
			header('Location: ?page=dashboard');
			exit;
		}

		switch ($action) {
			case 'list':
				require_once CONFIG_PATH . '/templates/agents.php';
				break;
			case 'create':
			case 'edit':
				require_once CONFIG_PATH . '/templates/agent_form.php';
				break;
			default:
				require_once CONFIG_PATH . '/templates/agents.php';
				break;
		}
		break;
	case 'promos':
		if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
			header('Location: ?page=dashboard');
			exit;
		}

		switch ($action) {
			case 'list':
				require_once CONFIG_PATH . '/templates/promos.php';
				break;
			case 'create':
			case 'edit':
				require_once CONFIG_PATH . '/templates/promo_form.php';
				break;
			default:
				require_once CONFIG_PATH . '/templates/promos.php';
				break;
		}
		break;
	case 'sections':
		if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
			header('Location: ?page=dashboard');
			exit;
		}

		switch ($action) {
			case 'list':
				require_once CONFIG_PATH . '/templates/sections.php';
				break;
			case 'create':
			case 'edit':
				require_once CONFIG_PATH . '/templates/section_form.php';
				break;
			default:
				require_once CONFIG_PATH . '/templates/sections.php';
				break;
		}
		break;
	case 'loans':
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$student_barcode = $_POST['student_barcode'];
			$material_barcode = $_POST['material_barcode'];

			// Get student id
			$stmt = $pdo->prepare("SELECT id FROM am_students WHERE barcode = ?");
			$stmt->execute([$student_barcode]);
			$student = $stmt->fetch();

			// Get material id and status title
			$stmt = $pdo->prepare("
                SELECT m.id, ms.title as status_title
                FROM am_materials m
                JOIN am_materials_status ms ON m.material_status_id = ms.id
                WHERE m.barcode = ?
            ");
			$stmt->execute([$material_barcode]);
			$material = $stmt->fetch();

			if ($student && $material && $material['status_title'] === 'available') {
				// Get the ID for 'loaned' status
				$stmt_status = $pdo->prepare("SELECT id FROM am_materials_status WHERE title = 'loaned'");
				$stmt_status->execute();
				$loaned_status_id = $stmt_status->fetchColumn();


				// Create loan
				$stmt = $pdo->prepare("INSERT INTO am_loans (student_id, material_id, loan_date, loan_user_id) VALUES (?, ?, NOW(), ?)");
				$stmt->execute([$student['id'], $material['id'], $_SESSION['user_id']]);
				$loan_id = $pdo->lastInsertId();

				// Update material status
				$stmt = $pdo->prepare("UPDATE am_materials SET material_status_id = ? WHERE id = ?");
				$stmt->execute([$loaned_status_id, $material['id']]);

				// Get student info
				$stmt = $pdo->prepare("SELECT first_name, last_name FROM am_students WHERE id = ?");
				$stmt->execute([$student['id']]);
				$student_info = $stmt->fetch();

				// Get material info
				$stmt = $pdo->prepare("SELECT name FROM am_materials WHERE id = ?");
				$stmt->execute([$material['id']]);
				$material_info = $stmt->fetch();

				// Get loan info
				$stmt = $pdo->prepare("SELECT loan_date FROM am_loans WHERE id = ?");
				$stmt->execute([$loan_id]);
				$loan_info = $stmt->fetch();

				$success = t('loan_success_message', [
					'material_name' => $material_info['name'],
					'student_name' => $student_info['first_name'] . ' ' . $student_info['last_name'],
					'loan_date' => date(t('date_format_long'), strtotime($loan_info['loan_date']))
				]);

				// Get student's other loaned am_materials
				$stmt = $pdo->prepare("
					SELECT m.name, l.loan_date
					FROM am_loans l
					JOIN am_materials m ON l.material_id = m.id
					WHERE l.student_id = ? AND l.return_date IS NULL
				");
				$stmt->execute([$student['id']]);
				$other_materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

				// Get student's loan history
				$stmt = $pdo->prepare("
					SELECT m.name, l.loan_date, l.return_date
					FROM am_loans l
					JOIN am_materials m ON l.material_id = m.id
					WHERE l.student_id = ?
					ORDER BY l.loan_date DESC
					LIMIT 5
				");
				$stmt->execute([$student['id']]);
				$loan_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$error = t('loan_error');
			}
		}
		require_once CONFIG_PATH . '/templates/loans.php';
		break;
	case 'returns':
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$material_barcode = $_POST['material_barcode'];

			// Get material id
			$stmt = $pdo->prepare("SELECT id FROM am_materials WHERE barcode = ?");
			$stmt->execute([$material_barcode]);
			$material = $stmt->fetch();

			if ($material) {
				// Find the active loan for this material
				$stmt = $pdo->prepare("SELECT id, student_id, loan_date FROM am_loans WHERE material_id = ? AND return_date IS NULL");
				$stmt->execute([$material['id']]);
				$loan = $stmt->fetch();

				if ($loan) {
					// Get the ID for 'available' status
					$stmt_status = $pdo->prepare("SELECT id FROM am_materials_status WHERE title = 'available'");
					$stmt_status->execute();
					$available_status_id = $stmt_status->fetchColumn();

					// Update loan
					$stmt = $pdo->prepare("UPDATE am_loans SET return_date = NOW(), return_user_id = ? WHERE id = ?");
					$stmt->execute([$_SESSION['user_id'], $loan['id']]);

					// Update material status
					$stmt = $pdo->prepare("UPDATE am_materials SET material_status_id = ? WHERE id = ?");
					$stmt->execute([$available_status_id, $material['id']]);

					$success = t('return_success_message');

					// Get student info
					$stmt = $pdo->prepare("SELECT first_name, last_name FROM am_students WHERE id = ?");
					$stmt->execute([$loan['student_id']]);
					$student_info = $stmt->fetch();

					// Get loan info
					$stmt = $pdo->prepare("SELECT loan_date, return_date FROM am_loans WHERE id = ?");
					$stmt->execute([$loan['id']]);
					$returned_loan = $stmt->fetch();

					// Calculate loan duration
					$loan_date = new DateTime($returned_loan['loan_date']);
					$return_date = new DateTime($returned_loan['return_date']);
					$loan_duration = $loan_date->diff($return_date)->format('%a jours, %h heures et %i minutes');

					// Get student's other loaned am_materials
					$stmt = $pdo->prepare("
						SELECT m.name
						FROM am_loans l
						JOIN am_materials m ON l.material_id = m.id
						WHERE l.student_id = ? AND l.return_date IS NULL
					");
					$stmt->execute([$loan['student_id']]);
					$other_materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

					// Get student's loan history
					$stmt = $pdo->prepare("
						SELECT m.name, l.loan_date, l.return_date
						FROM am_loans l
						JOIN am_materials m ON l.material_id = m.id
						WHERE l.student_id = ?
						ORDER BY l.loan_date DESC
						LIMIT 5
					");
					$stmt->execute([$loan['student_id']]);
					$loan_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
				} else {
					$error = t('return_error_no_active_loan');
				}
			} else {
				$error = t('return_error_invalid_barcode');
			}
		}
		require_once CONFIG_PATH . '/templates/returns.php';
		break;
	case 'history':
		require_once CONFIG_PATH . '/templates/history.php';
		break;
	case 'hydration':
		if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
			header('Location: ?page=dashboard');
			exit;
		}

		require_once CONFIG_PATH . '/hydration.php';
		$hydration = new Hydration($pdo);
		$action = $_GET['action'] ?? null;

		// Server-side validation
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM am_students WHERE barcode LIKE 'HYDRATION-%'");
		$stmt->execute();
		$isHydrated = $stmt->fetchColumn() > 0;

		if ($action === 'populate') {
			if ($isHydrated) {
				$error = t('hydration_already_exists');
			} else {
				$hydration->populateTables();
				$success = t('hydration_populated');
			}
		} elseif ($action === 'clear') {
			if (!$isHydrated) {
				$error = t('hydration_no_data_to_clear');
			} else {
				$hydration->clearTables();
				$success = t('hydration_cleared');
			}
		}

		require_once CONFIG_PATH . '/templates/hydration.php';
		break;
	case 'terms':
		require_once CONFIG_PATH . '/templates/terms.php';
		break;
	case 'privacy':
		require_once CONFIG_PATH . '/templates/privacy.php';
		break;
	case 'barecode':
		if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
			header('Location: ?page=dashboard');
			exit;
		}

		// Fetch all students with their details
		$stmt_students = $pdo->query("
			SELECT s.first_name, s.last_name, s.barcode, p.title as promo_title, sec.title as section_title
			FROM am_students s
			LEFT JOIN am_promos p ON s.promo_id = p.id
			LEFT JOIN am_sections sec ON s.section_id = sec.id
			WHERE s.barcode IS NOT NULL AND s.barcode != ''
			ORDER BY s.last_name, s.first_name
		");
		$students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

		// Fetch all materials
		$stmt_materials = $pdo->query("
			SELECT name, barcode
			FROM am_materials
			WHERE barcode IS NOT NULL AND barcode != ''
			ORDER BY name
		");
		$materials = $stmt_materials->fetchAll(PDO::FETCH_ASSOC);

		require_once CONFIG_PATH . '/templates/barcode.php';
		break;

	default:
		require_once CONFIG_PATH . '/templates/dashboard.php';
		break;
}

require_once CONFIG_PATH . '/templates/footer.php';
