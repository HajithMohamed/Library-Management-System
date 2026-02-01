<?php

require_once __DIR__ . '/vendor/autoload.php';

$phinx_bin = __DIR__ . '/vendor/bin/phinx';
if (!file_exists($phinx_bin)) {
    die("\033[31mError: Phinx is not installed. Please run 'composer install' first.\033[0m\n");
}

$command = $argv[1] ?? 'status';
$env = 'development';

// Parse arguments for environment
foreach ($argv as $arg) {
    if (strpos($arg, '--env=') === 0) {
        $env = substr($arg, 6);
    }
}

switch ($command) {
    case 'up':
        echo "\033[32mRunning migrations for environment: $env...\033[0m\n";
        passthru("php \"$phinx_bin\" migrate -e $env");
        break;

    case 'down':
        echo "\033[33mRolling back last migration for environment: $env...\033[0m\n";
        passthru("php \"$phinx_bin\" rollback -e $env");
        break;

    case 'status':
        echo "\033[34mMigration Status (Environment: $env):\033[0m\n";
        passthru("php \"$phinx_bin\" status -e $env");
        break;

    case 'create':
        $name = $argv[2] ?? null;
        if (!$name) {
            die("\033[31mError: Missing migration name. Usage: php migrate.php create MigrationName\033[0m\n");
        }
        echo "\033[32mCreating new migration: $name...\033[0m\n";
        passthru("php \"$phinx_bin\" create $name");
        break;

    case 'help':
    default:
        echo "Library Management System - Migration CLI\n";
        echo "Usage: php migrate.php [command] [options]\n\n";
        echo "Commands:\n";
        echo "  up             Run all pending migrations\n";
        echo "  down           Rollback the last migration\n";
        echo "  status         Show the current migration status\n";
        echo "  create [Name]  Create a new migration file\n\n";
        echo "Options:\n";
        echo "  --env=[name]   Specify environment (development, testing, production)\n";
        break;
}
