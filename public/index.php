<?php
session_start();

// Check if the config file exists
if (!file_exists('../config_assets_manager/config.php')) {
    if (!file_exists('install.php')) {
        // Redirect to the maintenance page
        header('Location: maintenance.php');
        exit;
    } else {
        // Redirect to the installation page
        header('Location: install.php');
        exit;
    }
    exit;
}

require_once '../config_assets_manager/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config_assets_manager/Database.php';

$db = new Database();
$pdo = $db->getConnection();

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'list';

// Handle CSV import
if ($action === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $csvFile = $_FILES['csv_file']['tmp_name'];

        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            $pdo->beginTransaction();
            try {
                // Skip header row
                fgetcsv($handle, 1000, ",");

                if ($page === 'students') {
                    // Prepare statements for getting promo and section IDs
                    $promo_stmt = $pdo->prepare("SELECT id FROM am_promos WHERE title = ?");
                    $section_stmt = $pdo->prepare("SELECT id FROM am_sections WHERE title = ?");

                    $student_stmt = $pdo->prepare("INSERT INTO am_students (first_name, last_name, email, barcode, promo_id, section_id) VALUES (?, ?, ?, ?, ?, ?)");

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        // CSV columns: first_name, last_name, email, promo_name, section_name, barcode
                        $first_name = $data[0];
                        $last_name = $data[1];
                        $email = $data[2];
                        $promo_name = $data[3];
                        $section_name = $data[4];
                        $barcode = $data[5];

                        // Get promo ID
                        $promo_stmt->execute([$promo_name]);
                        $promo_id = $promo_stmt->fetchColumn() ?: null;

                        // Get section ID
                        $section_stmt->execute([$section_name]);
                        $section_id = $section_stmt->fetchColumn() ?: null;

                        $student_stmt->execute([$first_name, $last_name, $email, $barcode, $promo_id, $section_id]);
                    }
                } elseif ($page === 'materials') {
                    $stmt = $pdo->prepare("INSERT INTO am_materials (name, description, status, barcode) VALUES (?, ?, ?, ?)");
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        // Assuming CSV columns are: name, description, status, barcode
                        $stmt->execute([$data[0], $data[1], $data[2], $data[3]]);
                    }
                }

                $pdo->commit();
                $_SESSION['success_message'] = "Importation réussie.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error_message'] = "Erreur lors de l'importation : " . $e->getMessage();
            }
            fclose($handle);
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'ouverture du fichier CSV.";
        }
    } else {
        $_SESSION['error_message'] = "Aucun fichier n'a été téléversé ou une erreur s'est produite.";
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
                SELECT s.id, s.first_name, s.last_name, s.email, p.title as promo_name, sec.title as section_name, s.barcode
                FROM am_students s
                LEFT JOIN am_promos p ON s.promo_id = p.id
                LEFT JOIN am_sections sec ON s.section_id = sec.id
                ORDER BY s.last_name, s.first_name
            ");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = 'etudiants.csv';
            $headers = ['ID', 'Prénom', 'Nom', 'Email', 'Promo', 'Section', 'Code-barres'];
            break;

        case 'materials':
            $stmt = $pdo->query("SELECT id, name, description, status, barcode FROM am_materials ORDER BY name");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = 'materiels.csv';
            $headers = ['ID', 'Nom', 'Description', 'Statut', 'Code-barres'];
            break;

        case 'agents':
            if ($_SESSION['user_role'] !== 'admin') {
                die('Accès non autorisé.');
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
        fputcsv($output, $headers);

        foreach ($items as $item) {
            fputcsv($output, $item);
        }

        fclose($output);
        exit;
    }
}

