<?php
// Prevent direct script access.
if (!defined('APP_LOADED')) {
    http_response_code(403);
    die('Accès non autorisé.');
}
?><h1 class="text-3xl font-bold mb-6"><?php echo t('return_material'); ?></h1>


<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<div class="max-w-md bg-white p-8 border border-gray-300 rounded">
    <form action="?page=returns" method="post">
        <div class="mb-6">
            <label for="material_barcode" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('material_barcode'); ?></label>
            <input type="text" id="material_barcode" name="material_barcode" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required autofocus>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo t('return_verb'); ?>
            </button>
        </div>
    </form>
</div>

<?php if (isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
    </div>

    <div class="max-w-4xl bg-white p-8 border border-gray-300 rounded mb-6">
        <h2 class="text-2xl font-bold mb-4"><?php echo t('return_details'); ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><strong><?php echo t('loaned_by'); ?></strong> <?php echo htmlspecialchars($student_info['first_name'] . ' ' . $student_info['last_name']); ?></p>
                <p><strong><?php echo t('loan_date_label'); ?></strong> <?php echo (new DateTime($returned_loan['loan_date']))->format(t('date_format_long')); ?></p>
                <p><strong><?php echo t('loan_duration_label'); ?></strong> <?php echo htmlspecialchars($loan_duration); ?></p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-2"><?php echo t('student_other_material'); ?></h3>
                <?php if (empty($other_materials)): ?>
                    <p><?php echo t('no_other_material'); ?></p>
                <?php else: ?>
                    <ul class="list-disc list-inside">
                        <?php foreach ($other_materials as $material): ?>
                            <li><?php echo htmlspecialchars($material['name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-6">
            <h3 class="text-xl font-bold mb-2"><?php echo t('student_loan_history_5'); ?></h3>
            <?php if (empty($loan_history)): ?>
                <p><?php echo t('no_loan_history_for_student'); ?></p>
            <?php else: ?>
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b"><?php echo t('material'); ?></th>
                            <th class="py-2 px-4 border-b"><?php echo t('loan_date'); ?></th>
                            <th class="py-2 px-4 border-b"><?php echo t('return_date'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loan_history as $loan): ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($loan['name']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo (new DateTime($loan['loan_date']))->format(t('date_format_long')); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo $loan['return_date'] ? (new DateTime($loan['return_date']))->format(t('date_format_long')) : t('in_progress'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>