<?php
class Database {
    private static $instance = null;
    private $pdo;
    private $lastInserted = 0;

    private function __construct() {
        $cfg  = require APP_ROOT . '/config/database.php';
        $port = $cfg['port'] ?? '3306';
        $dsn  = 'mysql:host=' . $cfg['host'] . ';port=' . $port . ';dbname=' . $cfg['name'] . ';charset=' . $cfg['charset'];
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ];
        try {
            $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $options);
        } catch(Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                die('DB Error: ' . $e->getMessage());
            }
            die('Koneksi database gagal. Cek config/database.php');
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): array {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                die('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            }
            return [];
        }
    }

    public function row(string $sql, array $params = []): ?array {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch(Exception $e) {
            return null;
        }
    }

    public function execute(string $sql, array $params = []): bool {
        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            $this->lastInserted = (int)$this->pdo->lastInsertId();
            return $result;
        } catch(Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                die('Execute Error: ' . $e->getMessage());
            }
            return false;
        }
    }

    public static function getLastId(): int {
        return self::getInstance()->lastInserted;
    }

    public function count(string $sql, array $params = []): int {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch(Exception $e) {
            return 0;
        }
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }
}
