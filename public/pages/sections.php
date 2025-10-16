<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ?page=dashboard');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'create' || $action === 'edit')) || $action === 'delete') {
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

// Default action to list if not specified or unknown
if (!in_array($action, ['list', 'create', 'edit'])) {
    $action = 'list';
}

// Fetch data for the views
$sections = [];
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM am_sections ORDER BY title");
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Display the appropriate template
switch ($action) {
    case 'create':
    case 'edit':
        require_once CONFIG_PATH . '/templates/section_form.php';
        break;
    case 'list':
    default:
        require_once CONFIG_PATH . '/templates/sections.php';
        break;
}