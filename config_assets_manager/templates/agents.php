<?php

// Prevent direct script access.
if (!defined('APP_LOADED')) {
    http_response_code(403);
    die('Accès non autorisé.');
}

if (!in_array($_SESSION['user_role'], ['admin', 'adminsys'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Accès refusé !</strong>
        <span class="block sm:inline">Vous n\'avez pas la permission d\'accéder à cette page.</span>
    </div>';
    return;
}

$query = "SELECT * FROM am_users";
if ($_SESSION['user_role'] === 'admin') {
    $query .= " WHERE role = 'agent' OR id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
} else {
    $stmt = $pdo->query($query);
}
$agents = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Agents</h1>
    <div class="flex space-x-4">
        <a href="?page=agents&action=export" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Exporter en CSV
        </a>
        <a href="?page=agents&action=create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Ajouter un agent
        </a>
    </div>
</div>


<div class="bg-white shadow-md rounded my-6">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Prénom</th>
                <th class="py-3 px-6 text-left">Nom</th>
                <th class="py-3 px-6 text-left">Email</th>
                <th class="py-3 px-6 text-center">Rôle</th>
                <th class="py-3 px-6 text-center">Statut</th>
                <th class="py-3 px-6 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php foreach ($agents as $agent) : ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($agent['first_name']); ?></td>
                    <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($agent['last_name']); ?></td>
                    <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($agent['email']); ?></td>
                    <td class="py-3 px-6 text-center"><?php echo htmlspecialchars($agent['role']); ?></td>
                    <td class="py-3 px-6 text-center">
                        <span class="bg-<?php echo $agent['status'] ? 'green' : 'red'; ?>-200 text-<?php echo $agent['status'] ? 'green' : 'red'; ?>-600 py-1 px-3 rounded-full text-xs">
                            <?php echo $agent['status'] ? 'Actif' : 'Inactif'; ?>
                        </span>
                    </td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex item-center justify-center">
                            <a href="?page=agents&action=edit&id=<?php echo $agent['id']; ?>" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L16.732 3.732z" />
                                </svg>
                            </a>
                            <?php if ($_SESSION['user_id'] != $agent['id']) : ?>
                                <a href="?page=agents&action=toggle_status&id=<?php echo $agent['id']; ?>" class="w-4 mr-2 transform hover:text-yellow-500 hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                </a>
                                <?php if ($agent['status'] == 0) : ?>
                                    <a href="?page=agents&action=delete&id=<?php echo $agent['id']; ?>" class="w-4 mr-2 transform hover:text-red-500 hover:scale-110">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>