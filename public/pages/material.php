<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

if (isset($_GET['id'])) {
    $material_id = intval($_GET['id']);

    // Fetch material information
    $stmt = $pdo->prepare("SELECT * FROM am_materials WHERE id = ?");
    $stmt->execute([$material_id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($material) {
        // Fetch material's loan history
        $stmt = $pdo->prepare("
                SELECT
                    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                    l.loan_date,
                    l.return_date
                FROM am_loans l
                JOIN am_students s ON l.student_id = s.id
                WHERE l.material_id = ?
                ORDER BY l.loan_date DESC
            ");
        $stmt->execute([$material_id]);
        $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once CONFIG_PATH . '/templates/material_details.php';
    } else {
        echo t('material_not_found');
    }
} else {
    echo t('material_id_not_provided');
}