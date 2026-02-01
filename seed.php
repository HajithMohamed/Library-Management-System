<?php

require_once __DIR__ . '/vendor/autoload.php';

$phinx_bin = __DIR__ . '/vendor/bin/phinx';
if (!file_exists($phinx_bin)) {
    die("\033[31mError: Phinx is not installed. Please run 'composer install' first.\033[0m\n");
}

$env = 'development';
$seeder = null;
$force = false;

// Parse arguments
foreach ($argv as $arg) {
    if (strpos($arg, '--env=') === 0) {
        $env = substr($arg, 6);
    } elseif (strpos($arg, '--class=') === 0) {
        $seeder = substr($arg, 8);
    } elseif ($arg === '--force' || $arg === '-f') {
        $force = true;
    }
}

echo "\033[34mLibrary Management System - Seeder CLI\033[0m\n";
echo "Environment: $env\n";

if (!$force && $env === 'production') {
    echo "\033[31mWARNING: You are about to seed the PRODUCTION database.\033[0m\n";
    echo "Are you sure you want to proceed? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) != 'y') {
        echo "Aborting.\n";
        exit;
    }
}

if ($seeder) {
    echo "\033[32mRunning seeder: $seeder...\033[0m\n";
    passthru("php \"$phinx_bin\" seed:run -e $env -s $seeder");
} else {
    echo "\033[32mRunning all seeders...\033[0m\n";
    passthru("php \"$phinx_bin\" seed:run -e $env");
}

echo "\n\033[32mSeeding completed successfully.\033[0m\n";
