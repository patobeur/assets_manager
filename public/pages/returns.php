<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material_barcode = $_POST['material_barcode'];

    // Get material id
    $stmt = $pdo->prepare("SELECT id FROM am_materials WHERE barcode = ?");
    $stmt->execute([$material_barcode]);
    $material = $stmt->fetch();

    if ($material) {
        // Find the active loan for this material
        $stmt = $pdo->prepare("SELECT id, student_id, loan_date FROM am_loans WHERE material_id = ? AND return_date IS NULL");
        $stmt->execute([$material['id']]);
        $loan = $stmt->fetch();

        if ($loan) {
            // Get the ID for 'available' status
            $stmt_status = $pdo->prepare("SELECT id FROM am_materials_status WHERE title = 'available'");
            $stmt_status->execute();
            $available_status_id = $stmt_status->fetchColumn();

            // Update loan
            $stmt = $pdo->prepare("UPDATE am_loans SET return_date = NOW(), return_user_id = ? WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $loan['id']]);

            // Update material status
            $stmt = $pdo->prepare("UPDATE am_materials SET material_status_id = ? WHERE id = ?");
            $stmt->execute([$available_status_id, $material['id']]);

            $success = t('return_success_message');

            // Get student info
            $stmt = $pdo->prepare("SELECT first_name, last_name FROM am_students WHERE id = ?");
            $stmt->execute([$loan['student_id']]);
            $student_info = $stmt->fetch();

            // Get loan info
            $stmt = $pdo->prepare("SELECT loan_date, return_date FROM am_loans WHERE id = ?");
            $stmt->execute([$loan['id']]);
            $returned_loan = $stmt->fetch();

            // Calculate loan duration
            $loan_date = new DateTime($returned_loan['loan_date']);
            $return_date = new DateTime($returned_loan['return_date']);
            $loan_duration = $loan_date->diff($return_date)->format('%a jours, %h heures et %i minutes');

            // Get student's other loaned am_materials
            $stmt = $pdo->prepare("
                SELECT m.name
                FROM am_loans l
                JOIN am_materials m ON l.material_id = m.id
                WHERE l.student_id = ? AND l.return_date IS NULL
            ");
            $stmt->execute([$loan['student_id']]);
            $other_materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get student's loan history
            $stmt = $pdo->prepare("
                SELECT m.name, l.loan_date, l.return_date
                FROM am_loans l
                JOIN am_materials m ON l.material_id = m.id
                WHERE l.student_id = ?
                ORDER BY l.loan_date DESC
                LIMIT 5
            ");
            $stmt->execute([$loan['student_id']]);
            $loan_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = t('return_error_no_active_loan');
        }
    } else {
        $error = t('return_error_invalid_barcode');
    }
}
require_once CONFIG_PATH . '/templates/returns.php';