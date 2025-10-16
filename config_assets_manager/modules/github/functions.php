<?php

function track_login_attempt($attemptDetails)
{
    if (!isset($attemptDetails['status'])) {
        return;
    }

    $logFilePath = __DIR__ . '/login_history.log';

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    $timestamp = date('Y-m-d H:i:s');

    $logEntry = '';

    if ($attemptDetails['status'] === 'success') {
        $logEntry = sprintf(
            "[%s] SUCCESSFUL Login: UserID=%d, Email=%s, IP=%s, UserAgent=%s\n",
            $timestamp,
            $attemptDetails['user_id'],
            $attemptDetails['email'],
            $ipAddress,
            $userAgent
        );
    } elseif ($attemptDetails['status'] === 'failure') {
        $logEntry = sprintf(
            "[%s] FAILED Login Attempt: Email=%s, IP=%s, UserAgent=%s\n",
            $timestamp,
            $attemptDetails['email'],
            $ipAddress,
            $userAgent
        );
    }

    if ($logEntry) {
        // Append to the log file
        file_put_contents($logFilePath, $logEntry, FILE_APPEND);
    }
}
