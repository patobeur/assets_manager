<?php
// No direct access
defined('APP_LOADED') or die('Accès non autorisé.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_barcode = $_POST['student_barcode'];
    $material_barcode = $_POST['material_barcode'];

    // Get student id
    $stmt = $pdo->prepare("SELECT id FROM am_students WHERE barcode = ?");
    $stmt->execute([$student_barcode]);
    $student = $stmt->fetch();

    // Get material id and status title
    $stmt = $pdo->prepare("
        SELECT m.id, ms.title as status_title
        FROM am_materials m
        JOIN am_materials_status ms ON m.material_status_id = ms.id
        WHERE m.barcode = ?
    ");
    $stmt->execute([$material_barcode]);
    $material = $stmt->fetch();

    if ($student && $material && $material['status_title'] === 'available') {
        // Get the ID for 'loaned' status
        $stmt_status = $pdo->prepare("SELECT id FROM am_materials_status WHERE title = 'loaned'");
        $stmt_status->execute();
        $loaned_status_id = $stmt_status->fetchColumn();


        // Create loan
        $stmt = $pdo->prepare("INSERT INTO am_loans (student_id, material_id, loan_date, loan_user_id) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$student['id'], $material['id'], $_SESSION['user_id']]);
        $loan_id = $pdo->lastInsertId();

        // Update material status
        $stmt = $pdo->prepare("UPDATE am_materials SET material_status_id = ? WHERE id = ?");
        $stmt->execute([$loaned_status_id, $material['id']]);

        // Get student info
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM am_students WHERE id = ?");
        $stmt->execute([$student['id']]);
        $student_info = $stmt->fetch();

        // Get material info
        $stmt = $pdo->prepare("SELECT name FROM am_materials WHERE id = ?");
        $stmt->execute([$material['id']]);
        $material_info = $stmt->fetch();

        // Get loan info
        $stmt = $pdo->prepare("SELECT loan_date FROM am_loans WHERE id = ?");
        $stmt->execute([$loan_id]);
        $loan_info = $stmt->fetch();

        $success = t('loan_success_message', [
            'material_name' => $material_info['name'],
            'student_name' => $student_info['first_name'] . ' ' . $student_info['last_name'],
            'loan_date' => date(t('date_format_long'), strtotime($loan_info['loan_date']))
        ]);

        // Get student's other loaned am_materials
        $stmt = $pdo->prepare("
            SELECT m.name
            FROM am_loans l
            JOIN am_materials m ON l.material_id = m.id
            WHERE l.student_id = ? AND l.return_date IS NULL
        ");
        $stmt->execute([$student['id']]);
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
        $stmt->execute([$student['id']]);
        $loan_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = t('loan_error');
    }
}
require_once CONFIG_PATH . '/templates/loans.php';