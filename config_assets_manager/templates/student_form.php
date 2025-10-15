<?php
$student = [
    'id' => '',
    'first_name' => '',
    'last_name' => '',
    'barcode' => '',
    'email' => '',
    'promo_id' => null,
    'section_id' => null,
    'status' => 1,
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
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
            <label for="promo_id" class="block text-gray-700 text-sm font-bold mb-2">Promo</label>
            <select id="promo_id" name="promo_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="">Sélectionner une promo</option>
                <?php foreach ($promos as $promo): ?>
                    <option value="<?php echo $promo['id']; ?>" <?php echo (isset($student['promo_id']) && $student['promo_id'] == $promo['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($promo['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="section_id" class="block text-gray-700 text-sm font-bold mb-2">Section</label>
            <select id="section_id" name="section_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="">Sélectionner une section</option>
                <?php foreach ($sections as $section): ?>
                    <option value="<?php echo $section['id']; ?>" <?php echo (isset($student['section_id']) && $student['section_id'] == $section['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($section['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-6">
            <label for="barcode" class="block text-gray-700 text-sm font-bold mb-2">Code-barres</label>
            <input type="text" id="barcode" name="barcode" value="<?php echo htmlspecialchars($student['barcode']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <div class="mb-4">
            <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Statut</label>
            <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                <input type="checkbox" name="status" id="status" value="1" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" <?php echo (isset($student['status']) && $student['status'] == 1) ? 'checked' : ''; ?>>
                <label for="status" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
            </div>
            <span class="text-gray-700 text-sm">Actif</span>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Mettre à jour' : 'Créer'; ?>
            </button>
            <a href="?page=students" class="text-gray-600">Annuler</a>
        </div>
    </form>
</div>