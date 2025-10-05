<?php
// classes/Database.php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        global $dbOptions;
        $dsn = "mysql:host={$dbOptions['host']};dbname={$dbOptions['dbname']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $dbOptions['user'], $dbOptions['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public static function getInstance() {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
