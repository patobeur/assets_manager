<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

// Handle POST actions (create, edit) and GET actions (delete, toggle_status) before any HTML is output
if (($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'create' || $action === 'edit')) || in_array($action, ['delete', 'toggle_status'])) {
    // ... (action handling logic remains the same)
    if ($action === 'toggle_status') {
        $id = intval($_GET['id']);
        $stmt = $pdo->prepare("SELECT status FROM am_students WHERE id = ?");
        $stmt->execute([$id]);
        $current_status = $stmt->fetchColumn();
        $new_status = $current_status == 1 ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE am_students SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        $_SESSION['success_message'] = t('student_status_updated');
    } elseif ($action === 'delete') {
        $id = intval($_GET['id']);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM am_loans WHERE student_id = ? AND return_date IS NULL");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error_message'] = t('student_delete_error_loans');
        } else {
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
}

// Default action to list if not specified or unknown
if (!in_array($action, ['list', 'create', 'edit'])) {
    $action = 'list';
}

// Fetch data for the views
$students = [];
$promos = [];
$sections = [];

if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT s.*, p.title as promo_name, sec.title as section_name
        FROM am_students s
        LEFT JOIN am_promos p ON s.promo_id = p.id
        LEFT JOIN am_sections sec ON s.section_id = sec.id
        ORDER BY s.last_name, s.first_name
    ");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($action === 'create' || $action === 'edit') {
    // Fetch promos and sections for the form
    $promos_stmt = $pdo->query("SELECT * FROM am_promos ORDER BY title");
    $promos = $promos_stmt->fetchAll(PDO::FETCH_ASSOC);

    $sections_stmt = $pdo->query("SELECT * FROM am_sections ORDER BY title");
    $sections = $sections_stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Display the appropriate template
switch ($action) {
    case 'create':
    case 'edit':
        require_once CONFIG_PATH . '/templates/student_form.php';
        break;
    case 'list':
    default:
        require_once CONFIG_PATH . '/templates/students.php';
        break;
}