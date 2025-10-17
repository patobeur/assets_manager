<?php
// Prevent direct script access.
if (!defined('APP_LOADED')) {
    http_response_code(403);
    die('Accès non autorisé.');
}

// All data fetching is now done in index.php before this template is included.
// This file is purely for presentation.

// --- Helper function to format duration ---
function format_duration($total_seconds)
{
    if ($total_seconds < 1) {
        return "0 " . t('time_unit_seconds');
    }
    $days = floor($total_seconds / 86400);
    $hours = floor(($total_seconds % 86400) / 3600);
    $minutes = floor(($total_seconds % 3600) / 60);
    $seconds = $total_seconds % 60;

    $parts = [];
    if ($days > 0) $parts[] = $days . t('time_unit_days');
    if ($hours > 0) $parts[] = $hours . t('time_unit_hours');
    if ($minutes > 0) $parts[] = $minutes . t('time_unit_minutes');
    if ($seconds > 0 || empty($parts)) $parts[] = $seconds . t('time_unit_seconds');

    return implode(' ', $parts);
}
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6"><?php echo t('dashboard', 'Tableau de bord'); ?></h1>

<p class="text-lg text-gray-600 mb-8"><?php echo str_replace('{user_name}', '<span class="font-semibold">' . htmlspecialchars($user['first_name']) . '</span>', t('welcome_back', 'Content de vous revoir, {user_name}!')); ?></p>

<!-- Main Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-3xl font-bold text-gray-800"><?php echo $student_count; ?></h2>
        <p class="text-gray-600 mt-1"><?php echo t('students', 'Étudiants'); ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-3xl font-bold text-gray-800"><?php echo $material_count; ?></h2>
        <p class="text-gray-600 mt-1"><?php echo t('materials', 'Matériels'); ?></p>
    </div>
    <div class="bg-blue-100 p-6 rounded-lg shadow-md">
        <h2 class="text-3xl font-bold text-gray-800"><?php echo $loaned_count; ?></h2>
        <p class="text-gray-600 mt-1"><?php echo t('loaned_materials', 'Matériels empruntés'); ?></p>
    </div>
</div>

<!-- Detailed Stats Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

    <!-- Left Column -->
    <div>
        <!-- Sections and Promos -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4"><?php echo t('school_structure', 'Structure de l\'école'); ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600"><?php echo t('sections'); ?></p>
                    <p class="text-2xl font-bold"><?php echo "{$used_sections} / {$total_sections}"; ?></p>
                    <p class="text-sm text-gray-500"><?php echo t('sections_in_use', 'utilisées'); ?></p>
                </div>
                <div>
                    <p class="text-gray-600"><?php echo t('promos'); ?></p>
                    <p class="text-2xl font-bold"><?php echo "{$used_promos} / {$total_promos}"; ?></p>
                    <p class="text-sm text-gray-500"><?php echo t('promos_in_use', 'utilisées'); ?></p>
                </div>
            </div>
        </div>

        <!-- Most Popular Materials -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold text-gray-800 mb-4"><?php echo t('most_popular_materials', 'Matériels les plus populaires'); ?></h3>
            <?php if (!empty($most_loaned_materials)): ?>
                <ol class="list-decimal list-inside space-y-2">
                    <?php foreach ($most_loaned_materials as $material): ?>
                        <li>
                            <span class="font-semibold"><?php echo htmlspecialchars($material['name']); ?></span>
                            - <span class="text-gray-600"><?php echo str_replace('{count}', $material['loan_count'], t('loaned_n_times', 'Emprunté {count} fois')); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p class="text-gray-500"><?php echo t('no_loan_data_yet', 'Aucune donnée d\'emprunt.'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Top 5 Students by Loans -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4"><?php echo t('top_5_students_by_loans', 'Top 5 des étudiants (par nombre d\'emprunts)'); ?></h3>
            <?php if (!empty($top_students_by_loans)): ?>
                <ol class="list-decimal list-inside space-y-2">
                    <?php foreach ($top_students_by_loans as $student): ?>
                        <li>
                            <span class="font-semibold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                            - <span class="text-gray-600"><?php echo str_replace('{count}', $student['loan_count'], t('n_loans', '{count} emprunts')); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p class="text-gray-500"><?php echo t('no_loan_data_yet'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Top 5 Students by Duration -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold text-gray-800 mb-4"><?php echo t('top_5_students_by_duration', 'Top 5 des étudiants (par durée d\'emprunt)'); ?></h3>
            <?php if (!empty($top_students_by_duration)): ?>
                <ol class="list-decimal list-inside space-y-2">
                    <?php foreach ($top_students_by_duration as $student): ?>
                        <li>
                            <span class="font-semibold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                            - <span class="text-gray-600"><?php echo format_duration($student['total_duration_seconds']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p class="text-gray-500"><?php echo t('no_loan_data_yet'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

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