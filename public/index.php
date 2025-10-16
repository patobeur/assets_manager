<?php
session_start();

// Initialize the language system
require_once 'language_init.php';

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

// --- Global Actions Handler ---
// These actions redirect and exit, so they must be handled before any HTML output.

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
            if ($_SESSION['user_role'] !== 'admin') {
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


// --- Page Loading ---

// Include the main header
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

// Whitelist of allowed pages
$allowedPages = [
    'dashboard',
    'student',
    'material',
    'students',
    'materials',
    'agents',
    'promos',
    'sections',
    'loans',
    'returns',
    'history',
    'hydration',
    'terms',
    'privacy',
    'barecode' // Note: 'barecode' from URL maps to 'barcode.php' file
];

// Sanitize the page name to prevent directory traversal attacks
$page_name = basename($page);

// Check if the page is in the whitelist
if (in_array($page_name, $allowedPages)) {
    // Special case for 'barecode' URL mapping to 'barcode.php'
    if ($page_name === 'barecode') {
        $page_name = 'barcode';
    }
    $page_file = __DIR__ . '/pages/' . $page_name . '.php';

    if (file_exists($page_file)) {
        require $page_file;
    } else {
        // If file does not exist, load dashboard
        require __DIR__ . '/pages/dashboard.php';
    }
} else {
    // If page is not in whitelist, load dashboard
    require __DIR__ . '/pages/dashboard.php';
}

// Include the main footer
require_once CONFIG_PATH . '/templates/footer.php';