<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define the path to the Language class
require_once __DIR__ . '/../config_assets_manager/Language.php';

// Supported languages
$supported_langs = ['fr', 'en'];
$default_lang = 'fr';

// 1. Check for language in URL query
$lang_from_url = $_GET['lang'] ?? null;
if ($lang_from_url && in_array($lang_from_url, $supported_langs)) {
    $_SESSION['lang'] = $lang_from_url;
    // Redirect to remove the lang parameter from the URL
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    $query = $_GET;
    unset($query['lang']);
    if (!empty($query)) {
        $redirect_url .= '?' . http_build_query($query);
    }
    header("Location: " . $redirect_url);
    exit;
}

// 2. Check for language in session
$lang_from_session = $_SESSION['lang'] ?? null;

// 3. Check for language in browser settings
$lang_from_browser = '';
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $lang_from_browser = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
}

// Determine the language to use
$lang = $lang_from_session ?? ($lang_from_browser && in_array($lang_from_browser, $supported_langs) ? $lang_from_browser : $default_lang);

// Store the chosen language in the session
$_SESSION['lang'] = $lang;

// Initialize the Language class
Language::getInstance($lang);
?>