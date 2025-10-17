<?php
// This script is included in the footer of every page.
// We only want to track failed login attempts, so we check if we are on the login page

// and if a login attempt has failed.
if (isset($isLoginPage) && $isLoginPage && isset($error) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $logFilePath = __DIR__ . '/login_history.log';

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    $timestamp = date('Y-m-d H:i:s');
    $email = $_POST['email'] ?? 'N/A';
    $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'N/A';
    $referer = $_SERVER['HTTP_REFERER'] ?? 'N/A';
    $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'N/A';
    $remotePort = $_SERVER['REMOTE_PORT'] ?? 'N/A';

    $logEntry = sprintf(
        "[%s] FAILED Login Attempt: Email=%s, IP=%s, UserAgent=%s, forwardedFor=%s, referer=%s, language=%s, Port=%s \n",
        $timestamp,
        $email,
        $ipAddress,
        $userAgent,
        $forwardedFor,
        $referer,
        $language,
        $remotePort
    );

    // Append to the log file
    file_put_contents($logFilePath, $logEntry, FILE_APPEND);
}
