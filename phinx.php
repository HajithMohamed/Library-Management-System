<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

return [
    'paths' => [
        'migrations' => [__DIR__ . '/database/migrations'],
        'seeds' => [__DIR__ . '/database/seeds']
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => ($_ENV['DB_HOST'] ?? 'localhost') === 'db' ? 'localhost' : ($_ENV['DB_HOST'] ?? 'localhost'),
            'name' => $_ENV['DB_NAME'] ?? 'integrated_library_system',
            'user' => $_ENV['DB_USER'] ?? 'root',
            'pass' => $_ENV['DB_PASSWORD'] ?? '',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'charset' => 'utf8mb4',
        ]
    ],
    'version_order' => 'creation'
];
