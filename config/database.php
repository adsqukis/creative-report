<?php
return [
    'host'    => getenv('DB_HOST')    ?: getenv('MYSQLHOST')    ?: 'localhost',
    'port'    => getenv('DB_PORT')    ?: getenv('MYSQLPORT')    ?: '3306',
    'name'    => getenv('DB_NAME')    ?: getenv('MYSQLDATABASE') ?: 'creative_ops',
    'user'    => getenv('DB_USER')    ?: getenv('MYSQLUSER')    ?: 'root',
    'pass'    => getenv('DB_PASS')    ?: getenv('MYSQLPASSWORD') ?: '',
    'charset' => 'utf8mb4',
];
