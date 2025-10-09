<?php
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SESSION['user_role'] === 'admin') {
    $student_count = $pdo->query("SELECT count(*) FROM students")->fetchColumn();
    $material_count = $pdo->query("SELECT count(*) FROM materials")->fetchColumn();
    $agent_count = $pdo->query("SELECT count(*) FROM users WHERE role = 'agent'")->fetchColumn();
}
?>

<h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>

<div class="mt-6">
    <p class="text-lg text-gray-600">Welcome back, <span class="font-semibold"><?php echo htmlspecialchars($user['email']); ?></span>!</p>
</div>

<?php if ($_SESSION['user_role'] === 'admin'): ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800"><?php echo $student_count; ?></h2>
        <p class="text-gray-600">Students</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800"><?php echo $material_count; ?></h2>
        <p class="text-gray-600">Materials</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800"><?php echo $agent_count; ?></h2>
        <p class="text-gray-600">Agents</p>
    </div>
</div>
<?php endif; ?>

<div class="mt-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Quick Actions</h2>
    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <a href="?page=loans" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center">
            Loan Material
        </a>
        <a href="?page=returns" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-center">
            Return Material
        </a>
    </div>
</div>