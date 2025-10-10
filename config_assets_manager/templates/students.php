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
            <a href="?page=students&action=create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Ajouter un étudiant
            </a>
        <?php endif; ?>
    </div>
</div>


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