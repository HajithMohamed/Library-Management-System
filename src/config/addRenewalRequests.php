<?php
/**
 * Migration: Create renewal_requests table
 */

// Load config first to get $mysqli
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/dbConnection.php';

global $conn;

$sql = "CREATE TABLE IF NOT EXISTS `renewal_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` varchar(255) NOT NULL COMMENT 'Transaction ID from transactions table',
  `userId` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `currentDueDate` date NOT NULL,
  `requestedDueDate` date NOT NULL,
  `reason` text NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `adminId` varchar(255) NULL COMMENT 'Admin who handled the request',
  `adminNote` text NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tid` (`tid`),
  KEY `idx_userId` (`userId`),
  KEY `idx_status` (`status`),
  CONSTRAINT `renewal_requests_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `renewal_requests_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE,
  CONSTRAINT `renewal_requests_ibfk_3` FOREIGN KEY (`tid`) REFERENCES `transactions` (`tid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql)) {
    echo "renewal_requests table created successfully.\n";
} else {
    echo "Error creating renewal_requests table: " . $conn->error . "\n";
}
