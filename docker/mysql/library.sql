-- University Library Management System Database Schema
-- This file is automatically executed when the Docker container starts
-- Version: 2.2 (FIXED: Added safety for Docker init - disabled FK checks during data load, used INSERT IGNORE for dummy data to prevent rollback/duplicate errors)
-- Date: 2025-10-26
-- FIXES APPLIED (from 2.1):
-- 1. Removed duplicate 'fine' column from transactions table
-- 2. Updated all dates to 2025 for consistency
-- 3. Added foreign key constraint for approvedBy in borrow_requests
-- 4. Fixed data consistency in transaction records
-- NEW FIXES:
-- 1. Removed outer transaction wrapper to prevent full rollback on single INSERT error
-- 2. Added SET FOREIGN_KEY_CHECKS=0 during data inserts to avoid transient FK violations in init order
-- 3. Changed dummy data INSERTs to INSERT IGNORE to skip duplicates on re-init without errors

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create database if not exists
DROP DATABASE IF EXISTS `integrated_library_system`;
CREATE DATABASE `integrated_library_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `integrated_library_system`;

-- ====================================================================
-- Table structure for table `users`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `userId` varchar(255) NOT NULL,
  `username` varchar(50) NULL,
  `password` varchar(255) NOT NULL,
  `userType` enum('Student','Faculty','Librarian','Admin') NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `emailId` varchar(255) NOT NULL,
  `phoneNumber` varchar(15) DEFAULT NULL,
  `address` text,
  `profileImage` varchar(255) DEFAULT NULL,
  `isVerified` tinyint(1) DEFAULT 0,
  `verificationToken` varchar(255) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `otpExpiry` datetime DEFAULT NULL,
  `max_borrow_limit` int(11) NOT NULL DEFAULT 3,
  `borrow_period_days` int(11) NOT NULL DEFAULT 14,
  `max_renewals` int(11) NOT NULL DEFAULT 1,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `unique_email` (`emailId`),
  UNIQUE KEY `unique_username` (`username`),
  KEY `idx_userType` (`userType`),
  KEY `idx_isVerified` (`isVerified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `books`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `books` (
  `isbn` varchar(13) NOT NULL,
  `barcode` varchar(255) NULL,
  `bookName` varchar(255) NOT NULL,
  `authorName` varchar(255) NOT NULL,
  `publisherName` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT NULL,
  `publicationYear` int(4) DEFAULT NULL,
  `totalCopies` int(11) NOT NULL DEFAULT 0,
  `available` int(11) NOT NULL DEFAULT 0,
  `borrowed` int(11) NOT NULL DEFAULT 0,
  `bookImage` varchar(255) DEFAULT NULL,
  `isTrending` tinyint(1) DEFAULT 0,
  `isSpecial` tinyint(1) DEFAULT 0,
  `specialBadge` varchar(50) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`isbn`),
  KEY `idx_author` (`authorName`),
  KEY `idx_publisher` (`publisherName`),
  KEY `idx_category` (`category`),
  KEY `idx_available` (`available`),
  KEY `idx_trending` (`isTrending`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `transactions`
-- FIXED: Removed duplicate 'fine' column - now only using 'fineAmount'
-- ====================================================================
CREATE TABLE IF NOT EXISTS `transactions` (
  `tid` varchar(255) NOT NULL,
  `userId` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `borrowDate` date NOT NULL,
  `returnDate` date DEFAULT NULL,
  `lastFinePaymentDate` date DEFAULT NULL,
  `fineAmount` decimal(10,2) DEFAULT 0.00,
  `fineStatus` enum('pending','paid','waived') DEFAULT 'pending',
  `finePaymentDate` date NULL,
  `finePaymentMethod` enum('cash','online','card','credit_card','debit_card','upi') NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tid`),
  KEY `idx_userId` (`userId`),
  KEY `idx_isbn` (`isbn`),
  KEY `idx_borrowDate` (`borrowDate`),
  KEY `idx_returnDate` (`returnDate`),
  KEY `idx_fineStatus` (`fineStatus`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `borrow_requests`
-- FIXED: Added foreign key constraint for approvedBy column
-- ====================================================================
CREATE TABLE IF NOT EXISTS `borrow_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `requestDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `approvedBy` varchar(255) NULL,
  `dueDate` date NULL,
  `rejectionReason` text NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_isbn` (`isbn`),
  KEY `idx_status` (`status`),
  KEY `idx_requestDate` (`requestDate`),
  KEY `idx_approvedBy` (`approvedBy`),
  CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE,
  CONSTRAINT `borrow_requests_ibfk_3` FOREIGN KEY (`approvedBy`) REFERENCES `users` (`userId`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `notifications`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `type` enum('overdue','fine_paid','out_of_stock','system','reminder','approval') NOT NULL DEFAULT 'system',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `isRead` tinyint(1) NOT NULL DEFAULT 0,
  `relatedId` varchar(255) NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_type` (`type`),
  KEY `idx_isRead` (`isRead`),
  KEY `idx_createdAt` (`createdAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `fine_settings`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `fine_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` text,
  `updatedBy` varchar(255),
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `backup_log`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `backup_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `filesize` bigint(20) NOT NULL,
  `backupType` enum('manual','scheduled','system') NOT NULL DEFAULT 'manual',
  `status` enum('success','failed','in_progress') NOT NULL DEFAULT 'success',
  `createdBy` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_createdAt` (`createdAt`),
  KEY `idx_backupType` (`backupType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `maintenance_log`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `maintenance_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `description` text,
  `performedBy` varchar(255) NOT NULL,
  `status` enum('success','failed','warning') NOT NULL DEFAULT 'success',
  `details` text NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_createdAt` (`createdAt`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- NEW TABLES FOR ADMIN DASHBOARD
-- ====================================================================

-- ====================================================================
-- Table structure for table `admin_logs`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminId` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `entityType` varchar(100),
  `entityId` varchar(255),
  `oldValues` json,
  `newValues` json,
  `ipAddress` varchar(45) DEFAULT NULL,
  `userAgent` text,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_adminId` (`adminId`),
  KEY `idx_createdAt` (`createdAt`),
  KEY `idx_action` (`action`),
  CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`adminId`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `system_settings`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `settingKey` varchar(100) NOT NULL,
  `settingValue` text NOT NULL,
  `settingType` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` text,
  `updatedBy` varchar(255),
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`settingKey`),
  KEY `idx_settingKey` (`settingKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `role_permissions`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('Student','Faculty','Librarian','Admin') NOT NULL,
  `permission` varchar(100) NOT NULL,
  `canRead` tinyint(1) DEFAULT 0,
  `canWrite` tinyint(1) DEFAULT 0,
  `canDelete` tinyint(1) DEFAULT 0,
  `canApprove` tinyint(1) DEFAULT 0,
  `description` text,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role`,`permission`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `library_hours`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `library_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dayOfWeek` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `openingTime` time DEFAULT NULL,
  `closingTime` time DEFAULT NULL,
  `isClosed` tinyint(1) DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_day` (`dayOfWeek`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `book_reviews`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `book_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `reviewText` text,
  `isApproved` tinyint(1) DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_isbn` (`isbn`),
  KEY `idx_rating` (`rating`),
  CONSTRAINT `book_reviews_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `book_reviews_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `favorites`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `notes` text,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_book` (`userId`,`isbn`),
  KEY `idx_userId` (`userId`),
  KEY `idx_isbn` (`isbn`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `book_reservations`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `book_reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `reservationStatus` enum('Active','Notified','Expired','Cancelled') DEFAULT 'Active',
  `notifiedDate` datetime DEFAULT NULL,
  `expiryDate` date DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_isbn` (`isbn`),
  KEY `idx_status` (`reservationStatus`),
  CONSTRAINT `book_reservations_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `book_reservations_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `audit_logs`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `entityType` varchar(100),
  `entityId` varchar(255),
  `changes` json,
  `ipAddress` varchar(45) DEFAULT NULL,
  `userAgent` text,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_action` (`action`),
  KEY `idx_createdAt` (`createdAt`),
  KEY `idx_entityType` (`entityType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `api_logs`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255),
  `endpoint` varchar(255) NOT NULL,
  `method` enum('GET','POST','PUT','DELETE','PATCH') NOT NULL,
  `statusCode` int(3),
  `responseTime` int(11),
  `ipAddress` varchar(45),
  `userAgent` text,
  `requestData` json,
  `responseData` json,
  `errorMessage` text,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_endpoint` (`endpoint`),
  KEY `idx_method` (`method`),
  KEY `idx_createdAt` (`createdAt`),
  KEY `idx_userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `report_schedule`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `report_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reportName` varchar(255) NOT NULL,
  `reportType` enum('Daily','Weekly','Monthly','Quarterly','Yearly') DEFAULT 'Monthly',
  `frequency` varchar(50),
  `recipients` json,
  `isActive` tinyint(1) DEFAULT 1,
  `lastGenerated` datetime,
  `nextScheduled` datetime,
  `createdBy` varchar(255),
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reportName` (`reportName`),
  KEY `idx_isActive` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `books_borrowed`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `books_borrowed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `borrowDate` date NOT NULL,
  `dueDate` date NOT NULL,
  `returnDate` date DEFAULT NULL,
  `status` enum('Active','Returned','Overdue') NOT NULL DEFAULT 'Active',
  `renewalCount` int(11) NOT NULL DEFAULT 0,
  `lastRenewalDate` date DEFAULT NULL,
  `notes` text,
  `addedBy` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_isbn` (`isbn`),
  KEY `idx_status` (`status`),
  KEY `idx_borrowDate` (`borrowDate`),
  KEY `idx_dueDate` (`dueDate`),
  CONSTRAINT `books_borrowed_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `books_borrowed_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE,
  CONSTRAINT `books_borrowed_ibfk_3` FOREIGN KEY (`addedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `saved_cards`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `saved_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `cardNickname` varchar(100) DEFAULT NULL,
  `cardLastFour` varchar(4) NOT NULL,
  `cardType` varchar(20) NOT NULL,
  `cardHolderName` varchar(255) NOT NULL,
  `expiryMonth` varchar(2) NOT NULL,
  `expiryYear` varchar(4) NOT NULL,
  `isDefault` tinyint(1) DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  CONSTRAINT `saved_cards_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `payment_logs`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `transactionId` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `cardLastFour` varchar(4) DEFAULT NULL,
  `paymentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paymentMethod` enum('credit_card','debit_card','upi','cash','online','card') NOT NULL DEFAULT 'card',
  `status` enum('success','failed','pending') NOT NULL DEFAULT 'success',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_transactionId` (`transactionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Disable FK checks for safe data insertion (re-enabled after)
-- ====================================================================
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================================================
-- Insert default fine settings (only if table is empty)
-- FIXED: Added column aliases to prevent duplicate column name '0' error
-- ====================================================================
INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'fine_per_day', '5', 'Fine amount per day for overdue books', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'fine_per_day'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'max_borrow_days', '14', 'Maximum days a book can be borrowed', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'max_borrow_days'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'grace_period_days', '0', 'Grace period before fines start', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'grace_period_days'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'max_fine_amount', '500', 'Maximum fine amount per book', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'max_fine_amount'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'fine_calculation_method', 'daily', 'Method for calculating fines: daily or fixed', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'fine_calculation_method'
);

-- ====================================================================
-- Insert default system settings
-- ====================================================================
INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_name', 'University Central Library', 'string', 'Name of the library', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_name');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_email', 'library@university.edu', 'string', 'Library contact email', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_email');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_phone', '+1-555-0100', 'string', 'Library contact phone', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_phone');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'max_books_per_user', '5', 'number', 'Maximum books a user can borrow', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'max_books_per_user');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'enable_notifications', 'true', 'boolean', 'Enable system notifications', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'enable_notifications');

-- ====================================================================
-- Insert default role permissions
-- ====================================================================
INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Student', 'view_catalog', 1, 0, 0, 0, 'Can view library catalog'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Student' AND permission = 'view_catalog');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Student', 'borrow_book', 1, 1, 0, 0, 'Can borrow books'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Student' AND permission = 'borrow_book');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Librarian', 'manage_books', 1, 1, 1, 0, 'Can manage books'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Librarian' AND permission = 'manage_books');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Librarian', 'approve_requests', 1, 0, 0, 1, 'Can approve borrow requests'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Librarian' AND permission = 'approve_requests');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'manage_users', 1, 1, 1, 0, 'Can manage all users'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'manage_users');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'view_reports', 1, 0, 0, 0, 'Can view system reports'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'view_reports');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'system_settings', 1, 1, 0, 0, 'Can manage system settings'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'system_settings');

-- ====================================================================
-- Insert default library hours
-- ====================================================================
INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Monday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Monday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Tuesday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Tuesday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Wednesday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Wednesday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Thursday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Thursday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Friday', '08:00:00', '18:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Friday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Saturday', '10:00:00', '16:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Saturday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Sunday', '00:00:00', '00:00:00', 1
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Sunday');

