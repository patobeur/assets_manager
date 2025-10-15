<?php
// Initialize the language system first, as it may start the session
require_once 'language_init.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (file_exists('bootstrap.php')) {
    header('Location: index.php');
    exit;
}

// --- Auto-detection of the configuration path ---
function find_config_path()
{
    // Start searching from the parent directory of the current script's directory.
    // This is more robust, especially for the built-in PHP server.
    $base_dir = dirname(__DIR__);
    $target_dir = $base_dir . '/config_assets_manager';

    if (is_dir($target_dir)) {
        // Replace backslashes with forward slashes for cross-platform compatibility.
        return str_replace('\\', '/', realpath($target_dir));
    }

    // Fallback for other server configurations: search up the directory tree.
    $current_dir = __DIR__;
    for ($i = 0; $i < 5; $i++) {
        $target_dir = $current_dir . '/config_assets_manager';
        if (is_dir($target_dir)) {
            return str_replace('\\', '/', realpath($target_dir));
        }
        $current_dir = dirname($current_dir);
    }

    return false;
}

$config_path = find_config_path();

if ($config_path === false) {
    die(t('critical_error_config_not_found'));
}
// --- End of auto-detection ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input, removing backticks to prevent SQL injection in CREATE DATABASE.
    $db_host = htmlspecialchars(str_replace('`', '', $_POST['db_host']));
    $db_name = htmlspecialchars(str_replace('`', '', $_POST['db_name']));
    $db_user = htmlspecialchars(str_replace('`', '', $_POST['db_user']));
    $db_password = $_POST['db_password']; // No sanitization needed for the password, it will be hashed
    $admin_first_name = htmlspecialchars($_POST['admin_first_name']);
    $admin_last_name = htmlspecialchars($_POST['admin_last_name']);
    $admin_email = filter_var($_POST['admin_email'], FILTER_SANITIZE_EMAIL);
    $admin_password = $_POST['admin_password'];

    // Basic validation
    if (empty($db_host) || empty($db_name) || empty($db_user) || empty($admin_first_name) || empty($admin_last_name) || empty($admin_email) || empty($admin_password)) {
        die(t('error_all_fields_required'));
    }

    if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        die(t('error_invalid_email'));
    }

    $admin_password = password_hash($admin_password, PASSWORD_DEFAULT);

    // Connect to the database
    try {
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $pdo->exec("USE `$db_name`");
    } catch (PDOException $e) {
        die(str_replace('{error_message}', $e->getMessage(), t('db_connection_failed')));
    }

    // Create tables
    $sql = "
    CREATE TABLE IF NOT EXISTS am_users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('agent', 'admin') NOT NULL
    );

    CREATE TABLE IF NOT EXISTS am_promos (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS am_sections (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL
    );


    CREATE TABLE IF NOT EXISTS am_students (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        barcode VARCHAR(255) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        promo_id INT(11) NOT NULL,
        section_id INT(11) NOT NULL,
        status INT(1) NOT NULL DEFAULT 1,
        FOREIGN KEY (promo_id) REFERENCES am_promos(id) ON DELETE CASCADE,
        FOREIGN KEY (section_id) REFERENCES am_sections(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS am_materials_categories (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL
    );
    CREATE TABLE IF NOT EXISTS am_materials_status (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS am_options (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS am_materials (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('available', 'loaned', 'maintenance') NOT NULL DEFAULT 'available',
        barcode VARCHAR(255) NOT NULL UNIQUE,
        material_categories_id INT(11) NOT NULL,
        material_status_id INT(11) NOT NULL,
        FOREIGN KEY (material_categories_id) REFERENCES am_materials_categories(id) ON DELETE CASCADE,
        FOREIGN KEY (material_categories_id) REFERENCES am_materials_status(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS am_loans (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        student_id INT(11) NOT NULL,
        material_id INT(11) NOT NULL,
        loan_date DATETIME NOT NULL,
        return_date DATETIME,
        loan_user_id INT(11) NOT NULL,
        return_user_id INT(11),
        FOREIGN KEY (student_id) REFERENCES am_students(id) ON DELETE CASCADE,
        FOREIGN KEY (material_id) REFERENCES am_materials(id) ON DELETE CASCADE,
        FOREIGN KEY (loan_user_id) REFERENCES am_users(id) ON DELETE CASCADE,
        FOREIGN KEY (return_user_id) REFERENCES am_users(id) ON DELETE CASCADE
    );
    ";

    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        die(str_replace('{error_message}', $e->getMessage(), t('table_creation_error')));
    }

    // Insert default data
    try {
        $pdo->exec("INSERT INTO am_promos (title) VALUES ('00-00'), ('25-27');");
        $pdo->exec("INSERT INTO am_sections (title) VALUES ('Bachelor RC'), ('BTS COM');");
        $pdo->exec("INSERT INTO am_materials_status (title) VALUES ('Available'), ('Loaned', ('Maintenance');");
        $pdo->exec("INSERT INTO am_materials_categories (title) VALUES ('Ordinateur Portable'), ('Matériels informatique'), ('Matériels de bureau');");
    } catch (PDOException $e) {
        die(str_replace('{error_message}', $e->getMessage(), t('default_data_error')));
    }


    // Create the admin user
    try {
        $stmt = $pdo->prepare("INSERT INTO am_users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute([$admin_first_name, $admin_last_name, $admin_email, $admin_password]);
    } catch (PDOException $e) {
        die(str_replace('{error_message}', $e->getMessage(), t('admin_creation_error')));
    }

    $config_path_from_form = rtrim($_POST['config_path'], '/\\');

    // Security check: ensure the path from the form is the same as the one we detected.
    if ($config_path_from_form !== $config_path) {
        die(t('security_error_path_mismatch'));
    }

    // Create the main config file in the specified path
    $config_content = "<?php\n\n";
    $config_content .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
    $config_content .= "define('DB_NAME', '" . addslashes($db_name) . "');\n";
    $config_content .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
    $config_content .= "define('DB_PASSWORD', '" . addslashes($db_password) . "');\n";

    if (file_put_contents($config_path . '/config.php', $config_content) === false) {
        die(str_replace('{path}', htmlspecialchars($config_path), t('config_file_creation_error')));
    }

    // Create the bootstrap file in the public directory
    $bootstrap_content = "<?php\n\n";
    $bootstrap_content .= "// Prevent direct script access.\n";
    $bootstrap_content .= "if (!defined('APP_LOADED')) {\n";
    $bootstrap_content .= "    die('Accès non autorisé.');\n";
    $bootstrap_content .= "}\n\n";
    $bootstrap_content .= "// This file is generated automatically by the installer.\n";
    $bootstrap_content .= "// It contains the absolute path to the configuration directory.\n";
    $bootstrap_content .= "define('CONFIG_PATH', '" . addslashes($config_path) . "');\n";

    if (file_put_contents('bootstrap.php', $bootstrap_content) === false) {
        // Attempt to clean up the main config file if bootstrap fails
        unlink($config_path . '/config.php');
        die(t('bootstrap_file_creation_error'));
    }

    // Redirect to the login page
    header('Location: index.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="<?php echo Language::getInstance()->getLang(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('installation', 'Installation'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-4">
        <h1 class="text-3xl font-bold text-center"><?php echo t('installation', 'Installation'); ?></h1>

        <div class="max-w-md mx-auto mt-10 bg-white p-8 border border-gray-300 rounded-lg shadow-md">
            <form action="install.php" method="post">
                <h2 class="text-2xl font-bold mb-6"><?php echo t('database_configuration', 'Configuration de la base de données'); ?></h2>
                <div class="mb-4">
                    <label for="db_host" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('host', 'Hôte'); ?></label>
                    <input value="localhost" type="text" id="db_host" name="db_host" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="db_name" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('database_name', 'Nom de la base de données'); ?></label>
                    <input value="assets_manager" type="text" id="db_name" name="db_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="db_user" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('user', 'Utilisateur'); ?></label>
                    <input value="root" type="text" id="db_user" name="db_user" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-6">
                    <label for="db_password" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('password', 'Mot de passe'); ?></label>
                    <input type="password" id="db_password" name="db_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <hr class="my-6">

                <h2 class="text-2xl font-bold mb-6"><?php echo t('configuration_path', 'Chemin de configuration'); ?></h2>
                <div class="mb-4 bg-gray-100 p-4 rounded">
                    <p class="text-sm text-gray-700"><?php echo t('config_path_detected'); ?></p>
                    <p class="text-sm font-mono bg-white p-2 mt-2 rounded"><strong><?php echo htmlspecialchars($config_path); ?></strong></p>
                    <input type="hidden" name="config_path" value="<?php echo htmlspecialchars($config_path); ?>">
                </div>

                <hr class="my-6">

                <h2 class="text-2xl font-bold mb-6"><?php echo t('admin_account', 'Compte administrateur'); ?></h2>
                <div class="mb-4">
                    <label for="admin_first_name" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('first_name', 'Prénom'); ?></label>
                    <input value="Patobeur" type="text" id="admin_first_name" name="admin_first_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="admin_last_name" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('last_name', 'Nom'); ?></label>
                    <input value="Etlardons" type="text" id="admin_last_name" name="admin_last_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="admin_email" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('email', 'Email'); ?></label>
                    <input value="patobeur@patobeur.pat" type="email" id="admin_email" name="admin_email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-6">
                    <label for="admin_password" class="block text-gray-700 text-sm font-bold mb-2"><?php echo t('password', 'Mot de passe'); ?></label>
                    <input type="password" id="admin_password" name="admin_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                        <?php echo t('install', 'Installer'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>