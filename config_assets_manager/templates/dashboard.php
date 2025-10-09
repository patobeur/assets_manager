<?php
$stmt = $pdo->prepare("SELECT first_name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SESSION['user_role'] === 'admin') {
    $student_count = $pdo->query("SELECT count(*) FROM students")->fetchColumn();
    $material_count = $pdo->query("SELECT count(*) FROM materials")->fetchColumn();
    $agent_count = $pdo->query("SELECT count(*) FROM users WHERE role = 'agent'")->fetchColumn();
}
?>

<h1 class="text-3xl font-bold text-gray-800">Tableau de bord</h1>

<div class="mt-6">
    <p class="text-lg text-gray-600">Content de vous revoir, <span class="font-semibold"><?php echo htmlspecialchars($user['first_name']); ?></span>!</p>
</div>

<?php if ($_SESSION['user_role'] === 'admin'): ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800"><?php echo $student_count; ?></h2>
        <p class="text-gray-600">Étudiants</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800"><?php echo $material_count; ?></h2>
        <p class="text-gray-600">Matériels</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800"><?php echo $agent_count; ?></h2>
        <p class="text-gray-600">Agents</p>
    </div>
</div>
<?php endif; ?>

<div class="mt-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Actions rapides</h2>
    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <a href="?page=loans" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center">
            Emprunter du matériel
        </a>
        <a href="?page=returns" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-center">
            Retourner du matériel
        </a>
    </div>
</div>