// Handle POST actions (create, edit) and GET actions (delete) before any HTML is output
if (($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'create' || $action === 'edit')) || $action === 'delete') {
    switch ($page) {
        case 'students':
            if ($action === 'delete') {
                $id = intval($_GET['id']);
                // Check for active loans
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM am_loans WHERE student_id = ? AND return_date IS NULL");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    $_SESSION['error_message'] = "Impossible de supprimer l'étudiant car il a des prêts en cours.";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM am_students WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['success_message'] = "Étudiant supprimé avec succès.";
                }
            } else { // POST request for create or edit
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $barcode = $_POST['barcode'];
                $promo_id = !empty($_POST['promo_id']) ? intval($_POST['promo_id']) : null;
                $section_id = !empty($_POST['section_id']) ? intval($_POST['section_id']) : null;

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error_message'] = "Adresse email de l'étudiant invalide.";
                    header('Location: ?page=students&action=' . $action . (isset($_POST['id']) ? '&id=' . $_POST['id'] : ''));
                    exit;
                }

                if ($action === 'create') {
                    $stmt = $pdo->prepare("INSERT INTO am_students (first_name, last_name, email, barcode, promo_id, section_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$first_name, $last_name, $email, $barcode, $promo_id, $section_id]);
                    $_SESSION['success_message'] = "Étudiant créé avec succès.";
                } elseif ($action === 'edit') {
                    $id = intval($_POST['id']);
                    $stmt = $pdo->prepare("UPDATE am_students SET first_name = ?, last_name = ?, email = ?, barcode = ?, promo_id = ?, section_id = ? WHERE id = ?");
                    $stmt->execute([$first_name, $last_name, $email, $barcode, $promo_id, $section_id, $id]);
                    $_SESSION['success_message'] = "Étudiant mis à jour avec succès.";
                }
            }
            header('Location: ?page=students');
            exit;

        case 'materials':
            if ($action === 'delete') {
                $id = intval($_GET['id']);
                // Check material status
                $stmt = $pdo->prepare("SELECT status FROM am_materials WHERE id = ?");
                $stmt->execute([$id]);
                $status = $stmt->fetchColumn();

                if ($status !== 'available') {
                    $_SESSION['error_message'] = "Impossible de supprimer le matériel car il n'est pas disponible (statut : " . htmlspecialchars($status) . ").";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM am_materials WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['success_message'] = "Matériel supprimé avec succès.";
                }
            } else { // POST request for create or edit
                $name = $_POST['name'];
                $description = $_POST['description'];
                $status = $_POST['status'];
                $barcode = $_POST['barcode'];

                if ($action === 'create') {
                    $stmt = $pdo->prepare("INSERT INTO am_materials (name, description, status, barcode) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $status, $barcode]);
                    $_SESSION['success_message'] = "Matériel créé avec succès.";
                } elseif ($action === 'edit') {
                    $id = intval($_POST['id']);
                    $stmt = $pdo->prepare("UPDATE am_materials SET name = ?, description = ?, status = ?, barcode = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $status, $barcode, $id]);
                    $_SESSION['success_message'] = "Matériel mis à jour avec succès.";
                }
            }
            header('Location: ?page=materials');
            exit;

        case 'agents':
            if ($_SESSION['user_role'] !== 'admin') {
                header('Location: ?page=dashboard');
                exit;
            }

            if ($action === 'delete') {
                $id = intval($_GET['id']);
                $stmt = $pdo->prepare("DELETE FROM am_users WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success_message'] = "Agent supprimé avec succès.";
            } else { // POST request for create or edit
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error_message'] = "Adresse email invalide.";
                    header('Location: ?page=agents&action=' . $action . (isset($_POST['id']) ? '&id=' . $_POST['id'] : ''));
                    exit;
                }

                if ($action === 'create') {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO am_users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'agent')");
                    $stmt->execute([$first_name, $last_name, $email, $password]);
                    $_SESSION['success_message'] = "Agent créé avec succès.";
                } elseif ($action === 'edit') {
                    $id = intval($_POST['id']);
                    if (!empty($_POST['password'])) {
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE am_users SET first_name = ?, last_name = ?, email = ?, password = ? WHERE id = ?");
                        $stmt->execute([$first_name, $last_name, $email, $password, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE am_users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                        $stmt->execute([$first_name, $last_name, $email, $id]);
                    }
                    $_SESSION['success_message'] = "Agent mis à jour avec succès.";
                }
            }
            header('Location: ?page=agents');
            exit;
    }
}

// If we reach here, it's a normal page view, so we include the header.
require_once '../config_assets_manager/templates/header.php';

// Display success/error messages
if (isset($_SESSION['success_message'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">';
    echo '<strong class="font-bold">Succès !</strong>';
    echo '<span class="block sm:inline">' . htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') . '</span>';
    echo '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
    echo '<strong class="font-bold">Erreur !</strong>';
    echo '<span class="block sm:inline">' . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') . '</span>';
    echo '</div>';
    unset($_SESSION['error_message']);
}

// The rest of the page logic for displaying HTML
switch ($page) {
    case 'dashboard':
        require_once '../config_assets_manager/templates/dashboard.php';
        break;

    case 'student':
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

                require_once '../config_assets_manager/templates/student_details.php';
            } else {
                echo "Étudiant non trouvé.";
            }
        } else {
            echo "ID de l'étudiant non fourni.";
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

                require_once '../config_assets_manager/templates/material_details.php';
            } else {
                echo "Matériel non trouvé.";
            }
        } else {
            echo "ID du matériel non fourni.";
        }
        break;
    case 'students':
        switch ($action) {
            case 'list':
                require_once '../config_assets_manager/templates/students.php';
                break;
            case 'create':
            case 'edit':
                // Fetch promos and sections for the form
                $promos_stmt = $pdo->query("SELECT * FROM am_promos ORDER BY title");
                $promos = $promos_stmt->fetchAll(PDO::FETCH_ASSOC);

                $sections_stmt = $pdo->query("SELECT * FROM am_sections ORDER BY title");
                $sections = $sections_stmt->fetchAll(PDO::FETCH_ASSOC);

                require_once '../config_assets_manager/templates/student_form.php';
                break;
            default:
                require_once '../config_assets_manager/templates/students.php';
                break;
        }
        break;
    case 'materials':
        switch ($action) {
            case 'list':
                require_once '../config_assets_manager/templates/materials.php';
                break;
            case 'create':
            case 'edit':
                require_once '../config_assets_manager/templates/material_form.php';
                break;
            default:
                require_once '../config_assets_manager/templates/materials.php';
                break;
        }
        break;
    case 'agents':
        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: ?page=dashboard');
            exit;
        }

        switch ($action) {
            case 'list':
                require_once '../config_assets_manager/templates/agents.php';
                break;
            case 'create':
            case 'edit':
                require_once '../config_assets_manager/templates/agent_form.php';
                break;
            default:
                require_once '../config_assets_manager/templates/agents.php';
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

            // Get material id
            $stmt = $pdo->prepare("SELECT id, status FROM am_materials WHERE barcode = ?");
            $stmt->execute([$material_barcode]);
            $material = $stmt->fetch();

            if ($student && $material && $material['status'] === 'available') {
                // Create loan
                $stmt = $pdo->prepare("INSERT INTO am_loans (student_id, material_id, loan_date, loan_user_id) VALUES (?, ?, NOW(), ?)");
                $stmt->execute([$student['id'], $material['id'], $_SESSION['user_id']]);
                $loan_id = $pdo->lastInsertId();

                // Update material status
                $stmt = $pdo->prepare("UPDATE am_materials SET status = 'loaned' WHERE id = ?");
                $stmt->execute([$material['id']]);

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

                $success = "Le matériel \"{$material_info['name']}\" a été emprunté par {$student_info['first_name']} {$student_info['last_name']} le " . date('d/m/Y à H:i', strtotime($loan_info['loan_date'])) . ".";

                // Get student's other loaned am_materials
                $stmt = $pdo->prepare("
                    SELECT m.name
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
                $error = "Invalid student or material barcode, or material is not available.";
            }
        }
        require_once '../config_assets_manager/templates/loans.php';
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
                    // Update loan
                    $stmt = $pdo->prepare("UPDATE am_loans SET return_date = NOW(), return_user_id = ? WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id'], $loan['id']]);

                    // Update material status
                    $stmt = $pdo->prepare("UPDATE am_materials SET status = 'available' WHERE id = ?");
                    $stmt->execute([$material['id']]);

                    $success = "Le matériel a été retourné avec succès !";

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
                    $error = "Aucun emprunt actif trouvé pour ce matériel.";
                }
            } else {
                $error = "Code-barres du matériel non valide.";
            }
        }
        require_once '../config_assets_manager/templates/returns.php';
        break;
    case 'history':
        require_once '../config_assets_manager/templates/history.php';
        break;
    case 'hydration':
        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: ?page=dashboard');
            exit;
        }

        require_once '../config_assets_manager/hydration.php';
        $hydration = new Hydration($pdo);
        $action = $_GET['action'] ?? null;

        // Server-side validation
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM am_students WHERE barcode LIKE 'HYDRATION-%'");
        $stmt->execute();
        $isHydrated = $stmt->fetchColumn() > 0;

        if ($action === 'populate') {
            if ($isHydrated) {
                $error = "Les données de démonstration existent déjà.";
            } else {
                $hydration->populateTables();
                $success = "Les données de démonstration ont été ajoutées.";
            }
        } elseif ($action === 'clear') {
            if (!$isHydrated) {
                $error = "Il n'y a pas de données de démonstration à supprimer.";
            } else {
                $hydration->clearTables();
                $success = "Les données de démonstration ont été supprimées.";
            }
        }

        require_once '../config_assets_manager/templates/hydration.php';
        break;
    default:
        require_once '../config_assets_manager/templates/dashboard.php';
        break;
}

require_once '../config_assets_manager/templates/footer.php';
