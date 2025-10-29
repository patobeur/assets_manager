<?php
// --- Security Configuration ---
// Prevent displaying errors to the end user.
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// Specify the path to the error log file.
ini_set('error_log', __DIR__ . '/../config_assets_manager/logs/php_errors.log');

// Report all PHP errors.
error_reporting(E_ALL);

// Set session cookie parameters for enhanced security
session_set_cookie_params([
    'lifetime' => 0, // Expire when the browser closes
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // Only send over HTTPS
    'httponly' => true, // Prevent client-side script access
    'samesite' => 'Strict' // CSRF protection
]);
