<?php
return [
    // Railway MySQL plugin injects MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT
    'host'    => getenv('MYSQLHOST') ?: getenv('DB_HOST')    ?: 'localhost',
    'port'    => getenv('MYSQLPORT') ?: getenv('DB_PORT')    ?: '3306',
    'name'    => getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'creative_ops',
    'user'    => getenv('MYSQLUSER') ?: getenv('DB_USER')    ?: 'root',
    'pass'    => getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];