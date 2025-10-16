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