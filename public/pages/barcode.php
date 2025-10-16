<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ?page=dashboard');
    exit;
}

// Fetch all students with their details
$stmt_students = $pdo->query("
    SELECT s.first_name, s.last_name, s.barcode, p.title as promo_title, sec.title as section_title
    FROM am_students s
    LEFT JOIN am_promos p ON s.promo_id = p.id
    LEFT JOIN am_sections sec ON s.section_id = sec.id
    WHERE s.barcode IS NOT NULL AND s.barcode != ''
    ORDER BY s.last_name, s.first_name
");
$students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

// Fetch all materials
$stmt_materials = $pdo->query("
    SELECT name, barcode
    FROM am_materials
    WHERE barcode IS NOT NULL AND barcode != ''
    ORDER BY name
");
$materials = $stmt_materials->fetchAll(PDO::FETCH_ASSOC);

require_once CONFIG_PATH . '/templates/barcode.php';