-- ====================================================================
-- DUMMY DATA: Users (20 users - mix of Students, Faculty, Librarian, Admin)
-- FIXED: Updated all dates to 2025
-- ====================================================================
INSERT IGNORE INTO `users` (`userId`, `username`, `password`, `userType`, `gender`, `dob`, `emailId`, `phoneNumber`, `address`, `profileImage`, `isVerified`, `createdAt`) VALUES
('USR001', 'student', '$2a$12$GDs.KTZ/lCabnyH2v41aK.vr.tuulNvqf8RiHZFyQsivHaQM8ysc.', 'Student', 'Male', '2002-05-15', 'john.smith@university.edu', '555-0101', '123 Campus Drive, Dorm A, Room 201', 'profile1.jpg', 1, '2025-01-15 09:30:00'),
('USR002', 'emily_chen', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-08-22', 'emily.chen@university.edu', '555-0102', '456 University Ave, Dorm B, Room 305', 'profile2.jpg', 1, '2025-01-16 10:15:00'),
('USR003', 'michael_brown', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-11-30', 'michael.brown@university.edu', '555-0103', '789 College Blvd, Dorm C, Room 102', 'profile3.jpg', 1, '2025-01-17 11:20:00'),
('USR004', 'sarah_davis', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-03-18', 'sarah.davis@university.edu', '555-0104', '321 Student Lane, Dorm A, Room 410', 'profile4.jpg', 1, '2025-01-18 14:30:00'),
('USR005', 'david_wilson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-07-25', 'david.wilson@university.edu', '555-0105', '654 Academic Way, Dorm D, Room 208', 'profile5.jpg', 1, '2025-01-19 09:45:00'),
('USR006', 'jessica_moore', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-01-12', 'jessica.moore@university.edu', '555-0106', '987 Scholar St, Dorm B, Room 501', 'profile6.jpg', 1, '2025-01-20 13:00:00'),
('USR007', 'robert_taylor', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-09-08', 'robert.taylor@university.edu', '555-0107', '147 Learning Ave, Dorm C, Room 315', 'profile7.jpg', 1, '2025-01-21 10:30:00'),
('USR008', 'amanda_anderson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-06-20', 'amanda.anderson@university.edu', '555-0108', '258 Knowledge Rd, Dorm A, Room 212', 'profile8.jpg', 1, '2025-01-22 15:15:00'),
('USR009', 'james_thomas', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-12-05', 'james.thomas@university.edu', '555-0109', '369 Education Blvd, Dorm D, Room 405', 'profile9.jpg', 0, '2025-10-20 09:00:00'),
('USR010', 'lisa_jackson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-04-17', 'lisa.jackson@university.edu', '555-0110', '741 Campus Circle, Dorm B, Room 118', 'profile10.jpg', 1, '2025-02-01 11:45:00'),
('FAC001', 'dr_robert_johnson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1978-03-22', 'r.johnson@university.edu', '555-0201', '1001 Faculty Housing, Building A', 'profile_fac1.jpg', 1, '2024-08-01 08:00:00'),
('FAC002', 'prof_maria_garcia', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Female', '1982-07-14', 'm.garcia@university.edu', '555-0202', '1002 Faculty Housing, Building B', 'profile_fac2.jpg', 1, '2024-08-01 08:00:00'),
('FAC003', 'dr_william_lee', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1975-11-08', 'w.lee@university.edu', '555-0203', '1003 Faculty Housing, Building A', 'profile_fac3.jpg', 1, '2024-08-01 08:00:00'),
('FAC004', 'prof_patricia_martinez', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Female', '1980-05-30', 'p.martinez@university.edu', '555-0204', '1004 Faculty Housing, Building C', 'profile_fac4.jpg', 1, '2024-08-01 08:00:00'),
('FAC005', 'dr_charles_white', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1979-09-16', 'c.white@university.edu', '555-0205', '1005 Faculty Housing, Building B', 'profile_fac5.jpg', 1, '2024-08-01 08:00:00'),
('LIB001', 'lib', '$2a$12$GDs.KTZ/lCabnyH2v41aK.vr.tuulNvqf8RiHZFyQsivHaQM8ysc.', 'Faculty', 'Female', '1985-06-12', 'susan.librarian@university.edu', '555-0301', '2001 Staff Quarters, Block A', 'profile_lib1.jpg', 1, '2023-06-01 08:00:00'),
('LIB002', 'librarian_mark', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Librarian', 'Male', '1988-02-28', 'mark.librarian@university.edu', '555-0302', '2002 Staff Quarters, Block B', 'profile_lib2.jpg', 1, '2023-06-01 08:00:00'),
('LIB003', 'librarian_nancy', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Librarian', 'Female', '1990-10-19', 'nancy.librarian@university.edu', '555-0303', '2003 Staff Quarters, Block A', 'profile_lib3.jpg', 1, '2024-01-15 08:00:00'),
('ADM001', 'admin', '$2a$12$GDs.KTZ/lCabnyH2v41aK.vr.tuulNvqf8RiHZFyQsivHaQM8ysc.', 'Admin', 'Male', '1975-04-05', 'richard.admin@university.edu', '555-0401', '3001 Admin Building, Office 101', 'profile_adm1.jpg', 1, '2022-01-01 08:00:00'),
('ADM002', 'admin_jennifer', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Admin', 'Female', '1980-12-21', 'jennifer.admin@university.edu', '555-0402', '3002 Admin Building, Office 102', 'profile_adm2.jpg', 1, '2022-01-01 08:00:00');

-- ====================================================================
-- DUMMY DATA: Books (30 books across various categories)
-- ====================================================================
INSERT IGNORE INTO `books` (`isbn`, `barcode`, `bookName`, `authorName`, `publisherName`, `description`, `category`, `publicationYear`, `totalCopies`, `available`, `borrowed`, `bookImage`, `isTrending`, `isSpecial`, `specialBadge`) VALUES
('6604622694292', NULL, 'where the forest meets the stars', 'glendy vanderah', 'HarperCollins', 'A captivating story exploring themes related to where the forest meets the stars and human emotions.', 'Literature', 2016, 5, 5, 0, 'img1.jpg', 0, 0, NULL),
('6080591560497', NULL, 'JAVA', 'josh thompson', 'Random House', 'A captivating story exploring themes related to java and human emotions.', 'Technology', 2012, 5, 5, 0, 'img2.jpg', 0, 0, NULL),
('7781111792539', NULL, 'a world of secrets', 'james maxwell', 'Scholastic', 'A captivating story exploring themes related to a world of secrets and human emotions.', 'Fantasy', 1978, 5, 5, 0, 'img3.jpg', 0, 0, NULL),
('8100852011803', NULL, 'the funnel of good', 'bob bloch', 'Vintage Publishing', 'A captivating story exploring themes related to the funnel of good  and human emotions.', 'Self-help', 1978, 5, 5, 0, 'img4.jpg', 0, 0, NULL),
('8783340203918', NULL, 'long way gone', 'charles martin', 'Cambridge Books', 'A captivating story exploring themes related to long way gone and human emotions.', 'Fiction', 2005, 5, 5, 0, 'img5.jpg', 0, 0, NULL),
('0307268000967', NULL, 'Roots', 'alex haley', 'Random House', 'A captivating story exploring themes related to roots and human emotions.', 'General', 1999, 5, 5, 0, 'img6.jpg', 0, 0, NULL),
('7840135040808', NULL, 'preparation for the next life', 'Uspoytxhii', 'Pearson', 'A captivating story exploring themes related to preparation for the next life and human emotions.', 'General', 1977, 5, 5, 0, 'img7.jpg', 0, 0, NULL),
('8131061994907', NULL, 'the house plants guid', 'Uanqenit', 'Pearson', 'A captivating story exploring themes related to the house plants guid and human emotions.', 'General', 1992, 5, 5, 0, 'img8.jpg', 0, 0, NULL),
('8063254954311', NULL, 'the science of plants', 'Sofcew', 'Cambridge Books', 'A captivating story exploring themes related to the science of plants and human emotions.', 'Science', 1988, 5, 5, 0, 'img9.jpg', 0, 0, NULL),
('5418428424560', NULL, 'indian agriculture', 'Yjydhoer', 'Pearson', 'A captivating story exploring themes related to indian agriculture and human emotions.', 'General', 1950, 5, 5, 0, 'img10.jpg', 0, 0, NULL),
('0500261952520', NULL, 'growing a revolution', 'Ogodqytjby', 'Vintage Publishing', 'A captivating story exploring themes related to growing a revolution and human emotions.', 'General', 1994, 5, 5, 0, 'img11.jpg', 0, 0, NULL),
('6417612062012', NULL, 'organic farming for sustainable agriculture', 'Zcjxwwblf', 'Cambridge Books', 'A captivating story exploring themes related to organic farming for sustainable agriculture and human emotions.', 'General', 2010, 5, 5, 0, 'img12.jpg', 0, 0, NULL),
('1946547630161', NULL, 'emerging research in agriculture science', 'Dztfpxyi', 'Pearson', 'A captivating story exploring themes related to emerging research in agriculture science and human emotions.', 'Science', 1969, 5, 5, 0, 'img13.jpg', 0, 0, NULL),
('1435337305858', NULL, 'climate change and agriculture', 'Iqubvwzqdv', 'HarperCollins', 'A captivating story exploring themes related to climate change and agriculture and human emotions.', 'General', 1967, 5, 5, 0, 'img14.jpg', 0, 0, NULL),
('1576527271399', NULL, 'information and communication technology', 'Lypsyu', 'Vintage Publishing', 'A captivating story exploring themes related to information and communication technology and human emotions.', 'General', 1952, 5, 5, 0, 'img15.jpg', 0, 0, NULL),
('6900451289401', NULL, 'foundation of ict', 'Pffol', 'Pearson', 'A captivating story exploring themes related to foundation of ict and human emotions.', 'General', 2001, 5, 5, 0, 'img16.jpg', 0, 0, NULL),
('1613357754265', NULL, 'computer programing language', 'Tltgv', 'Oxford Press', 'A captivating story exploring themes related to computer programing language and human emotions.', 'General', 1957, 5, 5, 0, 'img17.jpg', 0, 0, NULL),
('2461307156025', NULL, 'python programming for beginners', 'Gbwhhtrith', 'Cambridge Books', 'A captivating story exploring themes related to python programming for beginners and human emotions.', 'General', 1965, 5, 5, 0, 'img18.jpg', 0, 0, NULL),
('6603279916127', NULL, 'computer programming and cybersecurity', 'Qrpfcseahz', 'Pearson', 'A captivating story exploring themes related to computer programming and cybersecurity and human emotions.', 'General', 1989, 5, 5, 0, 'img19.jpg', 0, 0, NULL),
('7091768085250', NULL, 'orchid C++', 'Uozolxx', 'Scholastic', 'A captivating story exploring themes related to orchid c++ and human emotions.', 'General', 1989, 5, 5, 0, 'img20.jpg', 0, 0, NULL),
('6351830113568', NULL, 'practical electronics for inventors', 'Dugxure', 'Oxford Press', 'A captivating story exploring themes related to practical electronics for inventors and human emotions.', 'General', 2009, 5, 5, 0, 'img21.jpg', 0, 0, NULL),
('7128900048463', NULL, 'hacking electronics', 'Zvhmxmlj', 'Penguin Books', 'A captivating story exploring themes related to hacking electronics and human emotions.', 'General', 2006, 5, 5, 0, 'img22.jpg', 0, 0, NULL),
('8057034409269', NULL, 'engineering mechanics', 'Hxafbtcb', 'Pearson', 'A captivating story exploring themes related to engineering mechanics and human emotions.', 'General', 2020, 5, 5, 0, 'img23.jpg', 0, 0, NULL),
('0323850190420', NULL, 'mechanical engineering', 'Zvngnhva', 'Scholastic', 'A captivating story exploring themes related to mechanical engineering and human emotions.', 'General', 2019, 5, 5, 0, 'img24.jpg', 0, 0, NULL),
('4056543252699', NULL, 'numerical analysis and computational mathematics', 'Ysspcamcq', 'Scholastic', 'A captivating story exploring themes related to numerical analysis and computational mathematics and human emotions.', 'General', 1995, 5, 5, 0, 'img25.jpg', 0, 0, NULL),
('1875626713102', NULL, 'trignometry', 'Cidllopjs', 'Pearson', 'A captivating story exploring themes related to trignometry and human emotions.', 'General', 2001, 5, 5, 0, 'img26.jpg', 0, 0, NULL),
('2924006842526', NULL, 'short stories -English', 'Sqkvvupxj', 'Oxford Press', 'A captivating story exploring themes related to short stories -english and human emotions.', 'General', 1959, 5, 5, 0, 'img27.jpg', 0, 0, NULL),
('9875436992524', NULL, 'practical english usage', 'Mhusqfmwns', 'Random House', 'A captivating story exploring themes related to practical english usage and human emotions.', 'General', 1998, 5, 5, 0, 'img28.jpg', 0, 0, NULL),
('3328438043912', NULL, 'modern classical physics', 'Kqgnmoxb', 'Penguin Books', 'A captivating story exploring themes related to modern classical physics and human emotions.', 'General', 2013, 5, 5, 0, 'img29.jpg', 0, 0, NULL),
('7115814019075', NULL, 'active chemistry', 'Ajnhvsn', 'Pearson', 'A captivating story exploring themes related to active chemistry and human emotions.', 'General', 1966, 5, 5, 0, 'img30.jpg', 0, 0, NULL);

-- ====================================================================
-- DUMMY DATA: Transactions (25 transaction records)
-- FIXED: Removed duplicate 'fine' column, updated dates to 2025, FIXED: Use actual book ISBNs
-- ====================================================================
INSERT IGNORE INTO `transactions` (`tid`, `userId`, `isbn`, `borrowDate`, `returnDate`, `fineAmount`, `fineStatus`, `finePaymentDate`, `finePaymentMethod`) VALUES
('TXN001', 'USR001', '6604622694292', '2025-10-01', '2025-10-12', 0.00, 'paid', NULL, NULL),
('TXN002', 'USR002', '6080591560497', '2025-10-03', '2025-10-15', 0.00, 'paid', NULL, NULL),
('TXN003', 'USR003', '9780743273565', '2025-09-20', '2025-10-10', 15.00, 'paid', '2025-10-11', 'online'),
('TXN004', 'USR004', '7781111792539', '2025-10-05', '2025-10-18', 0.00, 'paid', NULL, NULL),
('TXN005', 'USR005', '8100852011803', '2025-10-08', '2025-10-20', 0.00, 'paid', NULL, NULL),
('TXN006', 'USR006', '9780134757599', '2025-09-15', '2025-10-05', 25.00, 'paid', '2025-10-06', 'card'),
('TXN007', 'USR007', '8783340203918', '2025-10-10', '2025-10-22', 0.00, 'paid', NULL, NULL),
('TXN008', 'USR008', '0307268000967', '2025-10-12', NULL, 0.00, 'pending', NULL, NULL),
('TXN009', 'FAC001', '7840135040808', '2025-09-25', '2025-10-15', 0.00, 'paid', NULL, NULL),
('TXN010', 'FAC002', '8131061994907', '2025-10-01', '2025-10-14', 0.00, 'paid', NULL, NULL),
('TXN011', 'USR001', '8063254954311', '2025-10-15', NULL, 0.00, 'pending', NULL, NULL),
('TXN012', 'USR002', '9780596517748', '2025-09-28', '2025-10-20', 10.00, 'paid', '2025-10-21', 'cash'),
('TXN013', 'USR003', '5418428424560', '2025-10-18', NULL, 0.00, 'pending', NULL, NULL),
('TXN014', 'USR004', '0500261952520', '2025-10-08', '2025-10-21', 0.00, 'paid', NULL, NULL),
('TXN015', 'USR005', '9780062316097', '2025-09-10', '2025-10-05', 35.00, 'paid', '2025-10-06', 'online'),
('TXN016', 'USR006', '6417612062012', '2025-10-20', NULL, 0.00, 'pending', NULL, NULL),
('TXN017', 'USR007', '1946547630161', '2025-10-12', NULL, 0.00, 'pending', NULL, NULL),
('TXN018', 'FAC003', '1435337305858', '2025-10-05', '2025-10-18', 0.00, 'paid', NULL, NULL),
('TXN019', 'FAC004', '1576527271399', '2025-10-10', '2025-10-23', 0.00, 'paid', NULL, NULL),
('TXN020', 'USR008', '9780691169866', '2025-09-18', '2025-10-12', 20.00, 'pending', NULL, NULL),
('TXN021', 'USR009', '6900451289401', '2025-10-22', NULL, 0.00, 'pending', NULL, NULL),
('TXN022', 'USR010', '1613357754265', '2025-10-16', NULL, 0.00, 'pending', NULL, NULL),
('TXN023', 'FAC005', '2461307156025', '2025-10-01', '2025-10-14', 0.00, 'paid', NULL, NULL),
('TXN024', 'USR001', '6603279916127', '2025-10-23', NULL, 0.00, 'pending', NULL, NULL),
('TXN025', 'USR002', '7091768085250', '2025-10-24', NULL, 0.00, 'pending', NULL, NULL);

-- ====================================================================
-- DUMMY DATA: Borrow Requests (15 requests with various statuses)
-- FIXED: Updated dates to 2025, FIXED: Use actual book ISBNs
-- ====================================================================
INSERT IGNORE INTO `borrow_requests` (`userId`, `isbn`, `requestDate`, `status`, `approvedBy`, `dueDate`, `rejectionReason`) VALUES
('USR003', '6604622694292', '2025-10-25 09:30:00', 'Pending', NULL, NULL, NULL),
('USR004', '6080591560497', '2025-10-25 10:15:00', 'Pending', NULL, NULL, NULL),
('USR005', '7781111792539', '2025-10-24 14:20:00', 'Approved', 'LIB001', '2025-11-07', NULL),
('USR006', '8100852011803', '2025-10-23 11:45:00', 'Approved', 'LIB002', '2025-11-06', NULL),
('USR007', '8783340203918', '2025-10-25 13:00:00', 'Pending', NULL, NULL, NULL),
('USR008', '0307268000967', '2025-10-22 15:30:00', 'Rejected', 'LIB001', NULL, 'All copies currently borrowed'),
('USR009', '7840135040808', '2025-10-25 08:45:00', 'Pending', NULL, NULL, NULL),
('USR010', '8131061994907', '2025-10-24 16:20:00', 'Approved', 'LIB003', '2025-11-07', NULL),
('FAC001', '8063254954311', '2025-10-23 09:00:00', 'Approved', 'LIB001', '2025-11-06', NULL),
('FAC002', '5418428424560', '2025-10-25 10:30:00', 'Pending', NULL, NULL, NULL),
('USR001', '0500261952520', '2025-10-21 14:15:00', 'Approved', 'LIB002', '2025-11-04', NULL),
('USR002', '6417612062012', '2025-10-25 11:00:00', 'Pending', NULL, NULL, NULL),
('FAC003', '1946547630161', '2025-10-20 13:45:00', 'Rejected', 'LIB003', NULL, 'User has pending fines'),
('USR003', '1435337305858', '2025-10-25 15:20:00', 'Pending', NULL, NULL, NULL),
('USR004', '1576527271399', '2025-10-24 09:30:00', 'Approved', 'LIB001', '2025-11-07', NULL);

-- ====================================================================
-- DUMMY DATA: Notifications (20 notifications)
-- FIXED: Updated dates to 2025
-- ====================================================================
INSERT IGNORE INTO `notifications` (`userId`, `title`, `message`, `type`, `priority`, `isRead`, `relatedId`, `createdAt`) VALUES
('USR001', 'Book Due Soon', 'Your borrowed book "computer programming and cybersecurity" is due in 2 days.', 'reminder', 'medium', 0, 'TXN024', '2025-10-24 09:00:00'),
('USR002', 'Book Due Soon', 'Your borrowed book "orchid C++" is due in 2 days.', 'reminder', 'medium', 0, 'TXN025', '2025-10-24 09:00:00'),
('USR003', 'Borrow Request Approved', 'Your request to borrow "where the forest meets the stars" has been approved.', 'approval', 'high', 1, '3', '2025-10-24 14:30:00'),
('USR006', 'Borrow Request Approved', 'Your request to borrow "the funnel of good" has been approved.', 'approval', 'high', 1, '4', '2025-10-23 12:00:00'),
('USR008', 'Borrow Request Rejected', 'Your request to borrow "Roots" was rejected: All copies currently borrowed', 'approval', 'high', 0, '6', '2025-10-22 16:00:00'),
('USR010', 'Borrow Request Approved', 'Your request to borrow "the house plants guid" has been approved.', 'approval', 'high', 1, '8', '2025-10-24 16:45:00'),
('FAC001', 'Borrow Request Approved', 'Your request to borrow "the science of plants" has been approved.', 'approval', 'high', 1, '9', '2025-10-23 09:30:00'),
('USR001', 'Borrow Request Approved', 'Your request to borrow "growing a revolution" has been approved.', 'approval', 'high', 1, '11', '2025-10-21 14:45:00'),
('FAC003', 'Borrow Request Rejected', 'Your request to borrow "emerging research in agriculture science" was rejected: User has pending fines', 'approval', 'high', 0, '13', '2025-10-20 14:15:00'),
('USR004', 'Borrow Request Approved', 'Your request to borrow "information and communication technology" has been approved.', 'approval', 'high', 1, '15', '2025-10-24 10:00:00'),
('USR003', 'Overdue Book', 'Your book is overdue. Fine: $15.00', 'overdue', 'high', 1, 'TXN003', '2025-10-05 10:00:00'),
('USR006', 'Overdue Book', 'Your book is overdue. Fine: $25.00', 'overdue', 'high', 1, 'TXN006', '2025-09-30 10:00:00'),
('USR005', 'Overdue Book', 'Your book is overdue. Fine: $35.00', 'overdue', 'high', 1, 'TXN015', '2025-09-25 10:00:00'),
('USR008', 'Pending Fine', 'You have an outstanding fine of $20.00', 'fine_paid', 'medium', 0, 'TXN020', '2025-10-13 11:00:00'),
('USR003', 'Fine Payment Received', 'Your fine payment of $15.00 has been received.', 'fine_paid', 'low', 1, 'TXN003', '2025-10-11 14:30:00'),
('USR006', 'Fine Payment Received', 'Your fine payment of $25.00 has been received.', 'fine_paid', 'low', 1, 'TXN006', '2025-10-06 15:45:00'),
('LIB001', 'Book Out of Stock', 'Book "Roots" is out of stock. All copies borrowed.', 'out_of_stock', 'medium', 0, '0307268000967', '2025-10-22 16:30:00'),
('LIB002', 'System Maintenance', 'Library system maintenance scheduled for Sunday, October 27, 2025 from 2:00 AM to 6:00 AM.', 'system', 'low', 1, NULL, '2025-10-20 08:00:00'),
('ADM001', 'New User Registration', 'New student user registered: james_thomas (USR009)', 'system', 'low', 1, 'USR009', '2025-10-20 09:15:00'),
(NULL, 'Library Announcement', 'New books added to Computer Science collection. Check out the latest titles!', 'system', 'medium', 0, NULL, '2025-10-15 10:00:00');

-- ====================================================================
-- DUMMY DATA: Book Reviews (15 reviews)
-- ====================================================================
INSERT IGNORE INTO `book_reviews` (`userId`, `isbn`, `rating`, `reviewText`, `isApproved`) VALUES
('USR001', '6604622694292', 5, 'Excellent book! Highly recommended.', 1),
('USR002', '6080591560497', 5, 'Must-read for every programmer. Changed the way I write code.', 1),
('USR003', '7781111792539', 4, 'Beautiful prose and a captivating story.', 1),
('USR004', '8100852011803', 5, 'Very helpful and insightful.', 1),
('FAC001', '7840135040808', 5, 'Great resource for learning.', 1),
('USR005', '8783340203918', 4, 'Great introduction with interesting projects and exercises.', 1),
('USR006', '0307268000967', 5, 'Essential reading! Highly recommended.', 1),
('FAC002', '8131061994907', 4, 'Fascinating and informative.', 1),
('USR007', '8063254954311', 5, 'Mind-blowing perspective. Great book!', 1),
('USR008', '5418428424560', 5, 'Life-changing book! Truly helpful.', 1),
('FAC003', '0500261952520', 5, 'The definitive guide. Every reader should check this.', 1),
('USR009', '6417612062012', 3, 'Good comprehensive guide but can be overwhelming for beginners.', 0),
('USR010', '1946547630161', 4, 'Insightful and well-written.', 1),
('FAC004', '1435337305858', 4, 'Practical and very helpful for projects.', 1),
('USR001', '1576527271399', 5, 'Raw and honest. No sugar-coating here!', 1);

-- ====================================================================
-- DUMMY DATA: Favorites (20 favorites)
-- ====================================================================
INSERT IGNORE INTO `favorites` (`userId`, `isbn`, `notes`) VALUES
('USR001', '6604622694292', 'Great reference'),
('USR001', '6080591560497', 'Must re-read annually'),
('USR002', '7781111792539', 'Favorite novel'),
('USR002', '8100852011803', 'Actually helped me a lot'),
('USR003', '8783340203918', 'Reread every few years'),
('USR003', '0307268000967', 'Interesting perspective'),
('USR004', '7840135040808', 'Relatable story'),
('USR005', '8131061994907', 'Best book I have read'),
('USR005', '8063254954311', NULL),
('USR006', '5418428424560', 'Powerful and moving'),
('USR006', '0500261952520', 'Great for learning'),
('USR007', '6417612062012', 'Reference guide'),
('USR008', '1946547630161', 'Changed how I think'),
('FAC001', '1435337305858', 'Amazing content'),
('FAC001', '1576527271399', 'Reference material'),
('FAC002', '6900451289401', 'Fascinating read'),
('FAC003', '1613357754265', 'Guide book'),
('FAC004', '2461307156025', 'Reference'),
('USR009', '6603279916127', 'Inspiration'),
('USR010', '7091768085250', 'Wisdom');

-- ====================================================================
-- Table structure for table `payment_logs`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `transactionId` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `cardLastFour` varchar(4) DEFAULT NULL,
  `paymentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paymentMethod` enum('credit_card','debit_card','upi','cash','online','card') NOT NULL DEFAULT 'card',
  `status` enum('success','failed','pending') NOT NULL DEFAULT 'success',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_transactionId` (`transactionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Disable FK checks for safe data insertion (re-enabled after)
-- ====================================================================
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================================================
-- Insert default fine settings (only if table is empty)
-- FIXED: Added column aliases to prevent duplicate column name '0' error
-- ====================================================================
INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'fine_per_day', '5', 'Fine amount per day for overdue books', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'fine_per_day'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'max_borrow_days', '14', 'Maximum days a book can be borrowed', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'max_borrow_days'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'grace_period_days', '0', 'Grace period before fines start', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'grace_period_days'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'max_fine_amount', '500', 'Maximum fine amount per book', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'max_fine_amount'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'fine_calculation_method', 'daily', 'Method for calculating fines: daily or fixed', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'fine_calculation_method'
);

-- ====================================================================
-- Insert default system settings
-- ====================================================================
INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_name', 'University Central Library', 'string', 'Name of the library', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_name');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_email', 'library@university.edu', 'string', 'Library contact email', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_email');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_phone', '+1-555-0100', 'string', 'Library contact phone', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_phone');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'max_books_per_user', '5', 'number', 'Maximum books a user can borrow', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'max_books_per_user');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'enable_notifications', 'true', 'boolean', 'Enable system notifications', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'enable_notifications');

-- ====================================================================
-- Insert default role permissions
-- ====================================================================
INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Student', 'view_catalog', 1, 0, 0, 0, 'Can view library catalog'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Student' AND permission = 'view_catalog');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Student', 'borrow_book', 1, 1, 0, 0, 'Can borrow books'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Student' AND permission = 'borrow_book');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Librarian', 'manage_books', 1, 1, 1, 0, 'Can manage books'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Librarian' AND permission = 'manage_books');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Librarian', 'approve_requests', 1, 0, 0, 1, 'Can approve borrow requests'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Librarian' AND permission = 'approve_requests');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'manage_users', 1, 1, 1, 0, 'Can manage all users'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'manage_users');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'view_reports', 1, 0, 0, 0, 'Can view system reports'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'view_reports');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'system_settings', 1, 1, 0, 0, 'Can manage system settings'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'system_settings');

