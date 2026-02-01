<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Cloudinary\Configuration\Configuration;

try {
    if (class_exists('Cloudinary\Configuration\Configuration')) {
        echo "SUCCESS: Cloudinary Class Found!";
    } else {
        echo "FAILURE: Cloudinary Class NOT Found!";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}
