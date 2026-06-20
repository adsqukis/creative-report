<?php
/**
 * One-time database migration trigger.
 * Akses via: GET /install.php?key=creativeops2026
 * Aman dihapus setelah migrate selesai.
 */

// Simple secret key — prevent accidental access
$validKey = 'creativeops2026';

if (!isset($_GET['key']) || $_GET['key'] !== $validKey) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid key']);
    exit;
}

define('APP_ROOT', __DIR__ . '/..');
require_once APP_ROOT . '/config/app.php';

$cfg  = require APP_ROOT . '/config/database.php';
$port = $cfg['port'] ?? '3306';
$dsn  = 'mysql:host=' . $cfg['host'] . ';port=' . $port . ';dbname=' . $cfg['name'] . ';charset=' . $cfg['charset'];

try {
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}

// Create migration tracking table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS co_schema_migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_filename (filename)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$stmt    = $pdo->query("SELECT filename FROM co_schema_migrations");
$applied = $stmt->fetchAll(PDO::FETCH_COLUMN);

$files = glob(APP_ROOT . '/migrations/*.sql');
sort($files);

$results = [];
$ranCount = 0;

foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $applied, true)) {
        $results[] = "SKIP $name (already applied)";
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        $results[] = "SKIP $name (empty file)";
        continue;
    }

    try {
        $pdo->exec($sql);
        $ins = $pdo->prepare("INSERT INTO co_schema_migrations (filename) VALUES (?)");
        $ins->execute([$name]);
        $results[] = "RUN $name ... OK";
        $ranCount++;
    } catch (Exception $e) {
        $results[] = "RUN $name ... FAILED: " . $e->getMessage();
        http_response_code(500);
        echo json_encode(['status' => 'partial', 'results' => $results, 'message' => "Migration failed at $name"]);
        exit;
    }
}

$results[] = $ranCount > 0 ? "$ranCount migration(s) applied successfully." : "No new migrations. Database is up to date.";
echo json_encode(['status' => 'success', 'results' => $results], JSON_PRETTY_PRINT);
