<?php
// Prevent direct script access.
if (!defined('APP_LOADED')) {
    http_response_code(403);
    die('Accès non autorisé.');
}

$promo = null;
$is_edit = $action === 'edit';

if ($is_edit) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM am_promos WHERE id = ?");
    $stmt->execute([$id]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4">
        <?php echo $is_edit ? t('edit_promo') : t('add_promo'); ?>
    </h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <form action="?page=promos&action=<?php echo $is_edit ? 'edit' : 'create'; ?>" method="POST">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo $promo['id']; ?>">
            <?php endif; ?>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                    <?php echo t('title'); ?>
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="title" name="title" type="text" placeholder="<?php echo t('title'); ?>" value="<?php echo htmlspecialchars($promo['title'] ?? ''); ?>" required>
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    <?php echo $is_edit ? t('update') : t('create'); ?>
                </button>
                <a href="?page=promos" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    <?php echo t('cancel'); ?>
                </a>
            </div>
        </form>
    </div>
</div>