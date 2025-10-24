<?php
/**
 * This script consolidates the two vendor folders into one
 * Run this once from command line: php consolidate_vendors.php
 */

$rootVendor = __DIR__ . '/vendor';
$srcVendor = __DIR__ . '/src/vendor';

echo "Consolidating vendor folders...\n\n";

// Check if src/vendor exists
if (!is_dir($srcVendor)) {
    echo "✓ No src/vendor folder found. Root vendor is already being used.\n";
    exit(0);
}

// If root vendor doesn't exist, move src/vendor to root
if (!is_dir($rootVendor)) {
    echo "Moving src/vendor to root...\n";
    rename($srcVendor, $rootVendor);
    echo "✓ Vendor folder moved to root successfully!\n";
} else {
    echo "⚠ Both vendor folders exist. Please manually verify and remove src/vendor if not needed.\n";
    echo "Root vendor contains: " . implode(', ', array_diff(scandir($rootVendor), ['.', '..'])) . "\n";
}

echo "\nDone! Please update your composer.json to be in the root if it isn't already.\n";
?>
