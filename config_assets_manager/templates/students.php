<?php
$stmt = $pdo->query("SELECT * FROM am_students");
$students = $stmt->fetchAll();
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
                            Le fichier CSV doit avoir les colonnes : first_name, last_name, barcode. <a href="examples/students_example.csv" class="text-blue-500 hover:underline" download>Télécharger un exemple</a>.
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


<div class="bg-white shadow-md rounded my-6">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Prénom</th>
                <th class="py-3 px-6 text-left">Nom</th>
                <th class="py-3 px-6 text-left">Code-barres</th>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <th class="py-3 px-6 text-center">Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php foreach ($students as $student): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($student['first_name']); ?></td>
                    <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($student['last_name']); ?></td>
                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($student['barcode']); ?></td>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <a href="?page=students&action=edit&id=<?php echo $student['id']; ?>" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L16.732 3.732z" />
                                    </svg>
                                </a>
                                <a href="?page=students&action=delete&id=<?php echo $student['id']; ?>" class="w-4 mr-2 transform hover:text-red-500 hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>