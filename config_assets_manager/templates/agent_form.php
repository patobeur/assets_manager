<?php
$agent = [
    'id' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $stmt = $pdo->prepare("SELECT * FROM am_users WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $agent = $stmt->fetch();
}
?>

<h1 class="text-3xl font-bold mb-6"><?php echo $is_edit ? 'Modifier l\'agent' : 'Ajouter un agent'; ?></h1>

<div class="max-w-md bg-white p-8 border border-gray-300 rounded">
    <form action="?page=agents&action=<?php echo $is_edit ? 'edit&id=' . $agent['id'] : 'create'; ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $agent['id']; ?>">
        <div class="mb-4">
            <label for="first_name" class="block text-gray-700 text-sm font-bold mb-2">Prénom</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($agent['first_name']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
            <label for="last_name" class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($agent['last_name']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($agent['email']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
            <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" <?php echo $is_edit ? '' : 'required'; ?>>
            <?php if ($is_edit): ?>
                <p class="text-xs italic">Laissez vide pour conserver le mot de passe actuel.</p>
            <?php endif; ?>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Mettre à jour' : 'Créer'; ?>
            </button>
            <a href="?page=agents" class="text-gray-600">Annuler</a>
        </div>
    </form>
</div>