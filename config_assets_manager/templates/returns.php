<h1 class="text-3xl font-bold mb-6">Retourner du matériel</h1>


<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<div class="max-w-md bg-white p-8 border border-gray-300 rounded">
    <form action="?page=returns" method="post">
        <div class="mb-6">
            <label for="material_barcode" class="block text-gray-700 text-sm font-bold mb-2">Code-barres du matériel</label>
            <input type="text" id="material_barcode" name="material_barcode" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required autofocus>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Retourner
            </button>
        </div>
    </form>
</div>

<?php if (isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
    </div>

    <div class="max-w-4xl bg-white p-8 border border-gray-300 rounded mb-6">
        <h2 class="text-2xl font-bold mb-4">Détails du retour</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><strong>Emprunté par :</strong> <?php echo htmlspecialchars($student_info['first_name'] . ' ' . $student_info['last_name']); ?></p>
                <p><strong>Date d'emprunt :</strong> <?php echo (new DateTime($returned_loan['loan_date']))->format('d/m/Y H:i'); ?></p>
                <p><strong>Durée de l'emprunt :</strong> <?php echo htmlspecialchars($loan_duration); ?></p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-2">Autre matériel en possession de l'étudiant(e)</h3>
                <?php if (empty($other_materials)): ?>
                    <p>Aucun autre matériel en possession.</p>
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
            <h3 class="text-xl font-bold mb-2">Historique des 5 derniers emprunts de l'étudiant(e)</h3>
            <?php if (empty($loan_history)): ?>
                <p>Aucun historique d'emprunt pour cet étudiant.</p>
            <?php else: ?>
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Matériel</th>
                            <th class="py-2 px-4 border-b">Date d'emprunt</th>
                            <th class="py-2 px-4 border-b">Date de retour</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loan_history as $loan): ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($loan['name']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo (new DateTime($loan['loan_date']))->format('d/m/Y H:i'); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo $loan['return_date'] ? (new DateTime($loan['return_date']))->format('d/m/Y H:i') : 'En cours'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>