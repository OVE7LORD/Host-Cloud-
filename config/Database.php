<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'host_cloud_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    // Get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            throw new Exception("Database connection error. Please try again later.");
        }

        return $this->conn;
    }

    // Get PDO instance
    public function getPdo() {
        if ($this->conn === null) {
            $this->getConnection();
        }
        return $this->conn;
    }
}
