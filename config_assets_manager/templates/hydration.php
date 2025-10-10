<?php
// Check if hydration data exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM am_students WHERE barcode LIKE 'HYDRATION-%'");
$stmt->execute();
$isHydrated = $stmt->fetchColumn() > 0;
?>
<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4">Hydratation de la base de données</h1>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <p class="mb-4">Cette page vous permet de peupler la base de données avec des données de démonstration ou de nettoyer ces mêmes données.</p>

    <div class="flex space-x-4">
        <form method="post" action="?page=hydration&action=populate">
            <button type="submit"
                class="font-bold py-2 px-4 rounded text-white <?php echo $isHydrated ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-700'; ?>"
                <?php echo $isHydrated ? 'disabled' : ''; ?>>
                Peupler les données
            </button>
        </form>

        <form method="post" action="?page=hydration&action=clear">
            <button type="submit"
                class="font-bold py-2 px-4 rounded text-white <?php echo !$isHydrated ? 'bg-gray-400 cursor-not-allowed' : 'bg-red-500 hover:bg-red-700'; ?>"
                <?php echo !$isHydrated ? 'disabled' : ''; ?>>
                Nettoyer les données
            </button>
        </form>
    </div>
</div>