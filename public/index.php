<?php
session_start();

// Check if the config file exists
if (!file_exists('../config/config.php')) {
    // Redirect to the installation page
    header('Location: install.php');
    exit;
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../src/Database.php';
require_once '../templates/header.php';

$db = new Database();
$pdo = $db->getConnection();

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'dashboard':
        require_once '../templates/dashboard.php';
        break;
    case 'students':
        $action = $_GET['action'] ?? 'list';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO students (name, barcode) VALUES (?, ?)");
                $stmt->execute([$_POST['name'], $_POST['barcode']]);
            } elseif ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE students SET name = ?, barcode = ? WHERE id = ?");
                $stmt->execute([$_POST['name'], $_POST['barcode'], $_POST['id']]);
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
                require_once '../templates/students.php';
                break;
            case 'create':
            case 'edit':
                require_once '../templates/student_form.php';
                break;
            default:
                require_once '../templates/students.php';
                break;
        }
        break;
    case 'materials':
        $action = $_GET['action'] ?? 'list';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO materials (name, status, barcode) VALUES (?, ?, ?)");
                $stmt->execute([$_POST['name'], $_POST['status'], $_POST['barcode']]);
            } elseif ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE materials SET name = ?, status = ?, barcode = ? WHERE id = ?");
                $stmt->execute([$_POST['name'], $_POST['status'], $_POST['barcode'], $_POST['id']]);
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
                require_once '../templates/materials.php';
                break;
            case 'create':
            case 'edit':
                require_once '../templates/material_form.php';
                break;
            default:
                require_once '../templates/materials.php';
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
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'agent')");
                $stmt->execute([$email, $password]);
            } elseif ($action === 'edit') {
                $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
                $stmt->execute([$email, $password, $_POST['id']]);
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
                require_once '../templates/agents.php';
                break;
            case 'create':
            case 'edit':
                require_once '../templates/agent_form.php';
                break;
            default:
                require_once '../templates/agents.php';
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
                $stmt = $pdo->prepare("INSERT INTO loans (student_id, material_id, loan_date) VALUES (?, ?, NOW())");
                $stmt->execute([$student['id'], $material['id']]);

                // Update material status
                $stmt = $pdo->prepare("UPDATE materials SET status = 'loaned' WHERE id = ?");
                $stmt->execute([$material['id']]);

                $success = "Material loaned successfully!";
            } else {
                $error = "Invalid student or material barcode, or material is not available.";
            }
        }
        require_once '../templates/loans.php';
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
                $stmt = $pdo->prepare("SELECT id FROM loans WHERE material_id = ? AND return_date IS NULL");
                $stmt->execute([$material['id']]);
                $loan = $stmt->fetch();

                if ($loan) {
                    // Update loan
                    $stmt = $pdo->prepare("UPDATE loans SET return_date = NOW() WHERE id = ?");
                    $stmt->execute([$loan['id']]);

                    // Update material status
                    $stmt = $pdo->prepare("UPDATE materials SET status = 'available' WHERE id = ?");
                    $stmt->execute([$material['id']]);

                    $success = "Material returned successfully!";
                } else {
                    $error = "No active loan found for this material.";
                }
            } else {
                $error = "Invalid material barcode.";
            }
        }
        require_once '../templates/returns.php';
        break;
    case 'history':
        $action = $_GET['action'] ?? 'list';

        if ($action === 'export') {
            $sql = "
                SELECT
                    students.name as student_name,
                    materials.name as material_name,
                    loans.loan_date,
                    loans.return_date
                FROM loans
                JOIN students ON loans.student_id = students.id
                JOIN materials ON loans.material_id = materials.id
                ORDER BY loans.loan_date DESC
            ";
            $stmt = $pdo->query($sql);
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="history.csv"');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['Student', 'Material', 'Loan Date', 'Return Date']);

            foreach ($loans as $loan) {
                fputcsv($output, $loan);
            }

            fclose($output);
            exit;
        }

        require_once '../templates/history.php';
        break;
    default:
        require_once '../templates/dashboard.php';
        break;
}


require_once '../templates/footer.php';