<?php
/**
 * Create E-Resources Tables
 * Run this script once to create the eresources and user_eresources tables.
 * Usage: php src/config/createEResourcesTable.php
 *        OR visit this file via browser
 */

// Load config
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// For local access to Docker MySQL, override host from 'db' to '127.0.0.1'
// and use the external port. Remove these if running inside Docker.
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', '3307');

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/dbConnection.php';

$queries = [];

// Note: users.userId is VARCHAR(255), so submitted_by and approved_by must also be VARCHAR
$queries[] = "
CREATE TABLE IF NOT EXISTS `eresources` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `resource_type` ENUM('pdf', 'link', 'video') NOT NULL DEFAULT 'pdf',
  `resource_url` VARCHAR(500) DEFAULT NULL,
  `file_path` VARCHAR(500) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `submitted_by` VARCHAR(255) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
  `approved_by` VARCHAR(255) DEFAULT NULL,
  `approval_date` DATETIME DEFAULT NULL,
  `rejection_reason` TEXT DEFAULT NULL,
  `download_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`submitted_by`) REFERENCES `users`(`userId`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`userId`) ON DELETE SET NULL,
  KEY `idx_status` (`status`),
  KEY `idx_submitted_by` (`submitted_by`),
  KEY `idx_category` (`category`),
  KEY `idx_resource_type` (`resource_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$queries[] = "
CREATE TABLE IF NOT EXISTS `user_eresources` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(255) NOT NULL,
  `resource_id` INT NOT NULL,
  `obtained_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`userId`) ON DELETE CASCADE,
  FOREIGN KEY (`resource_id`) REFERENCES `eresources`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_resource` (`user_id`, `resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// Check if old e_resources table exists and needs migration
$oldTableCheck = $conn->query("SHOW TABLES LIKE 'e_resources'");
if ($oldTableCheck && $oldTableCheck->num_rows > 0) {
    echo "<p style='color:orange;'>Warning: Old 'e_resources' table detected. You may want to migrate data manually.</p>\n";
    error_log("Old e_resources table detected during eresources setup.");
}

$success = true;
foreach ($queries as $sql) {
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>✓ Executed successfully.</p>\n";
    } else {
        echo "<p style='color:red;'>✗ Error: " . htmlspecialchars($conn->error) . "</p>\n";
        error_log("E-Resources table creation error: " . $conn->error);
        $success = false;
    }
}

// Create upload directory
$uploadDir = APP_ROOT . '/public/assets/uploads/eresources';
if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "<p style='color:green;'>✓ Upload directory created: {$uploadDir}</p>\n";
    } else {
        echo "<p style='color:red;'>✗ Failed to create upload directory.</p>\n";
    }
} else {
    echo "<p style='color:blue;'>ℹ Upload directory already exists.</p>\n";
}

if ($success) {
    echo "<h3 style='color:green;'>E-Resources tables created successfully!</h3>\n";
} else {
    echo "<h3 style='color:red;'>Some errors occurred. Check above.</h3>\n";
}
