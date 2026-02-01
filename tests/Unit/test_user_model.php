<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    if (class_exists('App\Models\User')) {
        echo "SUCCESS: App\Models\User Class Found!";
    } else {
        echo "FAILURE: App\Models\User Class NOT Found!";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}
