<div class="container mx-auto px-4 py-8">
    <a href="index.php?page=students" class="text-blue-500 hover:underline mb-6 inline-block">&larr; Retour à la liste des étudiants</a>

    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h1 class="text-3xl font-bold mb-4">Fiche de l'étudiant</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-gray-600"><strong>Prénom:</strong> <?php echo htmlspecialchars($student['first_name']); ?></p>
                <p class="text-gray-600"><strong>Nom:</strong> <?php echo htmlspecialchars($student['last_name']); ?></p>
                <p class="text-gray-600 flex items-center">
                    <strong class="mr-2">Email:</strong>
                    <span id="email-display" data-email="<?php echo htmlspecialchars($student['email']); ?>">
                        <?php echo str_repeat('•', strlen($student['email'])); ?>
                    </span>
                    <button id="toggle-email-visibility" class="ml-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg id="eye-icon-closed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.563 3.029m0 0l-2.117 2.116" />
                        </svg>
                        <svg id="eye-icon-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.022 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </p>
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


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.getElementById('toggle-email-visibility');
        const emailDisplay = document.getElementById('email-display');
        const eyeIconOpen = document.getElementById('eye-icon-open');
        const eyeIconClosed = document.getElementById('eye-icon-closed');
        const originalEmail = emailDisplay.getAttribute('data-email');
        const maskedEmail = '•'.repeat(originalEmail.length);

        let isEmailVisible = false;

        toggleButton.addEventListener('click', function() {
            isEmailVisible = !isEmailVisible;

            if (isEmailVisible) {
                emailDisplay.textContent = originalEmail;
                eyeIconOpen.classList.add('hidden');
                eyeIconClosed.classList.remove('hidden');
            } else {
                emailDisplay.textContent = maskedEmail;
                eyeIconOpen.classList.remove('hidden');
                eyeIconClosed.classList.add('hidden');
            }
        });
    });
</script>