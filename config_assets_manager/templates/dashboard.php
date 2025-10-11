<?php
$stmt = $pdo->prepare("SELECT first_name FROM am_users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'agent') {
    $student_count = $pdo->query("SELECT count(*) FROM am_students")->fetchColumn();
    $material_count = $pdo->query("SELECT count(*) FROM am_materials")->fetchColumn();
    $agent_count = $pdo->query("SELECT count(*) FROM am_users WHERE role = 'agent'")->fetchColumn();
    $loaned_count = $pdo->query("SELECT count(*) FROM am_materials WHERE status = 'loaned'")->fetchColumn();
    $overdue_count = $pdo->query("SELECT count(*) FROM am_loans WHERE return_date IS NULL AND loan_date < DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
}
?>

<h1 class="text-3xl font-bold text-gray-800">Tableau de bord</h1>

<div class="mt-6">
    <p class="text-lg text-gray-600">Content de vous revoir, <span class="font-semibold"><?php echo htmlspecialchars($user['first_name']); ?></span>!</p>
</div>

<?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'agent'): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $student_count; ?></h2>
            <p class="text-gray-600">Étudiants</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $material_count; ?></h2>
            <p class="text-gray-600">Matériels</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $agent_count; ?></h2>
            <p class="text-gray-600">Agents</p>
        </div>
        <div class="bg-blue-100 p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $loaned_count; ?></h2>
            <p class="text-gray-600">Matériels empruntés</p>
        </div>
        <div class="bg-red-100 p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $overdue_count; ?></h2>
            <p class="text-gray-600">Emprunts en retard</p>
        </div>
    </div>
<?php endif; ?>

<div class="mt-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Actions rapides</h2>
    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <a href="?page=loans" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center">
            Emprunter du matériel
        </a>
        <a href="?page=returns" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-center">
            Retourner du matériel
        </a>
    </div>
</div>

<?php
if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'agent') {
    $stmt = $pdo->query("
        SELECT l.loan_date, s.first_name AS student_first_name, s.last_name AS student_last_name, m.name AS material_name
        FROM am_loans l
        JOIN am_students s ON l.student_id = s.id
        JOIN am_materials m ON l.material_id = m.id
        ORDER BY l.loan_date DESC
        LIMIT 5
    ");
    $recent_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php if (($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'agent') && !empty($recent_loans)): ?>
    <div class="mt-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Derniers emprunts</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Étudiant</th>
                        <th class="text-left py-2">Matériel</th>
                        <th class="text-left py-2">Date d'emprunt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_loans as $loan): ?>
                        <tr class="border-b">
                            <td class="py-2"><?php echo htmlspecialchars($loan['student_first_name'] . ' ' . $loan['student_last_name']); ?></td>
                            <td class="py-2"><?php echo htmlspecialchars($loan['material_name']); ?></td>
                            <td class="py-2"><?php echo date('d/m/Y H:i', strtotime($loan['loan_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php
if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'agent') {
    $stmt = $pdo->query("
        SELECT l.loan_date, s.first_name AS student_first_name, s.last_name AS student_last_name, m.name AS material_name
        FROM am_loans l
        JOIN am_students s ON l.student_id = s.id
        JOIN am_materials m ON l.material_id = m.id
        WHERE l.return_date IS NULL AND l.loan_date < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY l.loan_date ASC
    ");
    $overdue_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php if (($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'agent') && !empty($overdue_loans)): ?>
    <div class="mt-10">
        <h2 class="text-2xl font-bold text-red-600 mb-4">Emprunts en retard (>7jours)</h2>
        <div class="bg-white p-6 rounded-lg shadow-md border border-red-200">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Étudiant</th>
                        <th class="text-left py-2">Matériel</th>
                        <th class="text-left py-2">Date d'emprunt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($overdue_loans as $loan): ?>
                        <tr class="border-b">
                            <td class="py-2"><?php echo htmlspecialchars($loan['student_first_name'] . ' ' . $loan['student_last_name']); ?></td>
                            <td class="py-2"><?php echo htmlspecialchars($loan['material_name']); ?></td>
                            <td class="py-2 text-red-600 font-semibold"><?php echo date('d/m/Y H:i', strtotime($loan['loan_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>