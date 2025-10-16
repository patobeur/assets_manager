<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

// --- All data fetching for the dashboard is done here ---

// Fetch user's first name for the welcome message
$stmt = $pdo->prepare("SELECT first_name FROM am_users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Main stats cards
$student_count = $pdo->query("SELECT count(*) FROM am_students")->fetchColumn();
$material_count = $pdo->query("SELECT count(*) FROM am_materials")->fetchColumn();
$loaned_count = $pdo->query("SELECT count(*) FROM am_loans WHERE return_date IS NULL")->fetchColumn();

// Sections
$total_sections_stmt = $pdo->query("SELECT COUNT(*) FROM am_sections");
$total_sections = $total_sections_stmt->fetchColumn();
$used_sections_stmt = $pdo->query("SELECT COUNT(DISTINCT section_id) FROM am_students WHERE section_id IS NOT NULL");
$used_sections = $used_sections_stmt->fetchColumn();

// Promos
$total_promos_stmt = $pdo->query("SELECT COUNT(*) FROM am_promos");
$total_promos = $total_promos_stmt->fetchColumn();
$used_promos_stmt = $pdo->query("SELECT COUNT(DISTINCT promo_id) FROM am_students WHERE promo_id IS NOT NULL");
$used_promos = $used_promos_stmt->fetchColumn();

// Top 3 most loaned materials
$most_loaned_materials_stmt = $pdo->query("
    SELECT m.name, COUNT(l.material_id) AS loan_count
    FROM am_loans l
    JOIN am_materials m ON l.material_id = m.id
    GROUP BY l.material_id, m.name
    ORDER BY loan_count DESC
    LIMIT 3
");
$most_loaned_materials = $most_loaned_materials_stmt->fetchAll(PDO::FETCH_ASSOC);

// Top 5 students by loan count
$top_students_by_loans_stmt = $pdo->query("
    SELECT s.first_name, s.last_name, COUNT(l.student_id) AS loan_count
    FROM am_loans l
    JOIN am_students s ON l.student_id = s.id
    GROUP BY l.student_id, s.first_name, s.last_name
    ORDER BY loan_count DESC
    LIMIT 5
");
$top_students_by_loans = $top_students_by_loans_stmt->fetchAll(PDO::FETCH_ASSOC);

// Top 5 students by loan duration
$top_students_by_duration_stmt = $pdo->query("
    SELECT
        s.first_name,
        s.last_name,
        SUM(TIMESTAMPDIFF(SECOND, l.loan_date, COALESCE(l.return_date, NOW()))) AS total_duration_seconds
    FROM am_loans l
    JOIN am_students s ON l.student_id = s.id
    GROUP BY l.student_id, s.first_name, s.last_name
    ORDER BY total_duration_seconds DESC
    LIMIT 5
");
$top_students_by_duration = $top_students_by_duration_stmt->fetchAll(PDO::FETCH_ASSOC);

// Currently loaned materials
$current_loans = [];
if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'agent') {
    $stmt = $pdo->query("
        SELECT l.loan_date, s.first_name AS student_first_name, s.last_name AS student_last_name, m.name AS material_name
        FROM am_loans l
        JOIN am_students s ON l.student_id = s.id
        JOIN am_materials m ON l.material_id = m.id
        WHERE l.return_date IS NULL
        ORDER BY l.loan_date ASC
    ");
    $current_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once CONFIG_PATH . '/templates/dashboard.php';