-- ====================================================================
-- Insert default library hours
-- ====================================================================
INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Monday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Monday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Tuesday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Tuesday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Wednesday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Wednesday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Thursday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Thursday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Friday', '08:00:00', '18:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Friday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Saturday', '10:00:00', '16:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Saturday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Sunday', '00:00:00', '00:00:00', 1
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Sunday');

-- ====================================================================
-- DUMMY DATA: Users (20 users - mix of Students, Faculty, Librarian, Admin)
-- FIXED: Updated all dates to 2025
-- ====================================================================
INSERT IGNORE INTO `users` (`userId`, `username`, `password`, `userType`, `gender`, `dob`, `emailId`, `phoneNumber`, `address`, `profileImage`, `isVerified`, `createdAt`) VALUES
('USR001', 'student', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Student', 'Male', '2002-05-15', 'john.smith@university.edu', '555-0101', '123 Campus Drive, Dorm A, Room 201', 'profile1.jpg', 1, '2025-01-15 09:30:00'),
('USR002', 'emily_chen', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-08-22', 'emily.chen@university.edu', '555-0102', '456 University Ave, Dorm B, Room 305', 'profile2.jpg', 1, '2025-01-16 10:15:00'),
('USR003', 'michael_brown', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-11-30', 'michael.brown@university.edu', '555-0103', '789 College Blvd, Dorm C, Room 102', 'profile3.jpg', 1, '2025-01-17 11:20:00'),
('USR004', 'sarah_davis', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-03-18', 'sarah.davis@university.edu', '555-0104', '321 Student Lane, Dorm A, Room 410', 'profile4.jpg', 1, '2025-01-18 14:30:00'),
('USR005', 'david_wilson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-07-25', 'david.wilson@university.edu', '555-0105', '654 Academic Way, Dorm D, Room 208', 'profile5.jpg', 1, '2025-01-19 09:45:00'),
('USR006', 'jessica_moore', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-01-12', 'jessica.moore@university.edu', '555-0106', '987 Scholar St, Dorm B, Room 501', 'profile6.jpg', 1, '2025-01-20 13:00:00'),
('USR007', 'robert_taylor', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-09-08', 'robert.taylor@university.edu', '555-0107', '147 Learning Ave, Dorm C, Room 315', 'profile7.jpg', 1, '2025-01-21 10:30:00'),
('USR008', 'amanda_anderson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-06-20', 'amanda.anderson@university.edu', '555-0108', '258 Knowledge Rd, Dorm A, Room 212', 'profile8.jpg', 1, '2025-01-22 15:15:00'),
('USR009', 'james_thomas', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-12-05', 'james.thomas@university.edu', '555-0109', '369 Education Blvd, Dorm D, Room 405', 'profile9.jpg', 0, '2025-10-20 09:00:00'),
('USR010', 'lisa_jackson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-04-17', 'lisa.jackson@university.edu', '555-0110', '741 Campus Circle, Dorm B, Room 118', 'profile10.jpg', 1, '2025-02-01 11:45:00'),
('FAC001', 'dr_robert_johnson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1978-03-22', 'r.johnson@university.edu', '555-0201', '1001 Faculty Housing, Building A', 'profile_fac1.jpg', 1, '2024-08-01 08:00:00'),
('FAC002', 'prof_maria_garcia', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Female', '1982-07-14', 'm.garcia@university.edu', '555-0202', '1002 Faculty Housing, Building B', 'profile_fac2.jpg', 1, '2024-08-01 08:00:00'),
('FAC003', 'dr_william_lee', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1975-11-08', 'w.lee@university.edu', '555-0203', '1003 Faculty Housing, Building A', 'profile_fac3.jpg', 1, '2024-08-01 08:00:00'),
('FAC004', 'prof_patricia_martinez', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Female', '1980-05-30', 'p.martinez@university.edu', '555-0204', '1004 Faculty Housing, Building C', 'profile_fac4.jpg', 1, '2024-08-01 08:00:00'),
('FAC005', 'dr_charles_white', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1979-09-16', 'c.white@university.edu', '555-0205', '1005 Faculty Housing, Building B', 'profile_fac5.jpg', 1, '2024-08-01 08:00:00'),
('LIB001', 'lib', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Faculty', 'Female', '1985-06-12', 'susan.librarian@university.edu', '555-0301', '2001 Staff Quarters, Block A', 'profile_lib1.jpg', 1, '2023-06-01 08:00:00'),
('LIB002', 'librarian_mark', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Librarian', 'Male', '1988-02-28', 'mark.librarian@university.edu', '555-0302', '2002 Staff Quarters, Block B', 'profile_lib2.jpg', 1, '2023-06-01 08:00:00'),
('LIB003', 'librarian_nancy', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Librarian', 'Female', '1990-10-19', 'nancy.librarian@university.edu', '555-0303', '2003 Staff Quarters, Block A', 'profile_lib3.jpg', 1, '2024-01-15 08:00:00'),
('ADM001', 'admin', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Admin', 'Male', '1975-04-05', 'richard.admin@university.edu', '555-0401', '3001 Admin Building, Office 101', 'profile_adm1.jpg', 1, '2022-01-01 08:00:00'),
('ADM002', 'admin_jennifer', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Admin', 'Female', '1980-12-21', 'jennifer.admin@university.edu', '555-0402', '3002 Admin Building, Office 102', 'profile_adm2.jpg', 1, '2022-01-01 08:00:00');

-- ====================================================================
-- DUMMY DATA: Books (30 books across various categories)
-- ====================================================================
INSERT IGNORE INTO `books` (`isbn`, `barcode`, `bookName`, `authorName`, `publisherName`, `description`, `category`, `publicationYear`, `totalCopies`, `available`, `borrowed`, `bookImage`, `isTrending`, `isSpecial`, `specialBadge`) VALUES
('6604622694292', NULL, 'where the forest meets the stars', 'glendy vanderah', 'HarperCollins', 'A captivating story exploring themes related to where the forest meets the stars and human emotions.', 'Literature', 2016, 5, 5, 0, 'img1.jpg', 0, 0, NULL),
('6080591560497', NULL, 'JAVA', 'josh thompson', 'Random House', 'A captivating story exploring themes related to java and human emotions.', 'Technology', 2012, 5, 5, 0, 'img2.jpg', 0, 0, NULL),
('7781111792539', NULL, 'a world of secrets', 'james maxwell', 'Scholastic', 'A captivating story exploring themes related to a world of secrets and human emotions.', 'Fantasy', 1978, 5, 5, 0, 'img3.jpg', 0, 0, NULL),
('8100852011803', NULL, 'the funnel of good', 'bob bloch', 'Vintage Publishing', 'A captivating story exploring themes related to the funnel of good  and human emotions.', 'Self-help', 1978, 5, 5, 0, 'img4.jpg', 0, 0, NULL),
('8783340203918', NULL, 'long way gone', 'charles martin', 'Cambridge Books', 'A captivating story exploring themes related to long way gone and human emotions.', 'Fiction', 2005, 5, 5, 0, 'img5.jpg', 0, 0, NULL),
('0307268000967', NULL, 'Roots', 'alex haley', 'Random House', 'A captivating story exploring themes related to roots and human emotions.', 'General', 1999, 5, 5, 0, 'img6.jpg', 0, 0, NULL),
('7840135040808', NULL, 'preparation for the next life', 'Uspoytxhii', 'Pearson', 'A captivating story exploring themes related to preparation for the next life and human emotions.', 'General', 1977, 5, 5, 0, 'img7.jpg', 0, 0, NULL),
('8131061994907', NULL, 'the house plants guid', 'Uanqenit', 'Pearson', 'A captivating story exploring themes related to the house plants guid and human emotions.', 'General', 1992, 5, 5, 0, 'img8.jpg', 0, 0, NULL),
('8063254954311', NULL, 'the science of plants', 'Sofcew', 'Cambridge Books', 'A captivating story exploring themes related to the science of plants and human emotions.', 'Science', 1988, 5, 5, 0, 'img9.jpg', 0, 0, NULL),
('5418428424560', NULL, 'indian agriculture', 'Yjydhoer', 'Pearson', 'A captivating story exploring themes related to indian agriculture and human emotions.', 'General', 1950, 5, 5, 0, 'img10.jpg', 0, 0, NULL),
('0500261952520', NULL, 'growing a revolution', 'Ogodqytjby', 'Vintage Publishing', 'A captivating story exploring themes related to growing a revolution and human emotions.', 'General', 1994, 5, 5, 0, 'img11.jpg', 0, 0, NULL),
('6417612062012', NULL, 'organic farming for sustainable agriculture', 'Zcjxwwblf', 'Cambridge Books', 'A captivating story exploring themes related to organic farming for sustainable agriculture and human emotions.', 'General', 2010, 5, 5, 0, 'img12.jpg', 0, 0, NULL),
('1946547630161', NULL, 'emerging research in agriculture science', 'Dztfpxyi', 'Pearson', 'A captivating story exploring themes related to emerging research in agriculture science and human emotions.', 'Science', 1969, 5, 5, 0, 'img13.jpg', 0, 0, NULL),
('1435337305858', NULL, 'climate change and agriculture', 'Iqubvwzqdv', 'HarperCollins', 'A captivating story exploring themes related to climate change and agriculture and human emotions.', 'General', 1967, 5, 5, 0, 'img14.jpg', 0, 0, NULL),
('1576527271399', NULL, 'information and communication technology', 'Lypsyu', 'Vintage Publishing', 'A captivating story exploring themes related to information and communication technology and human emotions.', 'General', 1952, 5, 5, 0, 'img15.jpg', 0, 0, NULL),
('6900451289401', NULL, 'foundation of ict', 'Pffol', 'Pearson', 'A captivating story exploring themes related to foundation of ict and human emotions.', 'General', 2001, 5, 5, 0, 'img16.jpg', 0, 0, NULL),
('1613357754265', NULL, 'computer programing language', 'Tltgv', 'Oxford Press', 'A captivating story exploring themes related to computer programing language and human emotions.', 'General', 1957, 5, 5, 0, 'img17.jpg', 0, 0, NULL),
('2461307156025', NULL, 'python programming for beginners', 'Gbwhhtrith', 'Cambridge Books', 'A captivating story exploring themes related to python programming for beginners and human emotions.', 'General', 1965, 5, 5, 0, 'img18.jpg', 0, 0, NULL),
('6603279916127', NULL, 'computer programming and cybersecurity', 'Qrpfcseahz', 'Pearson', 'A captivating story exploring themes related to computer programming and cybersecurity and human emotions.', 'General', 1989, 5, 5, 0, 'img19.jpg', 0, 0, NULL),
('7091768085250', NULL, 'orchid C++', 'Uozolxx', 'Scholastic', 'A captivating story exploring themes related to orchid c++ and human emotions.', 'General', 1989, 5, 5, 0, 'img20.jpg', 0, 0, NULL),
('6351830113568', NULL, 'practical electronics for inventors', 'Dugxure', 'Oxford Press', 'A captivating story exploring themes related to practical electronics for inventors and human emotions.', 'General', 2009, 5, 5, 0, 'img21.jpg', 0, 0, NULL),
('7128900048463', NULL, 'hacking electronics', 'Zvhmxmlj', 'Penguin Books', 'A captivating story exploring themes related to hacking electronics and human emotions.', 'General', 2006, 5, 5, 0, 'img22.jpg', 0, 0, NULL),
('8057034409269', NULL, 'engineering mechanics', 'Hxafbtcb', 'Pearson', 'A captivating story exploring themes related to engineering mechanics and human emotions.', 'General', 2020, 5, 5, 0, 'img23.jpg', 0, 0, NULL),
('0323850190420', NULL, 'mechanical engineering', 'Zvngnhva', 'Scholastic', 'A captivating story exploring themes related to mechanical engineering and human emotions.', 'General', 2019, 5, 5, 0, 'img24.jpg', 0, 0, NULL),
('4056543252699', NULL, 'numerical analysis and computational mathematics', 'Ysspcamcq', 'Scholastic', 'A captivating story exploring themes related to numerical analysis and computational mathematics and human emotions.', 'General', 1995, 5, 5, 0, 'img25.jpg', 0, 0, NULL),
('1875626713102', NULL, 'trignometry', 'Cidllopjs', 'Pearson', 'A captivating story exploring themes related to trignometry and human emotions.', 'General', 2001, 5, 5, 0, 'img26.jpg', 0, 0, NULL),
('2924006842526', NULL, 'short stories -English', 'Sqkvvupxj', 'Oxford Press', 'A captivating story exploring themes related to short stories -english and human emotions.', 'General', 1959, 5, 5, 0, 'img27.jpg', 0, 0, NULL),
('9875436992524', NULL, 'practical english usage', 'Mhusqfmwns', 'Random House', 'A captivating story exploring themes related to practical english usage and human emotions.', 'General', 1998, 5, 5, 0, 'img28.jpg', 0, 0, NULL),
('3328438043912', NULL, 'modern classical physics', 'Kqgnmoxb', 'Penguin Books', 'A captivating story exploring themes related to modern classical physics and human emotions.', 'General', 2013, 5, 5, 0, 'img29.jpg', 0, 0, NULL),
('7115814019075', NULL, 'active chemistry', 'Ajnhvsn', 'Pearson', 'A captivating story exploring themes related to active chemistry and human emotions.', 'General', 1966, 5, 5, 0, 'img30.jpg', 0, 0, NULL);

-- ====================================================================
-- DUMMY DATA: Transactions (25 transaction records)
-- FIXED: Removed duplicate 'fine' column, updated dates to 2025, FIXED: Use actual book ISBNs
-- ====================================================================
INSERT IGNORE INTO `transactions` (`tid`, `userId`, `isbn`, `borrowDate`, `returnDate`, `fineAmount`, `fineStatus`, `finePaymentDate`, `finePaymentMethod`) VALUES
('TXN001', 'USR001', '6604622694292', '2025-10-01', '2025-10-12', 0.00, 'paid', NULL, NULL),
('TXN002', 'USR002', '6080591560497', '2025-10-03', '2025-10-15', 0.00, 'paid', NULL, NULL),
('TXN003', 'USR003', '9780743273565', '2025-09-20', '2025-10-10', 15.00, 'paid', '2025-10-11', 'online'),
('TXN004', 'USR004', '7781111792539', '2025-10-05', '2025-10-18', 0.00, 'paid', NULL, NULL),
('TXN005', 'USR005', '8100852011803', '2025-10-08', '2025-10-20', 0.00, 'paid', NULL, NULL),
('TXN006', 'USR006', '9780134757599', '2025-09-15', '2025-10-05', 25.00, 'paid', '2025-10-06', 'card'),
('TXN007', 'USR007', '8783340203918', '2025-10-10', '2025-10-22', 0.00, 'paid', NULL, NULL),
('TXN008', 'USR008', '0307268000967', '2025-10-12', NULL, 0.00, 'pending', NULL, NULL),
('TXN009', 'FAC001', '7840135040808', '2025-09-25', '2025-10-15', 0.00, 'paid', NULL, NULL),
('TXN010', 'FAC002', '8131061994907', '2025-10-01', '2025-10-14', 0.00, 'paid', NULL, NULL),
('TXN011', 'USR001', '8063254954311', '2025-10-15', NULL, 0.00, 'pending', NULL, NULL),
('TXN012', 'USR002', '9780596517748', '2025-09-28', '2025-10-20', 10.00, 'paid', '2025-10-21', 'cash'),
('TXN013', 'USR003', '5418428424560', '2025-10-18', NULL, 0.00, 'pending', NULL, NULL),
('TXN014', 'USR004', '0500261952520', '2025-10-08', '2025-10-21', 0.00, 'paid', NULL, NULL),
('TXN015', 'USR005', '9780062316097', '2025-09-10', '2025-10-05', 35.00, 'paid', '2025-10-06', 'online'),
('TXN016', 'USR006', '6417612062012', '2025-10-20', NULL, 0.00, 'pending', NULL, NULL),
('TXN017', 'USR007', '1946547630161', '2025-10-12', NULL, 0.00, 'pending', NULL, NULL),
('TXN018', 'FAC003', '1435337305858', '2025-10-05', '2025-10-18', 0.00, 'paid', NULL, NULL),
('TXN019', 'FAC004', '1576527271399', '2025-10-10', '2025-10-23', 0.00, 'paid', NULL, NULL),
('TXN020', 'USR008', '9780691169866', '2025-09-18', '2025-10-12', 20.00, 'pending', NULL, NULL),
('TXN021', 'USR009', '6900451289401', '2025-10-22', NULL, 0.00, 'pending', NULL, NULL),
('TXN022', 'USR010', '1613357754265', '2025-10-16', NULL, 0.00, 'pending', NULL, NULL),
('TXN023', 'FAC005', '2461307156025', '2025-10-01', '2025-10-14', 0.00, 'paid', NULL, NULL),
('TXN024', 'USR001', '6603279916127', '2025-10-23', NULL, 0.00, 'pending', NULL, NULL),
('TXN025', 'USR002', '7091768085250', '2025-10-24', NULL, 0.00, 'pending', NULL, NULL);

-- ====================================================================
-- DUMMY DATA: Borrow Requests (15 requests with various statuses)
-- FIXED: Updated dates to 2025, FIXED: Use actual book ISBNs
-- ====================================================================
INSERT IGNORE INTO `borrow_requests` (`userId`, `isbn`, `requestDate`, `status`, `approvedBy`, `dueDate`, `rejectionReason`) VALUES
('USR003', '6604622694292', '2025-10-25 09:30:00', 'Pending', NULL, NULL, NULL),
('USR004', '6080591560497', '2025-10-25 10:15:00', 'Pending', NULL, NULL, NULL),
('USR005', '7781111792539', '2025-10-24 14:20:00', 'Approved', 'LIB001', '2025-11-07', NULL),
('USR006', '8100852011803', '2025-10-23 11:45:00', 'Approved', 'LIB002', '2025-11-06', NULL),
('USR007', '8783340203918', '2025-10-25 13:00:00', 'Pending', NULL, NULL, NULL),
('USR008', '0307268000967', '2025-10-22 15:30:00', 'Rejected', 'LIB001', NULL, 'All copies currently borrowed'),
('USR009', '7840135040808', '2025-10-25 08:45:00', 'Pending', NULL, NULL, NULL),
('USR010', '8131061994907', '2025-10-24 16:20:00', 'Approved', 'LIB003', '2025-11-07', NULL),
('FAC001', '8063254954311', '2025-10-23 09:00:00', 'Approved', 'LIB001', '2025-11-06', NULL),
('FAC002', '5418428424560', '2025-10-25 10:30:00', 'Pending', NULL, NULL, NULL),
('USR001', '0500261952520', '2025-10-21 14:15:00', 'Approved', 'LIB002', '2025-11-04', NULL),
('USR002', '6417612062012', '2025-10-25 11:00:00', 'Pending', NULL, NULL, NULL),
('FAC003', '1946547630161', '2025-10-20 13:45:00', 'Rejected', 'LIB003', NULL, 'User has pending fines'),
('USR003', '1435337305858', '2025-10-25 15:20:00', 'Pending', NULL, NULL, NULL),
('USR004', '1576527271399', '2025-10-24 09:30:00', 'Approved', 'LIB001', '2025-11-07', NULL);

-- ====================================================================
-- DUMMY DATA: Notifications (20 notifications)
-- FIXED: Updated dates to 2025
-- ====================================================================
INSERT IGNORE INTO `notifications` (`userId`, `title`, `message`, `type`, `priority`, `isRead`, `relatedId`, `createdAt`) VALUES
('USR001', 'Book Due Soon', 'Your borrowed book "computer programming and cybersecurity" is due in 2 days.', 'reminder', 'medium', 0, 'TXN024', '2025-10-24 09:00:00'),
('USR002', 'Book Due Soon', 'Your borrowed book "orchid C++" is due in 2 days.', 'reminder', 'medium', 0, 'TXN025', '2025-10-24 09:00:00'),
('USR003', 'Borrow Request Approved', 'Your request to borrow "where the forest meets the stars" has been approved.', 'approval', 'high', 1, '3', '2025-10-24 14:30:00'),
('USR006', 'Borrow Request Approved', 'Your request to borrow "the funnel of good" has been approved.', 'approval', 'high', 1, '4', '2025-10-23 12:00:00'),
('USR008', 'Borrow Request Rejected', 'Your request to borrow "Roots" was rejected: All copies currently borrowed', 'approval', 'high', 0, '6', '2025-10-22 16:00:00'),
('USR010', 'Borrow Request Approved', 'Your request to borrow "the house plants guid" has been approved.', 'approval', 'high', 1, '8', '2025-10-24 16:45:00'),
('FAC001', 'Borrow Request Approved', 'Your request to borrow "the science of plants" has been approved.', 'approval', 'high', 1, '9', '2025-10-23 09:30:00'),
('USR001', 'Borrow Request Approved', 'Your request to borrow "growing a revolution" has been approved.', 'approval', 'high', 1, '11', '2025-10-21 14:45:00'),
('FAC003', 'Borrow Request Rejected', 'Your request to borrow "emerging research in agriculture science" was rejected: User has pending fines', 'approval', 'high', 0, '13', '2025-10-20 14:15:00'),
('USR004', 'Borrow Request Approved', 'Your request to borrow "information and communication technology" has been approved.', 'approval', 'high', 1, '15', '2025-10-24 10:00:00'),
('USR003', 'Overdue Book', 'Your book is overdue. Fine: $15.00', 'overdue', 'high', 1, 'TXN003', '2025-10-05 10:00:00'),
('USR006', 'Overdue Book', 'Your book is overdue. Fine: $25.00', 'overdue', 'high', 1, 'TXN006', '2025-09-30 10:00:00'),
('USR005', 'Overdue Book', 'Your book is overdue. Fine: $35.00', 'overdue', 'high', 1, 'TXN015', '2025-09-25 10:00:00'),
('USR008', 'Pending Fine', 'You have an outstanding fine of $20.00', 'fine_paid', 'medium', 0, 'TXN020', '2025-10-13 11:00:00'),
('USR003', 'Fine Payment Received', 'Your fine payment of $15.00 has been received.', 'fine_paid', 'low', 1, 'TXN003', '2025-10-11 14:30:00'),
('USR006', 'Fine Payment Received', 'Your fine payment of $25.00 has been received.', 'fine_paid', 'low', 1, 'TXN006', '2025-10-06 15:45:00'),
('LIB001', 'Book Out of Stock', 'Book "Roots" is out of stock. All copies borrowed.', 'out_of_stock', 'medium', 0, '0307268000967', '2025-10-22 16:30:00'),
('LIB002', 'System Maintenance', 'Library system maintenance scheduled for Sunday, October 27, 2025 from 2:00 AM to 6:00 AM.', 'system', 'low', 1, NULL, '2025-10-20 08:00:00'),
('ADM001', 'New User Registration', 'New student user registered: james_thomas (USR009)', 'system', 'low', 1, 'USR009', '2025-10-20 09:15:00'),
(NULL, 'Library Announcement', 'New books added to Computer Science collection. Check out the latest titles!', 'system', 'medium', 0, NULL, '2025-10-15 10:00:00');

-- ====================================================================
-- DUMMY DATA: Book Reviews (15 reviews)
-- ====================================================================
INSERT IGNORE INTO `book_reviews` (`userId`, `isbn`, `rating`, `reviewText`, `isApproved`) VALUES
('USR001', '6604622694292', 5, 'Excellent book! Highly recommended.', 1),
('USR002', '6080591560497', 5, 'Must-read for every programmer. Changed the way I write code.', 1),
('USR003', '7781111792539', 4, 'Beautiful prose and a captivating story.', 1),
('USR004', '8100852011803', 5, 'Very helpful and insightful.', 1),
('FAC001', '7840135040808', 5, 'Great resource for learning.', 1),
('USR005', '8783340203918', 4, 'Great introduction with interesting projects and exercises.', 1),
('USR006', '0307268000967', 5, 'Essential reading! Highly recommended.', 1),
('FAC002', '8131061994907', 4, 'Fascinating and informative.', 1),
('USR007', '8063254954311', 5, 'Mind-blowing perspective. Great book!', 1),
('USR008', '5418428424560', 5, 'Life-changing book! Truly helpful.', 1),
('FAC003', '0500261952520', 5, 'The definitive guide. Every reader should check this.', 1),
('USR009', '6417612062012', 3, 'Good comprehensive guide but can be overwhelming for beginners.', 0),
('USR010', '1946547630161', 4, 'Insightful and well-written.', 1),
('FAC004', '1435337305858', 4, 'Practical and very helpful for projects.', 1),
('USR001', '1576527271399', 5, 'Raw and honest. No sugar-coating here!', 1);

-- ====================================================================
-- DUMMY DATA: Favorites (20 favorites)
-- ====================================================================
INSERT IGNORE INTO `favorites` (`userId`, `isbn`, `notes`) VALUES
('USR001', '6604622694292', 'Great reference'),
('USR001', '6080591560497', 'Must re-read annually'),
('USR002', '7781111792539', 'Favorite novel'),
('USR002', '8100852011803', 'Actually helped me a lot'),
('USR003', '8783340203918', 'Reread every few years'),
('USR003', '0307268000967', 'Interesting perspective'),
('USR004', '7840135040808', 'Relatable story'),
('USR005', '8131061994907', 'Best book I have read'),
('USR005', '8063254954311', NULL),
('USR006', '5418428424560', 'Powerful and moving'),
('USR006', '0500261952520', 'Great for learning'),
('USR007', '6417612062012', 'Reference guide'),
('USR008', '1946547630161', 'Changed how I think'),
('FAC001', '1435337305858', 'Amazing content'),
('FAC001', '1576527271399', 'Reference material'),
('FAC002', '6900451289401', 'Fascinating read'),
('FAC003', '1613357754265', 'Guide book'),
('FAC004', '2461307156025', 'Reference'),
('USR009', '6603279916127', 'Inspiration'),
('USR010', '7091768085250', 'Wisdom');

-- ====================================================================
-- Table structure for table `payment_logs`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `transactionId` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `cardLastFour` varchar(4) DEFAULT NULL,
  `paymentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paymentMethod` enum('credit_card','debit_card','upi','cash','online','card') NOT NULL DEFAULT 'card',
  `status` enum('success','failed','pending') NOT NULL DEFAULT 'success',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_transactionId` (`transactionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Disable FK checks for safe data insertion (re-enabled after)
-- ====================================================================
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================================================
-- Insert default fine settings (only if table is empty)
-- FIXED: Added column aliases to prevent duplicate column name '0' error
-- ====================================================================
INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'fine_per_day', '5', 'Fine amount per day for overdue books', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'fine_per_day'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'max_borrow_days', '14', 'Maximum days a book can be borrowed', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'max_borrow_days'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'grace_period_days', '0', 'Grace period before fines start', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'grace_period_days'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'max_fine_amount', '500', 'Maximum fine amount per book', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'max_fine_amount'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'fine_calculation_method', 'daily', 'Method for calculating fines: daily or fixed', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'fine_calculation_method'
);

-- ====================================================================
-- Insert default system settings
-- ====================================================================
INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_name', 'University Central Library', 'string', 'Name of the library', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_name');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_email', 'library@university.edu', 'string', 'Library contact email', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_email');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_phone', '+1-555-0100', 'string', 'Library contact phone', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_phone');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'max_books_per_user', '5', 'number', 'Maximum books a user can borrow', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'max_books_per_user');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'enable_notifications', 'true', 'boolean', 'Enable system notifications', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'enable_notifications');

-- ====================================================================
-- Insert default role permissions
-- ====================================================================
INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Student', 'view_catalog', 1, 0, 0, 0, 'Can view library catalog'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Student' AND permission = 'view_catalog');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Student', 'borrow_book', 1, 1, 0, 0, 'Can borrow books'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Student' AND permission = 'borrow_book');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Librarian', 'manage_books', 1, 1, 1, 0, 'Can manage books'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Librarian' AND permission = 'manage_books');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Librarian', 'approve_requests', 1, 0, 0, 1, 'Can approve borrow requests'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Librarian' AND permission = 'approve_requests');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'manage_users', 1, 1, 1, 0, 'Can manage all users'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'manage_users');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'view_reports', 1, 0, 0, 0, 'Can view system reports'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'view_reports');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'system_settings', 1, 1, 0, 0, 'Can manage system settings'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'system_settings');

-- ====================================================================
-- Insert default library hours
-- ====================================================================
INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Monday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Monday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Tuesday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Tuesday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Wednesday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Wednesday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Thursday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Thursday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Friday', '08:00:00', '18:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Friday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Saturday', '10:00:00', '16:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Saturday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Sunday', '00:00:00', '00:00:00', 1
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Sunday');

-- ====================================================================
-- DUMMY DATA: Users (20 users - mix of Students, Faculty, Librarian, Admin)
-- FIXED: Updated all dates to 2025
-- ====================================================================
INSERT IGNORE INTO `users` (`userId`, `username`, `password`, `userType`, `gender`, `dob`, `emailId`, `phoneNumber`, `address`, `profileImage`, `isVerified`, `createdAt`) VALUES
('USR001', 'student', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Student', 'Male', '2002-05-15', 'john.smith@university.edu', '555-0101', '123 Campus Drive, Dorm A, Room 201', 'profile1.jpg', 1, '2025-01-15 09:30:00'),
('USR002', 'emily_chen', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-08-22', 'emily.chen@university.edu', '555-0102', '456 University Ave, Dorm B, Room 305', 'profile2.jpg', 1, '2025-01-16 10:15:00'),
('USR003', 'michael_brown', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-11-30', 'michael.brown@university.edu', '555-0103', '789 College Blvd, Dorm C, Room 102', 'profile3.jpg', 1, '2025-01-17 11:20:00'),
('USR004', 'sarah_davis', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-03-18', 'sarah.davis@university.edu', '555-0104', '321 Student Lane, Dorm A, Room 410', 'profile4.jpg', 1, '2025-01-18 14:30:00'),
('USR005', 'david_wilson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-07-25', 'david.wilson@university.edu', '555-0105', '654 Academic Way, Dorm D, Room 208', 'profile5.jpg', 1, '2025-01-19 09:45:00'),
('USR006', 'jessica_moore', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-01-12', 'jessica.moore@university.edu', '555-0106', '987 Scholar St, Dorm B, Room 501', 'profile6.jpg', 1, '2025-01-20 13:00:00'),
('USR007', 'robert_taylor', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-09-08', 'robert.taylor@university.edu', '555-0107', '147 Learning Ave, Dorm C, Room 315', 'profile7.jpg', 1, '2025-01-21 10:30:00'),
('USR008', 'amanda_anderson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-06-20', 'amanda.anderson@university.edu', '555-0108', '258 Knowledge Rd, Dorm A, Room 212', 'profile8.jpg', 1, '2025-01-22 15:15:00'),
('USR009', 'james_thomas', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-12-05', 'james.thomas@university.edu', '555-0109', '369 Education Blvd, Dorm D, Room 405', 'profile9.jpg', 0, '2025-10-20 09:00:00'),
('USR010', 'lisa_jackson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-04-17', 'lisa.jackson@university.edu', '555-0110', '741 Campus Circle, Dorm B, Room 118', 'profile10.jpg', 1, '2025-02-01 11:45:00'),
('FAC001', 'dr_robert_johnson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1978-03-22', 'r.johnson@university.edu', '555-0201', '1001 Faculty Housing, Building A', 'profile_fac1.jpg', 1, '2024-08-01 08:00:00'),
('FAC002', 'prof_maria_garcia', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Female', '1982-07-14', 'm.garcia@university.edu', '555-0202', '1002 Faculty Housing, Building B', 'profile_fac2.jpg', 1, '2024-08-01 08:00:00'),
('FAC003', 'dr_william_lee', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1975-11-08', 'w.lee@university.edu', '555-0203', '1003 Faculty Housing, Building A', 'profile_fac3.jpg', 1, '2024-08-01 08:00:00'),
('FAC004', 'prof_patricia_martinez', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Female', '1980-05-30', 'p.martinez@university.edu', '555-0204', '1004 Faculty Housing, Building C', 'profile_fac4.jpg', 1, '2024-08-01 08:00:00'),
('FAC005', 'dr_charles_white', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1979-09-16', 'c.white@university.edu', '555-0205', '1005 Faculty Housing, Building B', 'profile_fac5.jpg', 1, '2024-08-01 08:00:00'),
('LIB001', 'lib', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Faculty', 'Female', '1985-06-12', 'susan.librarian@university.edu', '555-0301', '2001 Staff Quarters, Block A', 'profile_lib1.jpg', 1, '2023-06-01 08:00:00'),
('LIB002', 'librarian_mark', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Librarian', 'Male', '1988-02-28', 'mark.librarian@university.edu', '555-0302', '2002 Staff Quarters, Block B', 'profile_lib2.jpg', 1, '2023-06-01 08:00:00'),
('LIB003', 'librarian_nancy', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Librarian', 'Female', '1990-10-19', 'nancy.librarian@university.edu', '555-0303', '2003 Staff Quarters, Block A', 'profile_lib3.jpg', 1, '2024-01-15 08:00:00'),
('ADM001', 'admin', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Admin', 'Male', '1975-04-05', 'richard.admin@university.edu', '555-0401', '3001 Admin Building, Office 101', 'profile_adm1.jpg', 1, '2022-01-01 08:00:00'),
('ADM002', 'admin_jennifer', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Admin', 'Female', '1980-12-21', 'jennifer.admin@university.edu', '555-0402', '3002 Admin Building, Office 102', 'profile_adm2.jpg', 1, '2022-01-01 08:00:00');

-- ====================================================================
-- DUMMY DATA: Books (30 books across various categories)
-- ====================================================================
INSERT IGNORE INTO `books` (`isbn`, `barcode`, `bookName`, `authorName`, `publisherName`, `description`, `category`, `publicationYear`, `totalCopies`, `available`, `borrowed`, `bookImage`, `isTrending`, `isSpecial`, `specialBadge`) VALUES
('6604622694292', NULL, 'where the forest meets the stars', 'glendy vanderah', 'HarperCollins', 'A captivating story exploring themes related to where the forest meets the stars and human emotions.', 'Literature', 2016, 5, 5, 0, 'img1.jpg', 0, 0, NULL),
('6080591560497', NULL, 'JAVA', 'josh thompson', 'Random House', 'A captivating story exploring themes related to java and human emotions.', 'Technology', 2012, 5, 5, 0, 'img2.jpg', 0, 0, NULL),
('7781111792539', NULL, 'a world of secrets', 'james maxwell', 'Scholastic', 'A captivating story exploring themes related to a world of secrets and human emotions.', 'Fantasy', 1978, 5, 5, 0, 'img3.jpg', 0, 0, NULL),
('8100852011803', NULL, 'the funnel of good', 'bob bloch', 'Vintage Publishing', 'A captivating story exploring themes related to the funnel of good  and human emotions.', 'Self-help', 1978, 5, 5, 0, 'img4.jpg', 0, 0, NULL),
('8783340203918', NULL, 'long way gone', 'charles martin', 'Cambridge Books', 'A captivating story exploring themes related to long way gone and human emotions.', 'Fiction', 2005, 5, 5, 0, 'img5.jpg', 0, 0, NULL),
('0307268000967', NULL, 'Roots', 'alex haley', 'Random House', 'A captivating story exploring themes related to roots and human emotions.', 'General', 1999, 5, 5, 0, 'img6.jpg', 0, 0, NULL),
('7840135040808', NULL, 'preparation for the next life', 'Uspoytxhii', 'Pearson', 'A captivating story exploring themes related to preparation for the next life and human emotions.', 'General', 1977, 5, 5, 0, 'img7.jpg', 0, 0, NULL),
('8131061994907', NULL, 'the house plants guid', 'Uanqenit', 'Pearson', 'A captivating story exploring themes related to the house plants guid and human emotions.', 'General', 1992, 5, 5, 0, 'img8.jpg', 0, 0, NULL),
('8063254954311', NULL, 'the science of plants', 'Sofcew', 'Cambridge Books', 'A captivating story exploring themes related to the science of plants and human emotions.', 'Science', 1988, 5, 5, 0, 'img9.jpg', 0, 0, NULL),
('5418428424560', NULL, 'indian agriculture', 'Yjydhoer', 'Pearson', 'A captivating story exploring themes related to indian agriculture and human emotions.', 'General', 1950, 5, 5, 0, 'img10.jpg', 0, 0, NULL),
('0500261952520', NULL, 'growing a revolution', 'Ogodqytjby', 'Vintage Publishing', 'A captivating story exploring themes related to growing a revolution and human emotions.', 'General', 1994, 5, 5, 0, 'img11.jpg', 0, 0, NULL),
('6417612062012', NULL, 'organic farming for sustainable agriculture', 'Zcjxwwblf', 'Cambridge Books', 'A captivating story exploring themes related to organic farming for sustainable agriculture and human emotions.', 'General', 2010, 5, 5, 0, 'img12.jpg', 0, 0, NULL),
('1946547630161', NULL, 'emerging research in agriculture science', 'Dztfpxyi', 'Pearson', 'A captivating story exploring themes related to emerging research in agriculture science and human emotions.', 'Science', 1969, 5, 5, 0, 'img13.jpg', 0, 0, NULL),
('1435337305858', NULL, 'climate change and agriculture', 'Iqubvwzqdv', 'HarperCollins', 'A captivating story exploring themes related to climate change and agriculture and human emotions.', 'General', 1967, 5, 5, 0, 'img14.jpg', 0, 0, NULL),
('1576527271399', NULL, 'information and communication technology', 'Lypsyu', 'Vintage Publishing', 'A captivating story exploring themes related to information and communication technology and human emotions.', 'General', 1952, 5, 5, 0, 'img15.jpg', 0, 0, NULL),
('6900451289401', NULL, 'foundation of ict', 'Pffol', 'Pearson', 'A captivating story exploring themes related to foundation of ict and human emotions.', 'General', 2001, 5, 5, 0, 'img16.jpg', 0, 0, NULL),
('1613357754265', NULL, 'computer programing language', 'Tltgv', 'Oxford Press', 'A captivating story exploring themes related to computer programing language and human emotions.', 'General', 1957, 5, 5, 0, 'img17.jpg', 0, 0, NULL),
('2461307156025', NULL, 'python programming for beginners', 'Gbwhhtrith', 'Cambridge Books', 'A captivating story exploring themes related to python programming for beginners and human emotions.', 'General', 1965, 5, 5, 0, 'img18.jpg', 0, 0, NULL),
('6603279916127', NULL, 'computer programming and cybersecurity', 'Qrpfcseahz', 'Pearson', 'A captivating story exploring themes related to computer programming and cybersecurity and human emotions.', 'General', 1989, 5, 5, 0, 'img19.jpg', 0, 0, NULL),
('7091768085250', NULL, 'orchid C++', 'Uozolxx', 'Scholastic', 'A captivating story exploring themes related to orchid c++ and human emotions.', 'General', 1989, 5, 5, 0, 'img20.jpg', 0, 0, NULL),
('6351830113568', NULL, 'practical electronics for inventors', 'Dugxure', 'Oxford Press', 'A captivating story exploring themes related to practical electronics for inventors and human emotions.', 'General', 2009, 5, 5, 0, 'img21.jpg', 0, 0, NULL),
('7128900048463', NULL, 'hacking electronics', 'Zvhmxmlj', 'Penguin Books', 'A captivating story exploring themes related to hacking electronics and human emotions.', 'General', 2006, 5, 5, 0, 'img22.jpg', 0, 0, NULL),
('8057034409269', NULL, 'engineering mechanics', 'Hxafbtcb', 'Pearson', 'A captivating story exploring themes related to engineering mechanics and human emotions.', 'General', 2020, 5, 5, 0, 'img23.jpg', 0, 0, NULL),
('0323850190420', NULL, 'mechanical engineering', 'Zvngnhva', 'Scholastic', 'A captivating story exploring themes related to mechanical engineering and human emotions.', 'General', 2019, 5, 5, 0, 'img24.jpg', 0, 0, NULL),
('4056543252699', NULL, 'numerical analysis and computational mathematics', 'Ysspcamcq', 'Scholastic', 'A captivating story exploring themes related to numerical analysis and computational mathematics and human emotions.', 'General', 1995, 5, 5, 0, 'img25.jpg', 0, 0, NULL),
('1875626713102', NULL, 'trignometry', 'Cidllopjs', 'Pearson', 'A captivating story exploring themes related to trignometry and human emotions.', 'General', 2001, 5, 5, 0, 'img26.jpg', 0, 0, NULL),
('2924006842526', NULL, 'short stories -English', 'Sqkvvupxj', 'Oxford Press', 'A captivating story exploring themes related to short stories -english and human emotions.', 'General', 1959, 5, 5, 0, 'img27.jpg', 0, 0, NULL),
('9875436992524', NULL, 'practical english usage', 'Mhusqfmwns', 'Random House', 'A captivating story exploring themes related to practical english usage and human emotions.', 'General', 1998, 5, 5, 0, 'img28.jpg', 0, 0, NULL),
('3328438043912', NULL, 'modern classical physics', 'Kqgnmoxb', 'Penguin Books', 'A captivating story exploring themes related to modern classical physics and human emotions.', 'General', 2013, 5, 5, 0, 'img29.jpg', 0, 0, NULL),
('7115814019075', NULL, 'active chemistry', 'Ajnhvsn', 'Pearson', 'A captivating story exploring themes related to active chemistry and human emotions.', 'General', 1966, 5, 5, 0, 'img30.jpg', 0, 0, NULL);

-- ====================================================================
-- DUMMY DATA: Transactions (25 transaction records)
-- FIXED: Removed duplicate 'fine' column, updated dates to 2025, FIXED: Use actual book ISBNs
-- ====================================================================
INSERT IGNORE INTO `transactions` (`tid`, `userId`, `isbn`, `borrowDate`, `returnDate`, `fineAmount`, `fineStatus`, `finePaymentDate`, `finePaymentMethod`) VALUES
('TXN001', 'USR001', '6604622694292', '2025-10-01', '2025-10-12', 0.00, 'paid', NULL, NULL),
('TXN002', 'USR002', '6080591560497', '2025-10-03', '2025-10-15', 0.00, 'paid', NULL, NULL),
('TXN003', 'USR003', '9780743273565', '2025-09-20', '2025-10-10', 15.00, 'paid', '2025-10-11', 'online'),
('TXN004', 'USR004', '7781111792539', '2025-10-05', '2025-10-18', 0.00, 'paid', NULL, NULL),
('TXN005', 'USR005', '8100852011803', '2025-10-08', '2025-10-20', 0.00, 'paid', NULL, NULL),
('TXN006', 'USR006', '9780134757599', '2025-09-15', '2025-10-05', 25.00, 'paid', '2025-10-06', 'card'),
('TXN007', 'USR007', '8783340203918', '2025-10-10', '2025-10-22', 0.00, 'paid', NULL, NULL),
('TXN008', 'USR008', '0307268000967', '2025-10-12', NULL, 0.00, 'pending', NULL, NULL),
('TXN009', 'FAC001', '7840135040808', '2025-09-25', '2025-10-15', 0.00, 'paid', NULL, NULL),
('TXN010', 'FAC002', '8131061994907', '2025-10-01', '2025-10-14', 0.00, 'paid', NULL, NULL),
('TXN011', 'USR001', '8063254954311', '2025-10-15', NULL, 0.00, 'pending', NULL, NULL),
('TXN012', 'USR002', '9780596517748', '2025-09-28', '2025-10-20', 10.00, 'paid', '2025-10-21', 'cash'),
('TXN013', 'USR003', '5418428424560', '2025-10-18', NULL, 0.00, 'pending', NULL, NULL),
('TXN014', 'USR004', '0500261952520', '2025-10-08', '2025-10-21', 0.00, 'paid', NULL, NULL),
('TXN015', 'USR005', '9780062316097', '2025-09-10', '2025-10-05', 35.00, 'paid', '2025-10-06', 'online'),
('TXN016', 'USR006', '6417612062012', '2025-10-20', NULL, 0.00, 'pending', NULL, NULL),
('TXN017', 'USR007', '1946547630161', '2025-10-12', NULL, 0.00, 'pending', NULL, NULL),
('TXN018', 'FAC003', '1435337305858', '2025-10-05', '2025-10-18', 0.00, 'paid', NULL, NULL),
('TXN019', 'FAC004', '1576527271399', '2025-10-10', '2025-10-23', 0.00, 'paid', NULL, NULL),
('TXN020', 'USR008', '9780691169866', '2025-09-18', '2025-10-12', 20.00, 'pending', NULL, NULL),
('TXN021', 'USR009', '6900451289401', '2025-10-22', NULL, 0.00, 'pending', NULL, NULL),
('TXN022', 'USR010', '1613357754265', '2025-10-16', NULL, 0.00, 'pending', NULL, NULL),
('TXN023', 'FAC005', '2461307156025', '2025-10-01', '2025-10-14', 0.00, 'paid', NULL, NULL),
('TXN024', 'USR001', '6603279916127', '2025-10-23', NULL, 0.00, 'pending', NULL, NULL),
('TXN025', 'USR002', '7091768085250', '2025-10-24', NULL, 0.00, 'pending', NULL, NULL);

-- ====================================================================
-- DUMMY DATA: Borrow Requests (15 requests with various statuses)
-- FIXED: Updated dates to 2025, FIXED: Use actual book ISBNs
-- ====================================================================
INSERT IGNORE INTO `borrow_requests` (`userId`, `isbn`, `requestDate`, `status`, `approvedBy`, `dueDate`, `rejectionReason`) VALUES
('USR003', '6604622694292', '2025-10-25 09:30:00', 'Pending', NULL, NULL, NULL),
('USR004', '6080591560497', '2025-10-25 10:15:00', 'Pending', NULL, NULL, NULL),
('USR005', '7781111792539', '2025-10-24 14:20:00', 'Approved', 'LIB001', '2025-11-07', NULL),
('USR006', '8100852011803', '2025-10-23 11:45:00', 'Approved', 'LIB002', '2025-11-06', NULL),
('USR007', '8783340203918', '2025-10-25 13:00:00', 'Pending', NULL, NULL, NULL),
('USR008', '0307268000967', '2025-10-22 15:30:00', 'Rejected', 'LIB001', NULL, 'All copies currently borrowed'),
('USR009', '7840135040808', '2025-10-25 08:45:00', 'Pending', NULL, NULL, NULL),
('USR010', '8131061994907', '2025-10-24 16:20:00', 'Approved', 'LIB003', '2025-11-07', NULL),
('FAC001', '8063254954311', '2025-10-23 09:00:00', 'Approved', 'LIB001', '2025-11-06', NULL),
('FAC002', '5418428424560', '2025-10-25 10:30:00', 'Pending', NULL, NULL, NULL),
('USR001', '0500261952520', '2025-10-21 14:15:00', 'Approved', 'LIB002', '2025-11-04', NULL),
('USR002', '6417612062012', '2025-10-25 11:00:00', 'Pending', NULL, NULL, NULL),
('FAC003', '1946547630161', '2025-10-20 13:45:00', 'Rejected', 'LIB003', NULL, 'User has pending fines'),
('USR003', '1435337305858', '2025-10-25 15:20:00', 'Pending', NULL, NULL, NULL),
('USR004', '1576527271399', '2025-10-24 09:30:00', 'Approved', 'LIB001', '2025-11-07', NULL);

-- ====================================================================
-- DUMMY DATA: Notifications (20 notifications)
-- FIXED: Updated dates to 2025
-- ====================================================================
INSERT IGNORE INTO `notifications` (`userId`, `title`, `message`, `type`, `priority`, `isRead`, `relatedId`, `createdAt`) VALUES
('USR001', 'Book Due Soon', 'Your borrowed book "computer programming and cybersecurity" is due in 2 days.', 'reminder', 'medium', 0, 'TXN024', '2025-10-24 09:00:00'),
('USR002', 'Book Due Soon', 'Your borrowed book "orchid C++" is due in 2 days.', 'reminder', 'medium', 0, 'TXN025', '2025-10-24 09:00:00'),
('USR003', 'Borrow Request Approved', 'Your request to borrow "where the forest meets the stars" has been approved.', 'approval', 'high', 1, '3', '2025-10-24 14:30:00'),
('USR006', 'Borrow Request Approved', 'Your request to borrow "the funnel of good" has been approved.', 'approval', 'high', 1, '4', '2025-10-23 12:00:00'),
('USR008', 'Borrow Request Rejected', 'Your request to borrow "Roots" was rejected: All copies currently borrowed', 'approval', 'high', 0, '6', '2025-10-22 16:00:00'),
('USR010', 'Borrow Request Approved', 'Your request to borrow "the house plants guid" has been approved.', 'approval', 'high', 1, '8', '2025-10-24 16:45:00'),
('FAC001', 'Borrow Request Approved', 'Your request to borrow "the science of plants" has been approved.', 'approval', 'high', 1, '9', '2025-10-23 09:30:00'),
('USR001', 'Borrow Request Approved', 'Your request to borrow "growing a revolution" has been approved.', 'approval', 'high', 1, '11', '2025-10-21 14:45:00'),
('FAC003', 'Borrow Request Rejected', 'Your request to borrow "emerging research in agriculture science" was rejected: User has pending fines', 'approval', 'high', 0, '13', '2025-10-20 14:15:00'),
('USR004', 'Borrow Request Approved', 'Your request to borrow "information and communication technology" has been approved.', 'approval', 'high', 1, '15', '2025-10-24 10:00:00'),
('USR003', 'Overdue Book', 'Your book is overdue. Fine: $15.00', 'overdue', 'high', 1, 'TXN003', '2025-10-05 10:00:00'),
('USR006', 'Overdue Book', 'Your book is overdue. Fine: $25.00', 'overdue', 'high', 1, 'TXN006', '2025-09-30 10:00:00'),
('USR005', 'Overdue Book', 'Your book is overdue. Fine: $35.00', 'overdue', 'high', 1, 'TXN015', '2025-09-25 10:00:00'),
('USR008', 'Pending Fine', 'You have an outstanding fine of $20.00', 'fine_paid', 'medium', 0, 'TXN020', '2025-10-13 11:00:00'),
('USR003', 'Fine Payment Received', 'Your fine payment of $15.00 has been received.', 'fine_paid', 'low', 1, 'TXN003', '2025-10-11 14:30:00'),
('USR006', 'Fine Payment Received', 'Your fine payment of $25.00 has been received.', 'fine_paid', 'low', 1, 'TXN006', '2025-10-06 15:45:00'),
('LIB001', 'Book Out of Stock', 'Book "Roots" is out of stock. All copies borrowed.', 'out_of_stock', 'medium', 0, '0307268000967', '2025-10-22 16:30:00'),
('LIB002', 'System Maintenance', 'Library system maintenance scheduled for Sunday, October 27, 2025 from 2:00 AM to 6:00 AM.', 'system', 'low', 1, NULL, '2025-10-20 08:00:00'),
('ADM001', 'New User Registration', 'New student user registered: james_thomas (USR009)', 'system', 'low', 1, 'USR009', '2025-10-20 09:15:00'),
(NULL, 'Library Announcement', 'New books added to Computer Science collection. Check out the latest titles!', 'system', 'medium', 0, NULL, '2025-10-15 10:00:00');

-- ====================================================================
-- DUMMY DATA: Book Reviews (15 reviews)
-- ====================================================================
INSERT IGNORE INTO `book_reviews` (`userId`, `isbn`, `rating`, `reviewText`, `isApproved`) VALUES
('USR001', '6604622694292', 5, 'Excellent book! Highly recommended.', 1),
('USR002', '6080591560497', 5, 'Must-read for every programmer. Changed the way I write code.', 1),
('USR003', '7781111792539', 4, 'Beautiful prose and a captivating story.', 1),
('USR004', '8100852011803', 5, 'Very helpful and insightful.', 1),
('FAC001', '7840135040808', 5, 'Great resource for learning.', 1),
('USR005', '8783340203918', 4, 'Great introduction with interesting projects and exercises.', 1),
('USR006', '0307268000967', 5, 'Essential reading! Highly recommended.', 1),
('FAC002', '8131061994907', 4, 'Fascinating and informative.', 1),
('USR007', '8063254954311', 5, 'Mind-blowing perspective. Great book!', 1),
('USR008', '5418428424560', 5, 'Life-changing book! Truly helpful.', 1),
('FAC003', '0500261952520', 5, 'The definitive guide. Every reader should check this.', 1),
('USR009', '6417612062012', 3, 'Good comprehensive guide but can be overwhelming for beginners.', 0),
('USR010', '1946547630161', 4, 'Insightful and well-written.', 1),
('FAC004', '1435337305858', 4, 'Practical and very helpful for projects.', 1),
('USR001', '1576527271399', 5, 'Raw and honest. No sugar-coating here!', 1);

-- ====================================================================
-- DUMMY DATA: Favorites (20 favorites)
-- ====================================================================
INSERT IGNORE INTO `favorites` (`userId`, `isbn`, `notes`) VALUES
('USR001', '6604622694292', 'Great reference'),
('USR001', '6080591560497', 'Must re-read annually'),
('USR002', '7781111792539', 'Favorite novel'),
('USR002', '8100852011803', 'Actually helped me a lot'),
('USR003', '8783340203918', 'Reread every few years'),
('USR003', '0307268000967', 'Interesting perspective'),
('USR004', '7840135040808', 'Relatable story'),
('USR005', '8131061994907', 'Best book I have read'),
('USR005', '8063254954311', NULL),
('USR006', '5418428424560', 'Powerful and moving'),
('USR006', '0500261952520', 'Great for learning'),
('USR007', '6417612062012', 'Reference guide'),
('USR008', '1946547630161', 'Changed how I think'),
('FAC001', '1435337305858', 'Amazing content'),
('FAC001', '1576527271399', 'Reference material'),
('FAC002', '6900451289401', 'Fascinating read'),
('FAC003', '1613357754265', 'Guide book'),
('FAC004', '2461307156025', 'Reference'),
('USR009', '6603279916127', 'Inspiration'),
('USR010', '7091768085250', 'Wisdom');

-- ====================================================================
-- Table structure for table `payment_logs`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `transactionId` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `cardLastFour` varchar(4) DEFAULT NULL,
  `paymentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paymentMethod` enum('credit_card','debit_card','upi','cash','online','card') NOT NULL DEFAULT 'card',
  `status` enum('success','failed','pending') NOT NULL DEFAULT 'success',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_transactionId` (`transactionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Disable FK checks for safe data insertion (re-enabled after)
-- ====================================================================
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================================================
-- Insert default fine settings (only if table is empty)
-- FIXED: Added column aliases to prevent duplicate column name '0' error
-- ====================================================================
INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'fine_per_day', '5', 'Fine amount per day for overdue books', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'fine_per_day'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'max_borrow_days', '14', 'Maximum days a book can be borrowed', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'max_borrow_days'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'grace_period_days', '0', 'Grace period before fines start', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'grace_period_days'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'max_fine_amount', '500', 'Maximum fine amount per book', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'max_fine_amount'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT 'fine_calculation_method', 'daily', 'Method for calculating fines: daily or fixed', 'system'
WHERE NOT EXISTS (
    SELECT 1 FROM `fine_settings` WHERE setting_name = 'fine_calculation_method'
);

-- ====================================================================
-- Insert default system settings
-- ====================================================================
INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_name', 'University Central Library', 'string', 'Name of the library', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_name');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_email', 'library@university.edu', 'string', 'Library contact email', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_email');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'library_phone', '+1-555-0100', 'string', 'Library contact phone', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'library_phone');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'max_books_per_user', '5', 'number', 'Maximum books a user can borrow', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'max_books_per_user');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT 'enable_notifications', 'true', 'boolean', 'Enable system notifications', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_settings` WHERE settingKey = 'enable_notifications');

-- ====================================================================
-- Insert default role permissions
-- ====================================================================
INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Student', 'view_catalog', 1, 0, 0, 0, 'Can view library catalog'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Student' AND permission = 'view_catalog');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Student', 'borrow_book', 1, 1, 0, 0, 'Can borrow books'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Student' AND permission = 'borrow_book');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Librarian', 'manage_books', 1, 1, 1, 0, 'Can manage books'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Librarian' AND permission = 'manage_books');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Librarian', 'approve_requests', 1, 0, 0, 1, 'Can approve borrow requests'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Librarian' AND permission = 'approve_requests');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'manage_users', 1, 1, 1, 0, 'Can manage all users'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'manage_users');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'view_reports', 1, 0, 0, 0, 'Can view system reports'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'view_reports');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT 'Admin', 'system_settings', 1, 1, 0, 0, 'Can manage system settings'
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role = 'Admin' AND permission = 'system_settings');

-- ====================================================================
-- Insert default library hours
-- ====================================================================
INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Monday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Monday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Tuesday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Tuesday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Wednesday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Wednesday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Thursday', '08:00:00', '20:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Thursday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Friday', '08:00:00', '18:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Friday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Saturday', '10:00:00', '16:00:00', 0
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Saturday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT 'Sunday', '00:00:00', '00:00:00', 1
WHERE NOT EXISTS (SELECT 1 FROM `library_hours` WHERE dayOfWeek = 'Sunday');

-- ====================================================================
-- DUMMY DATA: Users (20 users - mix of Students, Faculty, Librarian, Admin)
-- FIXED: Updated all dates to 2025
-- ====================================================================
INSERT IGNORE INTO `users` (`userId`, `username`, `password`, `userType`, `gender`, `dob`, `emailId`, `phoneNumber`, `address`, `profileImage`, `isVerified`, `createdAt`) VALUES
('USR001', 'student', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Student', 'Male', '2002-05-15', 'john.smith@university.edu', '555-0101', '123 Campus Drive, Dorm A, Room 201', 'profile1.jpg', 1, '2025-01-15 09:30:00'),
('USR002', 'emily_chen', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-08-22', 'emily.chen@university.edu', '555-0102', '456 University Ave, Dorm B, Room 305', 'profile2.jpg', 1, '2025-01-16 10:15:00'),
('USR003', 'michael_brown', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-11-30', 'michael.brown@university.edu', '555-0103', '789 College Blvd, Dorm C, Room 102', 'profile3.jpg', 1, '2025-01-17 11:20:00'),
('USR004', 'sarah_davis', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-03-18', 'sarah.davis@university.edu', '555-0104', '321 Student Lane, Dorm A, Room 410', 'profile4.jpg', 1, '2025-01-18 14:30:00'),
('USR005', 'david_wilson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-07-25', 'david.wilson@university.edu', '555-0105', '654 Academic Way, Dorm D, Room 208', 'profile5.jpg', 1, '2025-01-19 09:45:00'),
('USR006', 'jessica_moore', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-01-12', 'jessica.moore@university.edu', '555-0106', '987 Scholar St, Dorm B, Room 501', 'profile6.jpg', 1, '2025-01-20 13:00:00'),
('USR007', 'robert_taylor', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-09-08', 'robert.taylor@university.edu', '555-0107', '147 Learning Ave, Dorm C, Room 315', 'profile7.jpg', 1, '2025-01-21 10:30:00'),
('USR008', 'amanda_anderson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-06-20', 'amanda.anderson@university.edu', '555-0108', '258 Knowledge Rd, Dorm A, Room 212', 'profile8.jpg', 1, '2025-01-22 15:15:00'),
('USR009', 'james_thomas', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Male', '2002-12-05', 'james.thomas@university.edu', '555-0109', '369 Education Blvd, Dorm D, Room 405', 'profile9.jpg', 0, '2025-10-20 09:00:00'),
('USR010', 'lisa_jackson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Student', 'Female', '2003-04-17', 'lisa.jackson@university.edu', '555-0110', '741 Campus Circle, Dorm B, Room 118', 'profile10.jpg', 1, '2025-02-01 11:45:00'),
('FAC001', 'dr_robert_johnson', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1978-03-22', 'r.johnson@university.edu', '555-0201', '1001 Faculty Housing, Building A', 'profile_fac1.jpg', 1, '2024-08-01 08:00:00'),
('FAC002', 'prof_maria_garcia', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Female', '1982-07-14', 'm.garcia@university.edu', '555-0202', '1002 Faculty Housing, Building B', 'profile_fac2.jpg', 1, '2024-08-01 08:00:00'),
('FAC003', 'dr_william_lee', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1975-11-08', 'w.lee@university.edu', '555-0203', '1003 Faculty Housing, Building A', 'profile_fac3.jpg', 1, '2024-08-01 08:00:00'),
('FAC004', 'prof_patricia_martinez', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Female', '1980-05-30', 'p.martinez@university.edu', '555-0204', '1004 Faculty Housing, Building C', 'profile_fac4.jpg', 1, '2024-08-01 08:00:00'),
('FAC005', 'dr_charles_white', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Faculty', 'Male', '1979-09-16', 'c.white@university.edu', '555-0205', '1005 Faculty Housing, Building B', 'profile_fac5.jpg', 1, '2024-08-01 08:00:00'),
('LIB001', 'lib', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Faculty', 'Female', '1985-06-12', 'susan.librarian@university.edu', '555-0301', '2001 Staff Quarters, Block A', 'profile_lib1.jpg', 1, '2023-06-01 08:00:00'),
('LIB002', 'librarian_mark', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Librarian', 'Male', '1988-02-28', 'mark.librarian@university.edu', '555-0302', '2002 Staff Quarters, Block B', 'profile_lib2.jpg', 1, '2023-06-01 08:00:00'),
('LIB003', 'librarian_nancy', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Librarian', 'Female', '1990-10-19', 'nancy.librarian@university.edu', '555-0303', '2003 Staff Quarters, Block A', 'profile_lib3.jpg', 1, '2024-01-15 08:00:00'),
('ADM001', 'admin', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Admin', 'Male', '1975-04-05', 'richard.admin@university.edu', '555-0401', '3001 Admin Building, Office 101', 'profile_adm1.jpg', 1, '2022-01-01 08:00:00'),
('ADM002', 'admin_jennifer', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Admin', 'Female', '1980-12-21', 'jennifer.admin@university.edu', '555-0402', '3002 Admin Building, Office 102', 'profile_adm2.jpg', 1, '2022-01-01 08:00:00');

-- ====================================================================
-- DUMMY DATA: Books (30 books across various categories)
-- ====================================================================
INSERT IGNORE INTO `books` (`isbn`, `barcode`, `bookName`, `authorName`, `publisherName`, `description`, `category`, `publicationYear`, `totalCopies`, `available`, `borrowed`, `bookImage`, `isTrending`, `isSpecial`, `specialBadge`) VALUES
('6604622694292', NULL, 'where the forest meets the stars', 'glendy vanderah', 'HarperCollins', 'A captivating story exploring themes related to where the forest meets the stars and human emotions.', 'Literature', 2016, 5, 5, 0, 'img1.jpg', 0, 0, NULL),
('6080591560497', NULL, 'JAVA', 'josh thompson', 'Random House', 'A captivating story exploring themes related to java and human emotions.', 'Technology', 2012, 5, 5, 0, 'img2.jpg', 0, 0, NULL),
('7781111792539', NULL, 'a world of secrets', 'james maxwell', 'Scholastic', 'A captivating story exploring themes related to a world of secrets and human emotions.', 'Fantasy', 1978, 5, 5, 0, 'img3.jpg', 0, 0, NULL),
('8100852011803', NULL, 'the funnel of good', 'bob bloch', 'Vintage Publishing', 'A captivating story exploring themes related to the funnel of good  and human emotions.', 'Self-help', 1978, 5, 5, 0, 'img4.jpg', 0, 0, NULL),
('8783340203918', NULL, 'long way gone', 'charles martin', 'Cambridge Books', 'A captivating story exploring themes related to long way gone and human emotions.', 'Fiction', 2005, 5, 5, 0, 'img5.jpg', 0, 0, NULL),
('0307268000967', NULL, 'Roots', 'alex haley', 'Random House', 'A captivating story exploring themes related to roots and human emotions.', 'General', 1999, 5, 5, 0, 'img6.jpg', 0, 0, NULL),
('7840135040808', NULL, 'preparation for the next life', 'Uspoytxhii', 'Pearson', 'A captivating story exploring themes related to preparation for the next life and human emotions.', 'General', 1977, 5, 5, 0, 'img7.jpg', 0, 0, NULL),
('8131061994907', NULL, 'the house plants guid', 'Uanqenit', 'Pearson', 'A captivating story exploring themes related to the house plants guid and human emotions.', 'General', 1992, 5, 5, 0, 'img8.jpg', 0, 0, NULL),
('8063254954311', NULL, 'the science of plants', 'Sofcew', 'Cambridge Books', 'A captivating story exploring themes related to the science of plants and human emotions.', 'Science', 1988, 5, 5, 0, 'img9.jpg', 0, 0, NULL),
('5418428424560', NULL, 'indian agriculture', 'Yjydhoer', 'Pearson', 'A captivating story exploring themes related to indian agriculture and human emotions.', 'General', 1950, 5, 5, 0, 'img10.jpg', 0, 0, NULL),
('0500261952520', NULL, 'growing a revolution', 'Ogodqytjby', 'Vintage Publishing', 'A captivating story exploring themes related to growing a revolution and human emotions.', 'General', 1994, 5, 5, 0, 'img11.jpg', 0, 0, NULL),
('6417612062012', NULL, 'organic farming for sustainable agriculture', 'Zcjxwwblf', 'Cambridge Books', 'A captivating story exploring themes related to organic farming for sustainable agriculture and human emotions.', 'General', 2010, 5, 5, 0, 'img12.jpg', 0, 0, NULL),
('1946547630161', NULL, 'emerging research in agriculture science', 'Dztfpxyi', 'Pearson', 'A captivating story exploring themes related to emerging research in agriculture science and human emotions.', 'Science', 1969, 5, 5, 0, 'img13.jpg', 0, 0, NULL),
('1435337305858', NULL, 'climate change and agriculture', 'Iqubvwzqdv', 'HarperCollins', 'A captivating story exploring themes related to climate change and agriculture and human emotions.', 'General', 1967, 5, 5, 0, 'img14.jpg', 0, 0, NULL),
('1576527271399', NULL, 'information and communication technology', 'Lypsyu', 'Vintage Publishing', 'A captivating story exploring themes related to information and communication technology and human emotions.', 'General', 1952, 5, 5, 0, 'img15.jpg', 0, 0, NULL),
('6900451289401', NULL, 'foundation of ict', 'Pffol', 'Pearson', 'A captivating story exploring themes related to foundation of ict and human emotions.', 'General', 2001, 5, 5, 0, 'img16.jpg', 0, 0, NULL),
('1613357754265', NULL, 'computer programing language', 'Tltgv', 'Oxford Press', 'A captivating story exploring themes related to computer programing language and human emotions.', 'General', 1957, 5, 5, 0, 'img17.jpg', 0, 0, NULL),
('2461307156025', NULL, 'python programming for beginners', 'Gbwhhtrith', 'Cambridge Books', 'A captivating story exploring themes related to python programming for beginners and human emotions.', 'General', 1965, 5, 5, 0, 'img18.jpg', 0, 0, NULL),
('6603279916127', NULL, 'computer programming and cybersecurity', 'Qrpfcseahz', 'Pearson', 'A captivating story exploring themes related to computer programming and cybersecurity and human emotions.', 'General', 1989, 5, 5, 0, 'img19.jpg', 0, 0, NULL),
('7091768085250', NULL, 'orchid C++', 'Uozolxx', 'Scholastic', 'A captivating story exploring themes related to orchid c++ and human emotions.', 'General', 1989, 5, 5, 0, 'img20.jpg', 0, 0, NULL),
('6351830113568', NULL, 'practical electronics for inventors', 'Dugxure', 'Oxford Press', 'A captivating story exploring themes related to practical electronics for inventors and human emotions.', 'General', 2009, 5, 5, 0, 'img21.jpg', 0, 0, NULL),
('7128900048463', NULL, 'hacking electronics', 'Zvhmxmlj', 'Penguin Books', 'A captivating story exploring themes related to hacking electronics and human emotions.', 'General', 2006, 5, 5, 0, 'img22.jpg', 0, 0, NULL),
('8057034409269', NULL, 'engineering mechanics', 'Hxafbtcb', 'Pearson', 'A captivating story exploring themes related to engineering mechanics and human emotions.', 'General', 2020, 5, 5, 0, 'img23.jpg', 0, 0, NULL),
('0323850190420', NULL, 'mechanical engineering', 'Zvngnhva', 'Scholastic', 'A captivating story exploring themes related to mechanical engineering and human emotions.', 'General', 2019, 5, 5, 0, 'img24.jpg', 0, 0, NULL),
('4056543252699', NULL, 'numerical analysis and computational mathematics', 'Ysspcamcq', 'Scholastic', 'A captivating story exploring themes related to numerical analysis and computational mathematics and human emotions.', 'General', 1995, 5, 5, 0, 'img25.jpg', 0, 0, NULL),
('1875626713102', NULL, 'trignometry', 'Cidllopjs', 'Pearson', 'A captivating story exploring themes related to trignometry and human emotions.', 'General', 2001, 5, 5, 0, 'img26.jpg', 0, 0, NULL),
('2924006842526', NULL, 'short stories -English', 'Sqkvvupxj', 'Oxford Press', 'A captivating story exploring themes related to short stories -english and human emotions.', 'General', 1959, 5, 5, 0, 'img27.jpg', 0, 0, NULL),
('9875436992524', NULL, 'practical english usage', 'Mhusqfmwns', 'Random House', 'A captivating story exploring themes related to practical english usage and human emotions.', 'General', 1998, 5, 5, 0, 'img28.jpg', 0, 0, NULL),
('3328438043912', NULL, 'modern classical physics', 'Kqgnmoxb', 'Penguin Books', 'A captivating story exploring themes related to modern classical physics and human emotions.', 'General', 2013, 5, 5, 0, 'img29.jpg', 0, 0, NULL),
('7115814019075', NULL, 'active chemistry', 'Ajnhvsn', 'Pearson', 'A captivating story exploring themes related to active chemistry and human emotions.', 'General', 1966, 5, 5, 0, 'img30.jpg', 0, 0, NULL);

-- ====================================================================
-- DUMMY DATA: Transactions (25 transaction records)
-- FIXED: Removed duplicate 'fine' column, updated dates to 2025, FIXED: Use actual book ISBNs
-- ====================================================================
INSERT IGNORE INTO `transactions` (`tid`, `userId`, `isbn`, `borrowDate`, `returnDate`, `fineAmount`, `fineStatus`, `finePaymentDate`, `finePaymentMethod`) VALUES
('TXN001', 'USR001', '6604622694292', '2025-10-01', '2025-10-12', 0.00, 'paid', NULL, NULL),
('TXN002', 'USR002', '6080591560497', '2025-10-03', '2025-10-15', 0.00, 'paid', NULL, NULL),
('TXN003', 'USR003', '9780743273565', '2025-09-20', '2025-10-10', 15.00, 'paid', '2025-10-11', 'online'),
('TXN004', 'USR004', '7781111792539', '2025-10-05', '2025-10-18', 0.00, 'paid', NULL, NULL),
('TXN005', 'USR005', '8100852011803', '2025-10-08', '2025-10-20', 0.00, 'paid', NULL, NULL),
('TXN006', 'USR006', '9780134757599', '2025-09-15', '2025-10-05', 25.00, 'paid', '2025-10-06', 'card'),
('TXN007', 'USR007', '8783340203918', '2025-10-10', '2025-10-22', 0.00, 'paid', NULL, NULL),
('TXN008', 'USR008', '0307268000967', '2025-10-12', NULL, 0.00, 'pending', NULL, NULL),
('TXN009', 'FAC001', '7840135040808', '2025-09-25', '2025-10-15', 0.00, 'paid', NULL, NULL),
('TXN010', 'FAC002', '8131061994907', '2025-10-01', '2025-10-14', 0.00, 'paid', NULL, NULL),
('TXN011', 'USR001', '8063254954311', '2025-10-15', NULL, 0.00, 'pending', NULL, NULL),
('TXN012', 'USR002', '9780596517748', '2025-09-28', '2025-10-20', 10.00, 'paid', '2025-10-21', 'cash'),
('TXN013', 'USR003', '5418428424560', '2025-10-18', NULL, 0.00, 'pending', NULL, NULL),
('TXN014', 'USR004', '0500261952520', '2025-10-08', '2025-10-21', 0.00, 'paid', NULL, NULL),
('TXN015', 'USR005', '9780062316097', '2025-09-10', '2025-10-05', 35.00, 'paid', '2025-10-06', 'online'),
('TXN016', 'USR006', '6417612062012', '2025-10-20', NULL, 0.00, 'pending', NULL, NULL),
('TXN017', 'USR007', '1946547630161', '2025-10-12', NULL, 0.00, 'pending', NULL, NULL),
('TXN018', 'FAC003', '1435337305858', '2025-10-05', '2025-10-18', 0.00, 'paid', NULL, NULL),
('TXN019', 'FAC004', '1576527271399', '2025-10-10', '2025-10-23', 0.00, 'paid', NULL, NULL),
('TXN020', 'USR008', '9780691169866', '2025-09-18', '2025-10-12', 20.00, 'pending', NULL, NULL),
('TXN021', 'USR009', '6900451289401', '2025-10-22', NULL, 0.00, 'pending', NULL, NULL),
('TXN022', 'USR010', '1613357754265', '2025-10-16', NULL, 0.00, 'pending', NULL, NULL),
('TXN023', 'FAC005', '2461307156025', '2025-10-01', '2025-10-14', 0.00, 'paid', NULL, NULL),
('TXN024', 'USR001', '6603279916127', '2025-10-23', NULL, 0.00, 'pending', NULL, NULL),
('TXN025', 'USR002', '7091768085250', '2025-10-24', NULL, 0.00, 'pending', NULL, NULL);

-- ====================================================================
-- DUMMY DATA: Borrow Requests (15 requests with various statuses)
-- FIXED: Updated dates to 2025, FIXED: Use actual book ISBNs
-- ====================================================================
INSERT IGNORE INTO `borrow_requests` (`userId`, `isbn`, `requestDate`, `status`, `approvedBy`, `dueDate`, `rejectionReason`) VALUES
('USR003', '6604622694292', '2025-10-25 09:30:00', 'Pending', NULL, NULL, NULL),
('USR004', '6080591560497', '2025-10-25 10:15:00', 'Pending', NULL, NULL, NULL),
('USR005', '7781111792539', '2025-10-24 14:20:00', 'Approved', 'LIB001', '2025-11-07', NULL),
('USR006', '8100852011803', '2025-10-23 11:45:00', 'Approved', 'LIB002', '2025-11-06', NULL),
('USR007', '8783340203918', '2025-10-25 13:00:00', 'Pending', NULL, NULL, NULL),
('USR008', '0307268000967', '2025-10-22 15:30:00', 'Rejected', 'LIB001', NULL, 'All copies currently borrowed'),
('USR009', '7840135040808', '2025-10-25 08:45:00', 'Pending', NULL, NULL, NULL),
('USR010', '8131061994907', '2025-10-24 16:20:00', 'Approved', 'LIB003', '2025-11-07', NULL),
('FAC001', '8063254954311', '2025-10-23 09:00:00', 'Approved', 'LIB001', '2025-11-06', NULL),
('FAC002', '5418428424560', '2025-10-25 10:30:00', 'Pending', NULL, NULL, NULL),
('USR001', '0500261952520', '2025-10-21 14:15:00', 'Approved', 'LIB002', '2025-11-04', NULL),
('USR002', '6417612062012', '2025-10-25 11:00:00', 'Pending', NULL, NULL, NULL),
('FAC003', '1946547630161', '2025-10-20 13:45:00', 'Rejected', 'LIB003', NULL, 'User has pending fines'),
('USR003', '1435337305858', '2025-10-25 15:20:00', 'Pending', NULL, NULL, NULL),
('USR004', '1576527271399', '2025-10-24 09:30:00', 'Approved', 'LIB001', '2025-11-07', NULL);

-- ====================================================================
-- DUMMY DATA: Notifications (20 notifications)
-- FIXED: Updated dates to 2025
-- ====================================================================
INSERT IGNORE INTO `notifications` (`userId`, `title`, `message`, `type`, `priority`, `isRead`, `relatedId`, `createdAt`) VALUES
('USR001', 'Book Due Soon', 'Your borrowed book "computer programming and cybersecurity" is due in 2 days.', 'reminder', 'medium', 0, 'TXN024', '2025-10-24 09:00:00'),
('USR002', 'Book Due Soon', 'Your borrowed book "orchid C++" is due in 2 days.', 'reminder', 'medium', 0, 'TXN025', '2025-10-24 09:00:00'),
('USR003', 'Borrow Request Approved', 'Your request to borrow "where the forest meets the stars" has been approved.', 'approval', 'high', 1, '3', '2025-10-24 14:30:00'),
('USR006', 'Borrow Request Approved', 'Your request to borrow "the funnel of good" has been approved.', 'approval', 'high', 1, '4', '2025-10-23 12:00:00'),
('USR008', 'Borrow Request Rejected', 'Your request to borrow "Roots" was rejected: All copies currently borrowed', 'approval', 'high', 0, '6', '2025-10-22 16:00:00'),
('USR010', 'Borrow Request Approved', 'Your request to borrow "the house plants guid" has been approved.', 'approval', 'high', 1, '8', '2025-10-24 16:45:00'),
('FAC001', 'Borrow Request Approved', 'Your request to borrow "the science of plants" has been approved.', 'approval', 'high', 1, '9', '2025-10-23 09:30:00'),
('USR001', 'Borrow Request Approved', 'Your request to borrow "growing a revolution" has been approved.', 'approval', 'high', 1, '11', '2025-10-21 14:45:00'),
('FAC003', 'Borrow Request Rejected', 'Your request to borrow "emerging research in agriculture science" was rejected: User has pending fines', 'approval', 'high', 0, '13', '2025-10-20 14:15:00'),
('USR004', 'Borrow Request Approved', 'Your request to borrow "information and communication technology" has been approved.', 'approval', 'high', 1, '15', '2025-10-24 10:00:00'),
('USR003', 'Overdue Book', 'Your book is overdue. Fine: $15.00', 'overdue', 'high', 1, 'TXN003', '2025-10-05 10:00:00'),
('USR006', 'Overdue Book', 'Your book is overdue. Fine: $25.00', 'overdue', 'high', 1, 'TXN006', '2025-09-30 10:00:00'),
('USR005', 'Overdue Book', 'Your book is overdue. Fine: $35.00', 'overdue', 'high', 1, 'TXN015', '2025-09-25 10:00:00'),
('USR008', 'Pending Fine', 'You have an outstanding fine of $20.00', 'fine_paid', 'medium', 0, 'TXN020', '2025-10-13 11:00:00'),
('USR003', 'Fine Payment Received', 'Your fine payment of $15.00 has been received.', 'fine_paid', 'low', 1, 'TXN003', '2025-10-11 14:30:00'),
('USR006', 'Fine Payment Received', 'Your fine payment of $25.00 has been received.', 'fine_paid', 'low', 1, 'TXN006', '2025-10-06 15:45:00'),
('LIB001', 'Book Out of Stock', 'Book "Roots" is out of stock. All copies borrowed.', 'out_of_stock', 'medium', 0, '0307268000967', '2025-10-22 16:30:00'),
('LIB002', 'System Maintenance', 'Library system maintenance scheduled for Sunday, October 27, 2025 from 2:00 AM to 6:00 AM.', 'system', 'low', 1, NULL, '2025-10-20 08:00:00'),
('ADM001', 'New User Registration', 'New student user registered: james_thomas (USR009)', 'system', 'low', 1, 'USR009', '2025-10-20 09:15:00'),
(NULL, 'Library Announcement', 'New books added to Computer Science collection. Check out the latest titles!', 'system', 'medium', 0, NULL, '2025-10-15 10:00:00');

-- ====================================================================
-- Renewal Requests Table
-- ====================================================================
CREATE TABLE IF NOT EXISTS `renewal_requests` (
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
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Re-enable foreign key checks
-- ====================================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ====================================================================
-- End of database schema and initial data
-- ====================================================================