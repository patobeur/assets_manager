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
require_once '../config_assets_manager/templates/header.php';

$db = new Database();
$pdo = $db->getConnection();

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'dashboard':
        require_once '../config_assets_manager/templates/dashboard.php';
        break;
    case 'students':
        $action = $_GET['action'] ?? 'list';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, barcode) VALUES (?, ?, ?)");
                $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['barcode']]);
            } elseif ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, barcode = ? WHERE id = ?");
                $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['barcode'], $_POST['id']]);
            }
            header('Location: ?page=students');
            exit;
        }

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            header('Location: ?page=students');
            exit;
        }

        switch ($action) {
            case 'list':
                require_once '../config_assets_manager/templates/students.php';
                break;
            case 'create':
            case 'edit':
                require_once '../config_assets_manager/templates/student_form.php';
                break;
            default:
                require_once '../config_assets_manager/templates/students.php';
                break;
        }
        break;
    case 'materials':
        $action = $_GET['action'] ?? 'list';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO materials (name, description, status, barcode) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_POST['name'], $_POST['description'], $_POST['status'], $_POST['barcode']]);
            } elseif ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE materials SET name = ?, description = ?, status = ?, barcode = ? WHERE id = ?");
                $stmt->execute([$_POST['name'], $_POST['description'], $_POST['status'], $_POST['barcode'], $_POST['id']]);
            }
            header('Location: ?page=materials');
            exit;
        }

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            header('Location: ?page=materials');
            exit;
        }

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
        $action = $_GET['action'] ?? 'list';

        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: ?page=dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $email = $_POST['email'];

            if ($action === 'create') {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'agent')");
                $stmt->execute([$first_name, $last_name, $email, $password]);
            } elseif ($action === 'edit') {
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->execute([$first_name, $last_name, $email, $password, $_POST['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$first_name, $last_name, $email, $_POST['id']]);
                }
            }
            header('Location: ?page=agents');
            exit;
        }

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            header('Location: ?page=agents');
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
            $stmt = $pdo->prepare("SELECT id FROM students WHERE barcode = ?");
            $stmt->execute([$student_barcode]);
            $student = $stmt->fetch();

            // Get material id
            $stmt = $pdo->prepare("SELECT id, status FROM materials WHERE barcode = ?");
            $stmt->execute([$material_barcode]);
            $material = $stmt->fetch();

            if ($student && $material && $material['status'] === 'available') {
                // Create loan
                $stmt = $pdo->prepare("INSERT INTO loans (student_id, material_id, loan_date, loan_user_id) VALUES (?, ?, NOW(), ?)");
                $stmt->execute([$student['id'], $material['id'], $_SESSION['user_id']]);
                $loan_id = $pdo->lastInsertId();

                // Update material status
                $stmt = $pdo->prepare("UPDATE materials SET status = 'loaned' WHERE id = ?");
                $stmt->execute([$material['id']]);

                // Get student info
                $stmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
                $stmt->execute([$student['id']]);
                $student_info = $stmt->fetch();

                // Get material info
                $stmt = $pdo->prepare("SELECT name FROM materials WHERE id = ?");
                $stmt->execute([$material['id']]);
                $material_info = $stmt->fetch();

                // Get loan info
                $stmt = $pdo->prepare("SELECT loan_date FROM loans WHERE id = ?");
                $stmt->execute([$loan_id]);
                $loan_info = $stmt->fetch();

                $success = "Le matériel \"{$material_info['name']}\" a été emprunté par {$student_info['first_name']} {$student_info['last_name']} le " . date('d/m/Y à H:i', strtotime($loan_info['loan_date'])) . ".";

                // Get student's other loaned materials
                $stmt = $pdo->prepare("
                    SELECT m.name
                    FROM loans l
                    JOIN materials m ON l.material_id = m.id
                    WHERE l.student_id = ? AND l.return_date IS NULL
                ");
                $stmt->execute([$student['id']]);
                $other_materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get student's loan history
                $stmt = $pdo->prepare("
                    SELECT m.name, l.loan_date, l.return_date
                    FROM loans l
                    JOIN materials m ON l.material_id = m.id
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
            $stmt = $pdo->prepare("SELECT id FROM materials WHERE barcode = ?");
            $stmt->execute([$material_barcode]);
            $material = $stmt->fetch();

            if ($material) {
                // Find the active loan for this material
                $stmt = $pdo->prepare("SELECT id, student_id, loan_date FROM loans WHERE material_id = ? AND return_date IS NULL");
                $stmt->execute([$material['id']]);
                $loan = $stmt->fetch();

                if ($loan) {
                    // Update loan
                    $stmt = $pdo->prepare("UPDATE loans SET return_date = NOW(), return_user_id = ? WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id'], $loan['id']]);

                    // Update material status
                    $stmt = $pdo->prepare("UPDATE materials SET status = 'available' WHERE id = ?");
                    $stmt->execute([$material['id']]);

                    $success = "Le matériel a été retourné avec succès !";

                    // Get student info
                    $stmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
                    $stmt->execute([$loan['student_id']]);
                    $student_info = $stmt->fetch();

                    // Get loan info
                    $stmt = $pdo->prepare("SELECT loan_date, return_date FROM loans WHERE id = ?");
                    $stmt->execute([$loan['id']]);
                    $returned_loan = $stmt->fetch();

                    // Calculate loan duration
                    $loan_date = new DateTime($returned_loan['loan_date']);
                    $return_date = new DateTime($returned_loan['return_date']);
                    $loan_duration = $loan_date->diff($return_date)->format('%a jours, %h heures et %i minutes');

                    // Get student's other loaned materials
                    $stmt = $pdo->prepare("
                        SELECT m.name
                        FROM loans l
                        JOIN materials m ON l.material_id = m.id
                        WHERE l.student_id = ? AND l.return_date IS NULL
                    ");
                    $stmt->execute([$loan['student_id']]);
                    $other_materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Get student's loan history
                    $stmt = $pdo->prepare("
                        SELECT m.name, l.loan_date, l.return_date
                        FROM loans l
                        JOIN materials m ON l.material_id = m.id
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
        $action = $_GET['action'] ?? 'list';

        if ($action === 'export') {
            $sql = "
                SELECT
                    CONCAT(students.first_name, ' ', students.last_name) as student_name,
                    materials.name as material_name,
                    loans.loan_date,
                    CONCAT(loan_user.first_name, ' ', loan_user.last_name) as loan_user_name,
                    loans.return_date,
                    CONCAT(return_user.first_name, ' ', return_user.last_name) as return_user_name
                FROM loans
                JOIN students ON loans.student_id = students.id
                JOIN materials ON loans.material_id = materials.id
                LEFT JOIN users AS loan_user ON loans.loan_user_id = loan_user.id
                LEFT JOIN users AS return_user ON loans.return_user_id = return_user.id
                ORDER BY loans.loan_date DESC
            ";
            $stmt = $pdo->query($sql);
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="history.csv"');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['Étudiant', 'Matériel', 'Date d\'emprunt', 'Agent d\'emprunt', 'Date de retour', 'Agent de retour']);

            foreach ($loans as $loan) {
                fputcsv($output, $loan);
            }

            fclose($output);
            exit;
        }

        require_once '../config_assets_manager/templates/history.php';
        break;
    default:
        require_once '../config_assets_manager/templates/dashboard.php';
        break;
}


require_once '../config_assets_manager/templates/footer.php';
