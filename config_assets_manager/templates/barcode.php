<div class="flex justify-between items-start mb-4">
    <h1 class="text-2xl font-bold"><?= t('barcode_page_title', 'Étiquettes de codes-barres') ?></h1>
    <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        <?= t('print_labels', 'Imprimer les étiquettes') ?>
    </button>
</div>

<div id="barcode-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

    <!-- Student Barcodes -->
    <?php if (!empty($students)): ?>
        <?php foreach ($students as $student): ?>
            <div class="barcode-label text-center border p-0-10 rounded-lg break-inside-avoid">
                <p class="font-bold text-sm"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
                <p class="text-sm"><?= htmlspecialchars($student['section_title'] ?? '') ?> - <?= htmlspecialchars($student['promo_title'] ?? '') ?></p>
                <img src="generator.php?data=<?= urlencode($student['barcode']) ?>" alt="Barcode for <?= htmlspecialchars($student['first_name']) ?>" class="mx-auto my-2 h-16">
                <p class="font-mono text-sm"><?= htmlspecialchars($student['barcode']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Material Barcodes -->
    <?php if (!empty($materials)): ?>
        <?php foreach ($materials as $material): ?>
            <div class="barcode-label text-center border p-0-10 rounded-lg break-inside-avoid">
                <p class="font-bold text-sm"><?= htmlspecialchars($material['name']) ?></p>
                <img src="generator.php?data=<?= urlencode($material['barcode']) ?>" alt="Barcode for <?= htmlspecialchars($material['name']) ?>" class="mx-auto my-2 h-16">
                <p class="font-mono text-sm"><?= htmlspecialchars($material['barcode']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php if (empty($students) && empty($materials)): ?>
    <p><?= t('no_barcodes_to_display', 'Aucun code-barres à afficher.') ?></p>
<?php endif; ?>