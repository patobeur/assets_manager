<?php
// Prevent direct script access.
if (!defined('APP_LOADED')) {
    http_response_code(403);
    die('Accès non autorisé.');
}

$sql = "
    SELECT
        am_loans.id,
        CONCAT(am_students.first_name, ' ', am_students.last_name) as student_name,
        am_materials.name as material_name,
        am_loans.loan_date,
        am_loans.return_date,
        CONCAT(loan_user.first_name, ' ', loan_user.last_name) as loan_user_name,
        CONCAT(return_user.first_name, ' ', return_user.last_name) as return_user_name
    FROM am_loans
    JOIN am_students ON am_loans.student_id = am_students.id
    JOIN am_materials ON am_loans.material_id = am_materials.id
    LEFT JOIN am_users AS loan_user ON am_loans.loan_user_id = loan_user.id
    LEFT JOIN am_users AS return_user ON am_loans.return_user_id = return_user.id
    ORDER BY am_loans.loan_date DESC
";

$stmt = $pdo->query($sql);
$loans = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Historique</h1>
    <a href="?page=history&action=export" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
        Exporter en CSV
    </a>
</div>

<div class="bg-white shadow-md rounded my-6">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Étudiant</th>
                <th class="py-3 px-6 text-left">Matériel</th>
                <th class="py-3 px-6 text-left">Date d'emprunt</th>
                <th class="py-3 px-6 text-left">Agent d'emprunt</th>
                <th class="py-3 px-6 text-left">Date de retour</th>
                <th class="py-3 px-6 text-left">Agent de retour</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php foreach ($loans as $loan): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($loan['student_name']); ?></td>
                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($loan['material_name']); ?></td>
                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($loan['loan_date']); ?></td>
                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($loan['loan_user_name']); ?></td>
                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($loan['return_date'] ?? 'Non retourné'); ?></td>
                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($loan['return_user_name'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>