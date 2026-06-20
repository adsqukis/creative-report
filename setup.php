<?php
// Temporary setup endpoint — run migrations
// Access: /setup.php?key=creative-ops-migrate-2026

$key = $_GET['key'] ?? $_SERVER['HTTP_X_SETUP_KEY'] ?? '';
if ($key !== 'creative-ops-migrate-2026') {
    http_response_code(403);
    die('Access denied.');
}

define('APP_ROOT', __DIR__);
require_once __DIR__ . '/config/app.php';

$cfg  = require __DIR__ . '/config/database.php';
$port = $cfg['port'] ?? '3306';
$dsn  = 'mysql:host=' . $cfg['host'] . ';port=' . $port . ';dbname=' . $cfg['name'] . ';charset=' . $cfg['charset'];

try {
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
} catch (Exception $e) {
    die('DB connection failed: ' . $e->getMessage());
}

// Create migrations tracking table
$pdo->exec("CREATE TABLE IF NOT EXISTS co_schema_migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_filename (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Run migration files
$files = glob(__DIR__ . '/migrations/*.sql');
sort($files);
$results = [];

foreach ($files as $file) {
    $name = basename($file);
    
    // Check if already applied
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM co_schema_migrations WHERE filename = ?");
    $stmt->execute([$name]);
    if ($stmt->fetchColumn() > 0) {
        $results[] = ['file' => $name, 'status' => 'SKIP (already applied)'];
        continue;
    }
    
    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        $results[] = ['file' => $name, 'status' => 'SKIP (empty)'];
        continue;
    }
    try {
        $pdo->exec($sql);
        $ins = $pdo->prepare("INSERT INTO co_schema_migrations (filename) VALUES (?)");
        $ins->execute([$name]);
        $results[] = ['file' => $name, 'status' => 'OK'];
    } catch (Exception $e) {
        $results[] = ['file' => $name, 'status' => 'ERROR: ' . $e->getMessage()];
    }
}

header('Content-Type: application/json');
echo json_encode(['results' => $results, 'admin_email' => 'admin@creative-ops.local'], JSON_PRETTY_PRINT);
