<?php
function track_user_login($userId, $email) {
    $logFilePath = __DIR__ . '/login_history.log';

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    $timestamp = date('Y-m-d H:i:s');

    $logEntry = sprintf(
        "[%s] User Login: ID=%d, Email=%s, IP=%s, UserAgent=%s\n",
        $timestamp,
        $userId,
        $email,
        $ipAddress,
        $userAgent
    );

    // Append to the log file
    file_put_contents($logFilePath, $logEntry, FILE_APPEND);
}
?>