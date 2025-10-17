<?php
// This script is included in the header of every page.
// We want to track successful logins.

// Check if user is logged in and if the login has not been tracked yet for this session.
if (isset($isLoginPage) && $isLoginPage && isset($_SESSION['user_id']) && !isset($_SESSION['login_tracked'])) {
    $logFilePath = __DIR__ . '/login_history.log';

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    $timestamp = date('Y-m-d H:i:s');
    $user_first_name = $_SESSION['user_first_name'] ?? 'N/A';
    $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'N/A';
    $referer = $_SERVER['HTTP_REFERER'] ?? 'N/A';
    $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'N/A';
    $remotePort = $_SERVER['REMOTE_PORT'] ?? 'N/A';

    $logEntry = sprintf(
        "[%s] SUCCESSFUL Login Attempt: UserID=%d, user_first_name=%s, IP=%s, UserAgent=%s, forwardedFor=%s, referer=%s, language=%s, Port=%s \n",
        $timestamp,
        $_SESSION['user_id'],
        $user_first_name,
        $ipAddress,
        $userAgent,
        $forwardedFor,
        $referer,
        $language,
        $remotePort
    );

    // Append to the log file
    file_put_contents($logFilePath, $logEntry, FILE_APPEND);

    // Mark this session as tracked to prevent duplicate logging
    $_SESSION['login_tracked'] = true;
}
