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

// Server-side validation
$stmt = $pdo->prepare("SELECT COUNT(*) FROM am_students WHERE barcode LIKE 'HYDRATION-%'");
$stmt->execute();
$isHydrated = $stmt->fetchColumn() > 0;

if ($action === 'populate') {
    if ($isHydrated) {
        $error = t('hydration_already_exists');
    } else {
        $hydration->populateTables();
        $success = t('hydration_populated');
    }
} elseif ($action === 'clear') {
    if (!$isHydrated) {
        $error = t('hydration_no_data_to_clear');
    } else {
        $hydration->clearTables();
        $success = t('hydration_cleared');
    }
}

require_once CONFIG_PATH . '/templates/hydration.php';