<?php
$material = [
    'id' => '',
    'name' => '',
    'status' => 'available',
    'barcode' => '',
    'description' => '',
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $stmt = $pdo->prepare("SELECT * FROM am_materials WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $material = $stmt->fetch();
}
?>

<h1 class="text-3xl font-bold mb-6"><?php echo $is_edit ? 'Modifier le matériel' : 'Ajouter du matériel'; ?></h1>

<div class="max-w-md bg-white p-8 border border-gray-300 rounded">
    <form action="?page=materials&action=<?php echo $is_edit ? 'edit&id=' . $material['id'] : 'create'; ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $material['id']; ?>">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($material['name']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
            <textarea id="description" name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($material['description']); ?></textarea>
        </div>
        <div class="mb-4">
            <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Statut</label>
            <select id="status" name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="available" <?php echo $material['status'] === 'available' ? 'selected' : ''; ?>>Disponible</option>
                <option value="loaned" <?php echo $material['status'] === 'loaned' ? 'selected' : ''; ?>>Emprunté</option>
                <option value="maintenance" <?php echo $material['status'] === 'maintenance' ? 'selected' : ''; ?>>En maintenance</option>
            </select>
        </div>
        <div class="mb-6">
            <label for="barcode" class="block text-gray-700 text-sm font-bold mb-2">Code-barres</label>
            <input type="text" id="barcode" name="barcode" value="<?php echo htmlspecialchars($material['barcode']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Mettre à jour' : 'Créer'; ?>
            </button>
            <a href="?page=materials" class="text-gray-600">Annuler</a>
        </div>
    </form>
</div>