<?php
/**
 * CLAIMORA - Insurance Claim Management System
 * Database Configuration
 */

return [
    // Preferred driver: 'mysql' or 'sqlite'
    'driver' => getenv('DB_DRIVER') ?: 'mysql',
    
    // MySQL Settings (Default XAMPP)
    'mysql' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => getenv('DB_PORT') ?: '3306',
        'dbname'   => getenv('DB_NAME') ?: 'claimora_db',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset'  => 'utf8mb4'
    ],
    
    // SQLite Fallback (Auto-created if MySQL is unreachable or requested)
    'sqlite' => [
        'path' => dirname(__DIR__) . '/database/claimora.sqlite'
    ],

    // Strict Mode PDO Options
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
