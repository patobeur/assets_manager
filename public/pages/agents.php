<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ?page=dashboard');
    exit;
}

// Handle POST actions (create, edit) and GET actions (delete) before any HTML is output
if (($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'create' || $action === 'edit')) || $action === 'delete') {
    if ($action === 'delete') {
        $id = intval($_GET['id']);
        $stmt = $pdo->prepare("DELETE FROM am_users WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_message'] = t('agent_deleted');
    } else { // POST request for create or edit
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = t('invalid_agent_email');
            header('Location: ?page=agents&action=' . $action . (isset($_POST['id']) ? '&id=' . $_POST['id'] : ''));
            exit;
        }

        if ($action === 'create') {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO am_users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'agent')");
            $stmt->execute([$first_name, $last_name, $email, $password]);
            $_SESSION['success_message'] = t('agent_created');
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
            $_SESSION['success_message'] = t('agent_updated');
        }
    }
    header('Location: ?page=agents');
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