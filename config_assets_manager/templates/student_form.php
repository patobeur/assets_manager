<?php
$student = [
    'id' => '',
    'first_name' => '',
    'last_name' => '',
    'barcode' => '',
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $stmt = $pdo->prepare("SELECT * FROM am_students WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $student = $stmt->fetch();
}
?>

<h1 class="text-3xl font-bold mb-6"><?php echo $is_edit ? 'Modifier l\'étudiant' : 'Ajouter un étudiant'; ?></h1>

<div class="max-w-md bg-white p-8 border border-gray-300 rounded">
    <form action="?page=students&action=<?php echo $is_edit ? 'edit&id=' . $student['id'] : 'create'; ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
        <div class="mb-4">
            <label for="first_name" class="block text-gray-700 text-sm font-bold mb-2">Prénom</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
            <label for="last_name" class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-6">
            <label for="barcode" class="block text-gray-700 text-sm font-bold mb-2">Code-barres</label>
            <input type="text" id="barcode" name="barcode" value="<?php echo htmlspecialchars($student['barcode']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Mettre à jour' : 'Créer'; ?>
            </button>
            <a href="?page=students" class="text-gray-600">Annuler</a>
        </div>
    </form>
</div>