<?php

// Initialize the language system, which also handles session security
require_once 'language_init.php';

session_start();

// Define a constant to grant access to the bootstrap file.
define('APP_LOADED', true);

// Load the bootstrap file to get the configuration path.
if (!file_exists('bootstrap.php')) {
    header('Location: install.php');
    exit;
}
require_once 'bootstrap.php';

// --- Brute Force Protection ---
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_TIME = 300; // 5 minutes in seconds

// Check if the user is currently locked out
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    $remaining_time = $_SESSION['lockout_time'] - time();
    $error = t('login_locked_out', ['minutes' => ceil($remaining_time / 60)], "Trop de tentatives de connexion. Veuillez réessayer dans {minutes} minutes.");
} else {

    // Now, use the CONFIG_PATH to load the actual configuration and database files.
    require_once CONFIG_PATH . '/config.php';
    require_once CONFIG_PATH . '/Database.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $db = new Database();
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM am_users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        $user = $stmt->fetch();

        if ($user && password_verify($_POST['password'], $user['password'])) {
            // On successful login, clear any attempts and lockout time
            unset($_SESSION['login_attempts']);
            unset($_SESSION['lockout_time']);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_first_name'] = $user['first_name'];

            header('Location: index.php');
            exit;
        } else {
            // On failed login, increment the attempt counter
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
            }
            $_SESSION['login_attempts']++;

            if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
                // If attempts exceed the max, set the lockout time
                $_SESSION['lockout_time'] = time() + LOCKOUT_TIME;
                unset($_SESSION['login_attempts']); // Reset attempts after lockout
                $remaining_time = LOCKOUT_TIME;
                $error = t('login_locked_out', ['minutes' => ceil($remaining_time / 60)], "Trop de tentatives de connexion. Veuillez réessayer dans {minutes} minutes.");
            } else {
                $error = t('invalid_credentials', 'Identifiants invalides');
            }
        }
    }
}

// Set a variable to indicate that this is the login page
$isLoginPage = true;

require_once CONFIG_PATH . '/templates/header.php';
?>

<div class="container mx-auto mt-10">
    <h1 class="text-3xl font-bold text-center"><?php echo t('login', 'Connexion'); ?></h1>

    <div class="max-w-md mx-auto mt-10 bg-white p-8 border border-gray-300 rounded">
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post" class="mt-6">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('email', 'Email'); ?></label>
                <input type="email" id="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('password', 'Mot de passe'); ?></label>
                <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <?php echo t('login', 'Connexion'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once CONFIG_PATH . '/templates/footer.php';
?>