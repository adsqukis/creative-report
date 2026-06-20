<?php
// ============================================================
// MIGRATION RUNNER — Dual Mode (Web + CLI)
// 
// CLI:  php migrate.php
// Web:  GET /migrate.php?key=creativeops2026
// ============================================================

$isWeb = (php_sapi_name() !== 'cli');

if ($isWeb) {
    $validKey = 'creativeops2026';
    if (!isset($_GET['key']) || $_GET['key'] !== $validKey) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Invalid key']);
        exit;
    }
    header('Content-Type: text/plain');
}

define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/config/app.php';

$cfg  = require APP_ROOT . '/config/database.php';
$port = $cfg['port'] ?? '3306';
$host = $cfg['host'];
$dsn  = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $cfg['name'] . ';charset=' . $cfg['charset'];

try {
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
    echo "[OK] Koneksi database: {$host}:{$port}/{$cfg['name']}\n\n";
} catch (Exception $e) {
    $msg = "Koneksi database gagal: " . $e->getMessage() . "\n";
    $msg .= "Host: {$host}, Port: {$port}, DB: {$cfg['name']}, User: {$cfg['user']}\n";
    if ($isWeb) {
        http_response_code(500);
        echo $msg;
    } else {
        fwrite(STDERR, $msg);
    }
    exit(1);
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

$ranCount = 0;

foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $applied, true)) {
        echo "[SKIP] {$name} (sudah pernah dijalankan)\n";
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        echo "[SKIP] {$name} (file kosong)\n";
        continue;
    }

    echo "[RUN]  {$name} ... ";
    try {
        $pdo->exec($sql);
        $ins = $pdo->prepare("INSERT INTO co_schema_migrations (filename) VALUES (?)");
        $ins->execute([$name]);
        echo "OK\n";
        $ranCount++;
    } catch (Exception $e) {
        echo "GAGAL\n";
        $msg = "  Error di {$name}: " . $e->getMessage() . "\n";
        $msg .= "  Migration dihentikan. Perbaiki file ini lalu jalankan ulang.\n";
        if ($isWeb) {
            echo $msg;
        } else {
            fwrite(STDERR, $msg);
        }
        exit(1);
    }
}

if ($ranCount === 0) {
    echo "\nTidak ada migration baru. Database sudah up to date.\n";
} else {
    echo "\n{$ranCount} migration(s) berhasil dijalankan.\n";
}

echo "\n--- SELESAI ---\n";
