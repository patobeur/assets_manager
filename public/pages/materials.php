<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

// Handle POST actions (create, edit) and GET actions (delete) before any HTML is output
if (($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'create' || $action === 'edit')) || $action === 'delete') {
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
}

// The rest of the page logic for displaying HTML
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