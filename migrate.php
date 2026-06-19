<?php
// ============================================================
// MIGRATION RUNNER
// Jalankan via Railway: railway run php migrate.php
// Atau lokal: php migrate.php
//
// Aman dijalankan berkali-kali — setiap file di migrations/ hanya
// dieksekusi sekali, dicatat di tabel co_schema_migrations.
// ============================================================

define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/config/app.php';

$cfg  = require APP_ROOT . '/config/database.php';
$port = $cfg['port'] ?? '3306';
$dsn  = 'mysql:host=' . $cfg['host'] . ';port=' . $port . ';dbname=' . $cfg['name'] . ';charset=' . $cfg['charset'];

try {
    // PDO::MYSQL_ATTR_MULTI_STATEMENTS enabled here only — migration files are
    // trusted local content (not user input), needed because each .sql file
    // contains multiple statements separated by semicolons.
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
} catch (Exception $e) {
    fwrite(STDERR, "Koneksi database gagal: " . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, "Cek environment variables: MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD" . PHP_EOL);
    exit(1);
}

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
        echo "SKIP  $name (sudah pernah dijalankan)" . PHP_EOL;
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        echo "SKIP  $name (file kosong)" . PHP_EOL;
        continue;
    }

    echo "RUN   $name ... ";
    try {
        $pdo->exec($sql);
        $ins = $pdo->prepare("INSERT INTO co_schema_migrations (filename) VALUES (?)");
        $ins->execute([$name]);
        echo "OK" . PHP_EOL;
        $ranCount++;
    } catch (Exception $e) {
        echo "GAGAL" . PHP_EOL;
        fwrite(STDERR, "  Error di $name: " . $e->getMessage() . PHP_EOL);
        fwrite(STDERR, "  Migration dihentikan. Perbaiki file ini lalu jalankan ulang." . PHP_EOL);
        exit(1);
    }
}

if ($ranCount === 0) {
    echo PHP_EOL . "Tidak ada migration baru. Database sudah up to date." . PHP_EOL;
} else {
    echo PHP_EOL . "$ranCount migration berhasil dijalankan." . PHP_EOL;
}
