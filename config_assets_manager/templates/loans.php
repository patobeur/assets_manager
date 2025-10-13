<h1 class="text-3xl font-bold mb-6"><?php echo t('loan_material'); ?></h1>

<?php if (isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
    </div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>


<div class="max-w-md bg-white p-8 border border-gray-300 rounded">
    <form action="?page=loans" method="post">
        <div class="mb-4">
            <label for="student_barcode" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('student_barcode'); ?></label>
            <input type="text" id="student_barcode" name="student_barcode" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required autofocus>
        </div>
        <div class="mb-6">
            <label for="material_barcode" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('material_barcode'); ?></label>
            <input type="text" id="material_barcode" name="material_barcode" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo t('loan_verb'); ?>
            </button>
        </div>
    </form>
</div>

<?php if (isset($student_info)): ?>
<div class="mt-6">
    <h2 class="text-2xl font-bold mb-4"><?php echo t('loan_information'); ?></h2>

    <?php if (!empty($other_materials)): ?>
        <div class="mb-4">
            <h3 class="text-xl font-bold mb-2"><?php echo t('student_material_possession'); ?></h3>
            <ul class="list-disc list-inside">
                <?php foreach ($other_materials as $material): ?>
                    <li><?php echo htmlspecialchars($material['name']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($loan_history)): ?>
        <div>
            <h3 class="text-xl font-bold mb-2"><?php echo t('student_loan_history_5'); ?></h3>
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
                            <td class="py-2 px-4 border-b"><?php echo date(t('date_format_long'), strtotime($loan['loan_date'])); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo $loan['return_date'] ? date(t('date_format_long'), strtotime($loan['return_date'])) : t('not_returned'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>