<?php

// Prevent direct script access.
if (!defined('APP_LOADED')) {
    http_response_code(403);
    die('Accès non autorisé.');
}

class Database
{
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASSWORD;
    private $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Log the detailed error message to the server's error log
            error_log('Database Connection Error: ' . $exception->getMessage());
            // Set conn to null and let the calling script handle the error
            $this->conn = null;
        }

        return $this->conn;
    }
}
