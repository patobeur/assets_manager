<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (file_exists('../config/config.php')) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_password = $_POST['db_password'];
    $admin_email = $_POST['admin_email'];
    $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);

    // Create the config file
    $config_content = "<?php\n\n";
    $config_content .= "define('DB_HOST', '$db_host');\n";
    $config_content .= "define('DB_NAME', '$db_name');\n";
    $config_content .= "define('DB_USER', '$db_user');\n";
    $config_content .= "define('DB_PASSWORD', '$db_password');\n";

    if (file_put_contents('../config/config.php', $config_content) === false) {
        die("Error: Unable to create the configuration file. Please check folder permissions.");
    }

    // Connect to the database
    try {
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $pdo->exec("USE `$db_name`");

    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

    // Create tables
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('agent', 'admin') NOT NULL
    );

    CREATE TABLE IF NOT EXISTS students (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        barcode VARCHAR(255) NOT NULL UNIQUE
    );

    CREATE TABLE IF NOT EXISTS materials (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        status ENUM('available', 'loaned', 'maintenance') NOT NULL DEFAULT 'available',
        barcode VARCHAR(255) NOT NULL UNIQUE
    );

    CREATE TABLE IF NOT EXISTS loans (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        student_id INT(11) NOT NULL,
        material_id INT(11) NOT NULL,
        loan_date DATETIME NOT NULL,
        return_date DATETIME,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
    );
    ";

    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        die("Error creating tables: " . $e->getMessage());
    }


    // Create the admin user
    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$admin_email, $admin_password]);
    } catch (PDOException $e) {
        die("Error creating admin user: " . $e->getMessage());
    }

    // Redirect to the login page
    header('Location: index.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-4">
        <h1 class="text-3xl font-bold text-center">Installation</h1>

        <div class="max-w-md mx-auto mt-10 bg-white p-8 border border-gray-300 rounded-lg shadow-md">
            <form action="install.php" method="post">
                <h2 class="text-2xl font-bold mb-6">Database Configuration</h2>
                <div class="mb-4">
                    <label for="db_host" class="block text-gray-700 text-sm font-bold mb-2">Host</label>
                    <input type="text" id="db_host" name="db_host" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="db_name" class="block text-gray-700 text-sm font-bold mb-2">Database Name</label>
                    <input type="text" id="db_name" name="db_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="db_user" class="block text-gray-700 text-sm font-bold mb-2">User</label>
                    <input type="text" id="db_user" name="db_user" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-6">
                    <label for="db_password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="db_password" name="db_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <hr class="my-6">

                <h2 class="text-2xl font-bold mb-6">Administrator Account</h2>
                <div class="mb-4">
                    <label for="admin_email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" id="admin_email" name="admin_email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-6">
                    <label for="admin_password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="admin_password" name="admin_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                        Install
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>