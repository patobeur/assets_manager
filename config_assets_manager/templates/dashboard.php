<?php
$stmt = $pdo->prepare("SELECT first_name FROM am_users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'agent') {
    $student_count = $pdo->query("SELECT count(*) FROM am_students")->fetchColumn();
    $material_count = $pdo->query("SELECT count(*) FROM am_materials")->fetchColumn();
    $agent_count = $pdo->query("SELECT count(*) FROM am_users WHERE role = 'agent'")->fetchColumn();
    $loaned_count = $pdo->query("SELECT count(*) FROM am_materials WHERE status = 'loaned'")->fetchColumn();
}
?>

<h1 class="text-3xl font-bold text-gray-800"><?php echo t('dashboard', 'Tableau de bord'); ?></h1>

<div class="mt-6">
    <p class="text-lg text-gray-600"><?php echo str_replace('{user_name}', '<span class="font-semibold">' . htmlspecialchars($user['first_name']) . '</span>', t('welcome_back', 'Content de vous revoir, {user_name}!')); ?></p>
</div>

<?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'agent'): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $student_count; ?></h2>
            <p class="text-gray-600"><?php echo t('students', 'Étudiants'); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $material_count; ?></h2>
            <p class="text-gray-600"><?php echo t('materials', 'Matériels'); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $agent_count; ?></h2>
            <p class="text-gray-600"><?php echo t('agents', 'Agents'); ?></p>
        </div>
        <div class="bg-blue-100 p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $loaned_count; ?></h2>
            <p class="text-gray-600"><?php echo t('loaned_materials', 'Matériels empruntés'); ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="mt-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-4"><?php echo t('quick_actions', 'Actions rapides'); ?></h2>
    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <a href="?page=loans" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center">
            <?php echo t('loan_material', 'Emprunter du matériel'); ?>
        </a>
        <a href="?page=returns" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-center">
            <?php echo t('return_material', 'Retourner du matériel'); ?>
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
        WHERE l.return_date IS NULL
        ORDER BY l.loan_date ASC
    ");
    $current_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php if (($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'agent') && !empty($current_loans)): ?>
    <div class="mt-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-4"><?php echo t('currently_loaned_materials', 'Matériels actuellement empruntés'); ?></h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2"><?php echo t('student', 'Étudiant'); ?></th>
                        <th class="text-left py-2"><?php echo t('material', 'Matériel'); ?></th>
                        <th class="text-left py-2"><?php echo t('loan_date', 'Date d\'emprunt'); ?></th>
                        <th class="text-left py-2"><?php echo t('elapsed_time', 'Temps écoulé'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $now = new DateTime();
                    foreach ($current_loans as $loan):
                        $loan_date = new DateTime($loan['loan_date']);
                        $interval = $now->diff($loan_date);

                        $elapsed_time = '';
                        if ($interval->days > 0) {
                            $elapsed_time .= $interval->days . t('time_unit_days') . ' ';
                        }
                        $elapsed_time .= sprintf('%02d', $interval->h) . t('time_unit_hours') . ' ';
                        $elapsed_time .= sprintf('%02d', $interval->i) . t('time_unit_minutes') . ' ';
                        $elapsed_time .= sprintf('%02d', $interval->s) . t('time_unit_seconds');
                    ?>
                        <tr class="border-b">
                            <td class="py-2"><?php echo htmlspecialchars($loan['student_first_name'] . ' ' . $loan['student_last_name']); ?></td>
                            <td class="py-2"><?php echo htmlspecialchars($loan['material_name']); ?></td>
                            <td class="py-2"><?php echo date(t('date_format_long'), strtotime($loan['loan_date'])); ?></td>
                            <td class="py-2 font-mono"><?php echo $elapsed_time; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>