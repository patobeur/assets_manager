<?php
$stmt = $pdo->query("
    SELECT s.*, p.title as promo_name, sec.title as section_name
    FROM am_students s
    LEFT JOIN am_promos p ON s.promo_id = p.id
    LEFT JOIN am_sections sec ON s.section_id = sec.id
    ORDER BY s.last_name, s.first_name
");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Étudiants</h1>
    <div class="flex space-x-4">
        <a href="?page=students&action=export" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Exporter en CSV
        </a>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <button id="import-btn" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                Importer CSV
            </button>
            <a href="?page=students&action=create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Ajouter un étudiant
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Import Modal -->
<div id="import-modal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="?page=students&action=import" method="post" enctype="multipart/form-data">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Importer des étudiants depuis un CSV
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Le fichier CSV doit avoir les colonnes : Prénom, Nom, Email, Promo, Section, Code-barres. <a href="examples/students_example.csv" class="text-blue-500 hover:underline" download>Télécharger un exemple</a>.
                        </p>
                        <input type="file" name="csv_file" accept=".csv" class="mt-2">
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Importer
                    </button>
                    <button type="button" id="cancel-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('import-btn').addEventListener('click', function() {
        document.getElementById('import-modal').classList.remove('hidden');
    });
    document.getElementById('cancel-btn').addEventListener('click', function() {
        document.getElementById('import-modal').classList.add('hidden');
    });
</script>


<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Supprimer l'étudiant
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Êtes-vous sûr de vouloir supprimer cet étudiant ? Cette action est irréversible.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <a id="confirm-delete-btn" href="#" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Supprimer
                </a>
                <button id="cancel-delete-btn" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>


<div class="bg-white shadow-md rounded my-6">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Prénom</th>
                <th class="py-3 px-6 text-left">Nom</th>
                <th class="py-3 px-6 text-left">Promo</th>
                <th class="py-3 px-6 text-left">Section</th>
                <th class="py-3 px-6 text-left">Code-barres</th>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <th class="py-3 px-6 text-center">Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php foreach ($students as $student): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100">

                    <td class="py-3 px-6 text-left whitespace-nowrap">
                        <a href="?page=student&id=<?php echo $student['id']; ?>" class="text-blue-500 hover:underline">
                            <?php echo htmlspecialchars($student['first_name']); ?>
                        </a>
                    </td>
                    <td class="py-3 px-6 text-left whitespace-nowrap">
                        <a href="?page=student&id=<?php echo $student['id']; ?>" class="text-blue-500 hover:underline">
                            <?php echo htmlspecialchars($student['last_name']); ?>
                        </a>
                    </td>
                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($student['promo_name'] ?? 'N/A'); ?></td>
                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                    <td class="py-3 px-6 text-left">
                        <?php if (!empty($student['barcode'])): ?>
                            <img src="barcode/generator.php?data=<?php echo urlencode($student['barcode']); ?>" alt="Code-barres de l'étudiant" class="mt-2">
                        <?php endif; ?>

                        <?php echo htmlspecialchars($student['barcode']); ?>
                    </td>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <a href="?page=student&id=<?php echo $student['id']; ?>" class="w-4 mr-2 transform hover:text-blue-500 hover:scale-110" title="Voir la fiche">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="?page=students&action=edit&id=<?php echo $student['id']; ?>" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110" title="Modifier">

                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L16.732 3.732z" />
                                    </svg>
                                </a>
                                <button data-id="<?php echo $student['id']; ?>" class="delete-btn w-4 mr-2 transform hover:text-red-500 hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('delete-modal');
        const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
        const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
        let studentIdToDelete = null;

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                studentIdToDelete = this.dataset.id;
                deleteModal.classList.remove('hidden');
            });
        });

        cancelDeleteBtn.addEventListener('click', function() {
            deleteModal.classList.add('hidden');
            studentIdToDelete = null;
        });

        confirmDeleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (studentIdToDelete) {
                window.location.href = `?page=students&action=delete&id=${studentIdToDelete}`;
            }
        });
    });
</script>