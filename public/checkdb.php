<?php
// Quick DB check — AMAN DIHAPUS setelah selesai
define('APP_ROOT', __DIR__ . '/..');
require_once APP_ROOT . '/config/app.php';
$cfg = require APP_ROOT . '/config/database.php';
$dsn = 'mysql:host=' . $cfg['host'] . ';port=' . ($cfg['port']??'3306') . ';dbname=' . $cfg['name'] . ';charset=utf8mb4';

header('Content-Type: text/plain');
try {
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    echo "DB OK\n\n";
    
    echo "=== TABLES ===\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo implode(', ', $tables) . "\n\n";
    
    echo "=== USERS ===\n";
    $users = $pdo->query("SELECT id, email, role, is_active FROM co_users")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) echo "  {$u['id']} | {$u['email']} | {$u['role']} | active={$u['is_active']}\n";
    
    echo "\n=== TEST PASSWORD ===\n";
    $row = $pdo->query("SELECT password FROM co_users WHERE email = 'admin@creative-ops.local'")->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $match = password_verify('Admin@2026', $row['password']);
        echo "Password match: " . ($match ? 'YES' : 'NO') . "\n";
        echo "Hash: {$row['password']}\n";
    } else {
        echo "Admin user NOT FOUND!\n";
    }
} catch (Exception $e) {
    echo "DB FAIL: " . $e->getMessage() . "\n";
}
