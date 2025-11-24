<?php
//
class Database {
    private $host = "127.0.0.1";
    private $user = "root";
    private $pass = "root";
    private $dbname = "lernwebseite";
    public $conn;

    public function getConnection() {
        try {
            $dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("❌ Datenbankfehler: ".$e->getMessage());
        }

        return $this->conn;
    }
}
