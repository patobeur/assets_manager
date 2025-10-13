<?php
$stmt = $pdo->query("SELECT * FROM am_promos ORDER BY title");
$promos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold"><?php echo t('manage_promos'); ?></h1>
    <a href="?page=promos&action=create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        <?php echo t('add_promo'); ?>
    </a>
</div>

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
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <?php echo t('delete_promo'); ?>
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                <?php echo t('confirm_delete_promo'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <a id="confirm-delete-btn" href="#" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    <?php echo t('delete'); ?>
                </a>
                <button id="cancel-delete-btn" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    <?php echo t('cancel'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="bg-white shadow-md rounded my-6">
    <table class="min-w-full table-auto">
        <thead class="bg-gray-200">
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left"><?php echo t('title'); ?></th>
                <th class="py-3 px-6 text-center"><?php echo t('actions'); ?></th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php foreach ($promos as $promo): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6 text-left whitespace-nowrap">
                        <?php echo htmlspecialchars($promo['title']); ?>
                    </td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex item-center justify-center">
                            <a href="?page=promos&action=edit&id=<?php echo $promo['id']; ?>" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110" title="<?php echo t('edit_promo'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L16.732 3.732z" />
                                </svg>
                            </a>
                            <button data-id="<?php echo $promo['id']; ?>" class="delete-btn w-4 mr-2 transform hover:text-red-500 hover:scale-110" title="<?php echo t('delete_promo'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('delete-modal');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
    let promoIdToDelete = null;

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            promoIdToDelete = this.dataset.id;
            deleteModal.classList.remove('hidden');
        });
    });

    cancelDeleteBtn.addEventListener('click', function () {
        deleteModal.classList.add('hidden');
        promoIdToDelete = null;
    });

    confirmDeleteBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (promoIdToDelete) {
            window.location.href = `?page=promos&action=delete&id=${promoIdToDelete}`;
        }
    });
});
</script>