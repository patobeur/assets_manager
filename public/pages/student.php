<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Fetch student information
    $stmt = $pdo->prepare("
        SELECT s.*, p.title as promo_name, sec.title as section_name
        FROM am_students s
        LEFT JOIN am_promos p ON s.promo_id = p.id
        LEFT JOIN am_sections sec ON s.section_id = sec.id
        WHERE s.id = ?
    ");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        // Fetch student's loan history
        $stmt = $pdo->prepare("
            SELECT
                m.name AS material_name,
                l.loan_date,
                l.return_date
            FROM am_loans l
            JOIN am_materials m ON l.material_id = m.id
            WHERE l.student_id = ?
            ORDER BY l.loan_date DESC
        ");
        $stmt->execute([$student_id]);
        $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once CONFIG_PATH . '/templates/student_details.php';
    } else {
        echo t('student_not_found');
    }
} else {
    echo t('student_id_not_provided');
}