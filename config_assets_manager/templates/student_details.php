<div class="container mx-auto px-4 py-8">
    <a href="index.php?page=students" class="text-blue-500 hover:underline mb-6 inline-block">&larr; Retour à la liste des étudiants</a>

    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h1 class="text-3xl font-bold mb-4">Fiche de l'étudiant</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-gray-600"><strong>Prénom:</strong> <?php echo htmlspecialchars($student['first_name']); ?></p>
                <p class="text-gray-600"><strong>Nom:</strong> <?php echo htmlspecialchars($student['last_name']); ?></p>
                <p class="text-gray-600"><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
            </div>
            <div>
                <p class="text-gray-600"><strong>Promo:</strong> <?php echo htmlspecialchars($student['promo_name'] ?? 'N/A'); ?></p>
                <p class="text-gray-600"><strong>Section:</strong> <?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></p>

                <p class="text-gray-600"><strong>Code-barres:</strong> <?php echo htmlspecialchars($student['barcode']); ?></p>
                <?php if (!empty($student['barcode'])): ?>
                    <img src="generator.php?data=<?php echo urlencode($student['barcode']); ?>" alt="Code-barres de l'étudiant" class="mt-2">
                <?php endif; ?>
            </div>
            <div>
            </div>
        </div>
    </div>
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-4">Historique des emprunts</h2>
    <?php if (count($loans) > 0): ?>
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Matériel</th>
                    <th class="py-3 px-6 text-left">Date d'emprunt</th>
                    <th class="py-3 px-6 text-left">Date de retour</th>
                    <th class="py-3 px-6 text-left">Temps d'emprunt</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <?php foreach ($loans as $loan): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($loan['material_name']); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo date('d/m/Y H:i', strtotime($loan['loan_date'])); ?></td>
                        <td class="py-3 px-6 text-left">
                            <?php if ($loan['return_date']): ?>
                                <?php echo date('d/m/Y H:i', strtotime($loan['return_date'])); ?>
                            <?php else: ?>
                                <span class="bg-yellow-200 text-yellow-800 py-1 px-3 rounded-full text-xs">En cours</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-6 text-left">
                            <?php
                            $loan_date = new DateTime($loan['loan_date']);
                            $return_date = $loan['return_date'] ? new DateTime($loan['return_date']) : new DateTime(); // Use current time if not returned
                            $duration = $loan_date->diff($return_date);
                            echo $duration->format('%a jours, %h heures, %i minutes');
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Cet étudiant n'a aucun emprunt.</p>
    <?php endif; ?>
</div>
</div>