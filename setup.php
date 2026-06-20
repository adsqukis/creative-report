<?php
// Temporary setup endpoint — run migrations
// Access: POST /setup/run-migration with header X-Setup-Key: creative-ops-migrate-2026

$key = $_SERVER['HTTP_X_SETUP_KEY'] ?? '';
if ($key !== 'creative-ops-migrate-2026') {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid key']));
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
    die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
}

// Run migration files
$files = glob(__DIR__ . '/migrations/*.sql');
sort($files);
$results = [];

foreach ($files as $file) {
    $name = basename($file);
    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        $results[] = ['file' => $name, 'status' => 'SKIP (empty)'];
        continue;
    }
    try {
        $pdo->exec($sql);
        $results[] = ['file' => $name, 'status' => 'OK'];
    } catch (Exception $e) {
        $results[] = ['file' => $name, 'status' => 'ERROR: ' . $e->getMessage()];
    }
}

header('Content-Type: application/json');
echo json_encode(['results' => $results], JSON_PRETTY_PRINT);
