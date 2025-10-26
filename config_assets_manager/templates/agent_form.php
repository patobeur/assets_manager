<?php
// Prevent direct script access.
if (!defined('APP_LOADED')) {
    http_response_code(403);
    die('Accès non autorisé.');
}

if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
    echo '<p class="text-red-500">Accès non autorisé.</p>';
    return;
}

$agent = null;
$page_title = 'Ajouter un agent';
if ($action === 'edit') {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM am_users WHERE id = ?");
    $stmt->execute([$id]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$agent) {
        echo '<p class="text-red-500">Agent non trouvé.</p>';
        return;
    }

    // Security check: An admin cannot edit another admin or an adminsys, but can edit themselves
    if ($_SESSION['user_role'] === 'admin' && $agent['id'] !== $_SESSION['user_id'] && in_array($agent['role'], ['admin', 'adminsys'])) {
        echo '<p class="text-red-500">Vous n\'êtes pas autorisé à modifier cet utilisateur.</p>';
        return;
    }

    $page_title = 'Modifier l\'agent';
}
?>

<h1 class="text-3xl font-bold mb-6"><?php echo $page_title; ?></h1>

<div class="max-w-md mx-auto bg-white p-8 border border-gray-300 rounded-lg shadow-md">
    <form action="?page=agents&action=<?php echo $action; ?><?php echo $agent ? '&id=' . $agent['id'] : ''; ?>" method="post">
        <?php if ($agent) : ?>
            <input type="hidden" name="id" value="<?php echo $agent['id']; ?>">
        <?php endif; ?>
        <div class="mb-4">
            <label for="first_name" class="block text-gray-700 text-sm font-bold mb-2">Prénom</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($agent['first_name'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
            <label for="last_name" class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($agent['last_name'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($agent['email'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
            <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" <?php echo $action === 'create' ? 'required' : ''; ?>>
            <?php if ($action === 'edit') : ?>
                <p class="text-xs text-gray-600">Laissez vide pour ne pas changer le mot de passe.</p>
            <?php endif; ?>
        </div>

        <?php if ($_SESSION['user_role'] === 'adminsys') : ?>
            <div class="mb-4">
                <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Rôle</label>
                <select id="role" name="role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" <?php echo ($agent && $agent['id'] === $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                    <option value="agent" <?php echo ($agent && $agent['role'] === 'agent') ? 'selected' : ''; ?>>Agent</option>
                    <option value="admin" <?php echo ($agent && $agent['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="adminsys" <?php echo ($agent && $agent['role'] === 'adminsys') ? 'selected' : ''; ?>>Adminsys</option>
                </select>
            </div>
        <?php elseif ($action === 'create' && $_SESSION['user_role'] === 'admin') : ?>
             <input type="hidden" name="role" value="agent">
        <?php endif; ?>


        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                <?php echo $action === 'create' ? 'Créer' : 'Mettre à jour'; ?>
            </button>
        </div>
    </form>
</div>
