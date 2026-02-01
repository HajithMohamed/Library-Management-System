<?php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "<h1>Environment Variable Debug</h1>";
echo "<pre>";
echo "CLOUDINARY_CLOUD_NAME: ";
var_dump($_ENV['CLOUDINARY_CLOUD_NAME'] ?? 'NOT SET');
echo "getenv('CLOUDINARY_CLOUD_NAME'): ";
var_dump(getenv('CLOUDINARY_CLOUD_NAME') ?: 'NOT SET');
echo "</pre>";

echo "<h2>All ENV Keys:</h2>";
echo "<pre>";
print_r(array_keys($_ENV));
echo "</pre>";
