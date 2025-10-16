<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ?page=dashboard');
    exit;
}

require_once CONFIG_PATH . '/hydration.php';
$hydration = new Hydration($pdo);
$action = $_GET['action'] ?? null;

// Server-side validation to check if data already exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM am_students WHERE barcode LIKE 'HYDRATION-%'");
$stmt->execute();
$isHydrated = $stmt->fetchColumn() > 0;

if ($action === 'populate') {
    if ($isHydrated) {
        $_SESSION['error_message'] = t('hydration_already_exists');
    } else {
        try {
            $hydration->populateTables($_SESSION['user_id']);
            $_SESSION['success_message'] = t('hydration_populated');
        } catch (Exception $e) {
            // Capture and display the actual database error
            $_SESSION['error_message'] = 'Erreur d\'hydratation : ' . $e->getMessage();
        }
    }
    // Redirect back to the hydration page to show the message
    header('Location: ?page=hydration');
    exit;
} elseif ($action === 'clear') {
    if (!$isHydrated) {
        $_SESSION['error_message'] = t('hydration_no_data_to_clear');
    } else {
        try {
            $hydration->clearTables();
            $_SESSION['success_message'] = t('hydration_cleared');
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Erreur de nettoyage : ' . $e->getMessage();
        }
    }
    // Redirect back to the hydration page to show the message
    header('Location: ?page=hydration');
    exit;
}

// The template is now only responsible for displaying the page, not handling actions
require_once CONFIG_PATH . '/templates/hydration.php';