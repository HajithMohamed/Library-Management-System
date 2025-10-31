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
  `finePaymentMethod` enum('cash','online','card') NULL,
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
('LIB001', 'lib', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Librarian', 'Female', '1985-06-12', 'susan.librarian@university.edu', '555-0301', '2001 Staff Quarters, Block A', 'profile_lib1.jpg', 1, '2023-06-01 08:00:00'),
('LIB002', 'librarian_mark', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Librarian', 'Male', '1988-02-28', 'mark.librarian@university.edu', '555-0302', '2002 Staff Quarters, Block B', 'profile_lib2.jpg', 1, '2023-06-01 08:00:00'),
('LIB003', 'librarian_nancy', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Librarian', 'Female', '1990-10-19', 'nancy.librarian@university.edu', '555-0303', '2003 Staff Quarters, Block A', 'profile_lib3.jpg', 1, '2024-01-15 08:00:00'),
('ADM001', 'admin', '$2a$12$VI3YXTWXCDaLz6rFU2PQEe6TyGxENyR085V2y1Jhbh8lS1TQq26wm', 'Admin', 'Male', '1975-04-05', 'richard.admin@university.edu', '555-0401', '3001 Admin Building, Office 101', 'profile_adm1.jpg', 1, '2022-01-01 08:00:00'),
('ADM002', 'admin_jennifer', '$2b$10$abcdefghijklmnopqrstuvwxyz123456', 'Admin', 'Female', '1980-12-21', 'jennifer.admin@university.edu', '555-0402', '3002 Admin Building, Office 102', 'profile_adm2.jpg', 1, '2022-01-01 08:00:00');

-- ====================================================================
-- DUMMY DATA: Books (30 books across various categories)
-- ====================================================================
INSERT IGNORE INTO `books` (`isbn`, `barcode`, `bookName`, `authorName`, `publisherName`, `description`, `category`, `publicationYear`, `totalCopies`, `available`, `borrowed`, `bookImage`, `isTrending`, `isSpecial`, `specialBadge`) VALUES
('9780134685991', 'BAR001', 'Effective Java', 'Joshua Bloch', 'Addison-Wesley', 'Essential programming guide for Java developers covering best practices and design patterns.', 'Computer Science', 2018, 5, 3, 2, 'effective_java.jpg', 1, 1, 'Bestseller'),
('9780132350884', 'BAR002', 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 'A handbook of agile software craftsmanship teaching clean code principles.', 'Computer Science', 2008, 4, 2, 2, 'clean_code.jpg', 1, 0, NULL),
('9780596517748', 'BAR003', 'JavaScript: The Good Parts', 'Douglas Crockford', 'O\'Reilly Media', 'Explores the elegant and useful features of JavaScript while avoiding its pitfalls.', 'Computer Science', 2008, 3, 1, 2, 'js_good_parts.jpg', 0, 0, NULL),
('9780201633610', 'BAR004', 'Design Patterns', 'Gang of Four', 'Addison-Wesley', 'Classic reference on design patterns in object-oriented programming.', 'Computer Science', 1994, 6, 4, 2, 'design_patterns.jpg', 0, 1, 'Classic'),
('9781593279509', 'BAR005', 'Eloquent JavaScript', 'Marijn Haverbeke', 'No Starch Press', 'Modern introduction to programming and JavaScript fundamentals.', 'Computer Science', 2018, 4, 3, 1, 'eloquent_js.jpg', 1, 0, NULL),
('9780134494166', 'BAR006', 'Clean Architecture', 'Robert C. Martin', 'Prentice Hall', 'Guide to software structure and design principles for professional programmers.', 'Computer Science', 2017, 3, 2, 1, 'clean_architecture.jpg', 1, 1, 'Bestseller'),
('9781449355739', 'BAR007', 'Learning Python', 'Mark Lutz', 'O\'Reilly Media', 'Comprehensive guide to Python programming for beginners and professionals.', 'Computer Science', 2013, 5, 2, 3, 'learning_python.jpg', 1, 0, NULL),
('9780735619678', 'BAR008', 'Code Complete', 'Steve McConnell', 'Microsoft Press', 'Practical handbook of software construction covering coding best practices.', 'Computer Science', 2004, 4, 3, 1, 'code_complete.jpg', 0, 1, 'Classic'),
('9781617294136', 'BAR009', 'Microservices Patterns', 'Chris Richardson', 'Manning Publications', 'Comprehensive guide to designing and building microservices architectures.', 'Computer Science', 2018, 3, 2, 1, 'microservices.jpg', 1, 0, NULL),
('9780134757599', 'BAR010', 'Refactoring', 'Martin Fowler', 'Addison-Wesley', 'Improving the design of existing code through systematic refactoring techniques.', 'Computer Science', 2018, 4, 2, 2, 'refactoring.jpg', 0, 0, NULL),
('9780743273565', 'BAR011', 'The Great Gatsby', 'F. Scott Fitzgerald', 'Scribner', 'Classic American novel set in the Jazz Age depicting the American Dream.', 'Literature', 1925, 8, 5, 3, 'great_gatsby.jpg', 1, 1, 'Classic'),
('9780061120084', 'BAR012', 'To Kill a Mockingbird', 'Harper Lee', 'Harper Perennial', 'Pulitzer Prize-winning novel about racial injustice in the American South.', 'Literature', 1960, 7, 4, 3, 'mockingbird.jpg', 1, 1, 'Classic'),
('9780451524935', 'BAR013', '1984', 'George Orwell', 'Signet Classic', 'Dystopian novel about totalitarianism and mass surveillance.', 'Literature', 1949, 6, 3, 3, '1984.jpg', 1, 1, 'Classic'),
('9780316769488', 'BAR014', 'The Catcher in the Rye', 'J.D. Salinger', 'Little, Brown', 'Coming-of-age novel about teenage rebellion and alienation.', 'Literature', 1951, 5, 3, 2, 'catcher_rye.jpg', 0, 1, 'Classic'),
('9780141439518', 'BAR015', 'Pride and Prejudice', 'Jane Austen', 'Penguin Classics', 'Romantic novel about manners, marriage, and society in Regency England.', 'Literature', 1813, 6, 4, 2, 'pride_prejudice.jpg', 1, 1, 'Classic'),
('9780393979503', 'BAR016', 'Brief History of Time', 'Stephen Hawking', 'W. W. Norton', 'Exploration of cosmology, black holes, and the nature of time.', 'Science', 1988, 4, 2, 2, 'brief_history.jpg', 1, 1, 'Bestseller'),
('9780385490818', 'BAR017', 'The Selfish Gene', 'Richard Dawkins', 'Oxford University Press', 'Revolutionary book about evolution from the gene\'s perspective.', 'Science', 1976, 3, 2, 1, 'selfish_gene.jpg', 0, 0, NULL),
('9780465026562', 'BAR018', 'Gödel Escher Bach', 'Douglas Hofstadter', 'Basic Books', 'Exploration of strange loops, consciousness, and artificial intelligence.', 'Science', 1979, 3, 2, 1, 'godel_escher_bach.jpg', 0, 1, 'Pulitzer Prize'),
('9780385472579', 'BAR019', 'Cosmos', 'Carl Sagan', 'Ballantine Books', 'Journey through space, time, and the origin of life in the universe.', 'Science', 1980, 5, 3, 2, 'cosmos.jpg', 1, 0, NULL),
('9780691169866', 'BAR020', 'Thinking Fast and Slow', 'Daniel Kahneman', 'Farrar Straus Giroux', 'Nobel Prize winner explores the two systems that drive the way we think.', 'Psychology', 2011, 4, 2, 2, 'thinking_fast_slow.jpg', 1, 1, 'Bestseller'),
('9780143127741', 'BAR021', 'The Lean Startup', 'Eric Ries', 'Crown Business', 'How constant innovation creates radically successful businesses.', 'Business', 2011, 5, 3, 2, 'lean_startup.jpg', 1, 0, NULL),
('9780062273208', 'BAR022', 'The Hard Thing About Hard Things', 'Ben Horowitz', 'Harper Business', 'Building a business when there are no easy answers.', 'Business', 2014, 4, 3, 1, 'hard_things.jpg', 1, 0, NULL),
('9781591846444', 'BAR023', 'Zero to One', 'Peter Thiel', 'Crown Business', 'Notes on startups and how to build the future.', 'Business', 2014, 4, 2, 2, 'zero_to_one.jpg', 1, 1, 'Bestseller'),
('9780062316097', 'BAR024', 'Sapiens', 'Yuval Noah Harari', 'Harper', 'Brief history of humankind from the Stone Age to the modern age.', 'History', 2015, 6, 3, 3, 'sapiens.jpg', 1, 1, 'Bestseller'),
('9780141034591', 'BAR025', 'Guns Germs and Steel', 'Jared Diamond', 'W. W. Norton', 'The fates of human societies and the roots of inequality.', 'History', 1997, 3, 2, 1, 'guns_germs_steel.jpg', 0, 1, 'Pulitzer Prize'),
('9780553380163', 'BAR026', 'A Brief History of Nearly Everything', 'Bill Bryson', 'Broadway Books', 'Understanding how we got from nothing to here.', 'Science', 2003, 4, 3, 1, 'brief_history_everything.jpg', 0, 0, NULL),
('9780307887894', 'BAR027', 'The Signal and the Noise', 'Nate Silver', 'Penguin Press', 'Why so many predictions fail but some don\'t.', 'Statistics', 2012, 3, 2, 1, 'signal_noise.jpg', 0, 0, NULL),
('9780593083857', 'BAR028', 'Atomic Habits', 'James Clear', 'Avery', 'Easy and proven way to build good habits and break bad ones.', 'Self-Help', 2018, 7, 4, 3, 'atomic_habits.jpg', 1, 1, 'Bestseller'),
('9780735211292', 'BAR029', 'Educated', 'Tara Westover', 'Random House', 'Memoir about growing up in a survivalist family and pursuing education.', 'Biography', 2018, 5, 3, 2, 'educated.jpg', 1, 0, NULL),
('9780316346627', 'BAR030', 'The Power of Habit', 'Charles Duhigg', 'Random House', 'Why we do what we do in life and business.', 'Psychology', 2012, 4, 2, 2, 'power_habit.jpg', 0, 0, NULL);

-- ====================================================================
-- DUMMY DATA: Transactions (25 transaction records)
-- FIXED: Removed duplicate 'fine' column, updated dates to 2025
-- ====================================================================
INSERT IGNORE INTO `transactions` (`tid`, `userId`, `isbn`, `borrowDate`, `returnDate`, `fineAmount`, `fineStatus`, `finePaymentDate`, `finePaymentMethod`) VALUES
('TXN001', 'USR001', '9780134685991', '2025-10-01', '2025-10-12', 0.00, 'paid', NULL, NULL),
('TXN002', 'USR002', '9780132350884', '2025-10-03', '2025-10-15', 0.00, 'paid', NULL, NULL),
('TXN003', 'USR003', '9780743273565', '2025-09-20', '2025-10-10', 15.00, 'paid', '2025-10-11', 'online'),
('TXN004', 'USR004', '9780451524935', '2025-10-05', '2025-10-18', 0.00, 'paid', NULL, NULL),
('TXN005', 'USR005', '9781593279509', '2025-10-08', '2025-10-20', 0.00, 'paid', NULL, NULL),
('TXN006', 'USR006', '9780134757599', '2025-09-15', '2025-10-05', 25.00, 'paid', '2025-10-06', 'card'),
('TXN007', 'USR007', '9780061120084', '2025-10-10', '2025-10-22', 0.00, 'paid', NULL, NULL),
('TXN008', 'USR008', '9780141439518', '2025-10-12', NULL, 0.00, 'pending', NULL, NULL),
('TXN009', 'FAC001', '9780393979503', '2025-09-25', '2025-10-15', 0.00, 'paid', NULL, NULL),
('TXN010', 'FAC002', '9781617294136', '2025-10-01', '2025-10-14', 0.00, 'paid', NULL, NULL),
('TXN011', 'USR001', '9780062273208', '2025-10-15', NULL, 0.00, 'pending', NULL, NULL),
('TXN012', 'USR002', '9780596517748', '2025-09-28', '2025-10-20', 10.00, 'paid', '2025-10-21', 'cash'),
('TXN013', 'USR003', '9780316769488', '2025-10-18', NULL, 0.00, 'pending', NULL, NULL),
('TXN014', 'USR004', '9780385490818', '2025-10-08', '2025-10-21', 0.00, 'paid', NULL, NULL),
('TXN015', 'USR005', '9780062316097', '2025-09-10', '2025-10-05', 35.00, 'paid', '2025-10-06', 'online'),
('TXN016', 'USR006', '9780134494166', '2025-10-20', NULL, 0.00, 'pending', NULL, NULL),
('TXN017', 'USR007', '9781449355739', '2025-10-12', NULL, 0.00, 'pending', NULL, NULL),
('TXN018', 'FAC003', '9780735619678', '2025-10-05', '2025-10-18', 0.00, 'paid', NULL, NULL),
('TXN019', 'FAC004', '9780465026562', '2025-10-10', '2025-10-23', 0.00, 'paid', NULL, NULL),
('TXN020', 'USR008', '9780691169866', '2025-09-18', '2025-10-12', 20.00, 'pending', NULL, NULL),
('TXN021', 'USR009', '9781591846444', '2025-10-22', NULL, 0.00, 'pending', NULL, NULL),
('TXN022', 'USR010', '9780593083857', '2025-10-16', NULL, 0.00, 'pending', NULL, NULL),
('TXN023', 'FAC005', '9780201633610', '2025-10-01', '2025-10-14', 0.00, 'paid', NULL, NULL),
('TXN024', 'USR001', '9780307887894', '2025-10-23', NULL, 0.00, 'pending', NULL, NULL),
('TXN025', 'USR002', '9780316346627', '2025-10-24', NULL, 0.00, 'pending', NULL, NULL);

-- ====================================================================
-- DUMMY DATA: Borrow Requests (15 requests with various statuses)
-- FIXED: Updated dates to 2025
-- ====================================================================
INSERT IGNORE INTO `borrow_requests` (`userId`, `isbn`, `requestDate`, `status`, `approvedBy`, `dueDate`, `rejectionReason`) VALUES
('USR003', '9780134685991', '2025-10-25 09:30:00', 'Pending', NULL, NULL, NULL),
('USR004', '9780132350884', '2025-10-25 10:15:00', 'Pending', NULL, NULL, NULL),
('USR005', '9780201633610', '2025-10-24 14:20:00', 'Approved', 'LIB001', '2025-11-07', NULL),
('USR006', '9781449355739', '2025-10-23 11:45:00', 'Approved', 'LIB002', '2025-11-06', NULL),
('USR007', '9780062273208', '2025-10-25 13:00:00', 'Pending', NULL, NULL, NULL),
('USR008', '9780385490818', '2025-10-22 15:30:00', 'Rejected', 'LIB001', NULL, 'All copies currently borrowed'),
('USR009', '9780593083857', '2025-10-25 08:45:00', 'Pending', NULL, NULL, NULL),
('USR010', '9780062316097', '2025-10-24 16:20:00', 'Approved', 'LIB003', '2025-11-07', NULL),
('FAC001', '9780134494166', '2025-10-23 09:00:00', 'Approved', 'LIB001', '2025-11-06', NULL),
('FAC002', '9780596517748', '2025-10-25 10:30:00', 'Pending', NULL, NULL, NULL),
('USR001', '9780141439518', '2025-10-21 14:15:00', 'Approved', 'LIB002', '2025-11-04', NULL),
('USR002', '9780743273565', '2025-10-25 11:00:00', 'Pending', NULL, NULL, NULL),
('FAC003', '9780465026562', '2025-10-20 13:45:00', 'Rejected', 'LIB003', NULL, 'User has pending fines'),
('USR003', '9781591846444', '2025-10-25 15:20:00', 'Pending', NULL, NULL, NULL),
('USR004', '9780691169866', '2025-10-24 09:30:00', 'Approved', 'LIB001', '2025-11-07', NULL);

-- ====================================================================
-- DUMMY DATA: Notifications (20 notifications)
-- FIXED: Updated dates to 2025
-- ====================================================================
INSERT IGNORE INTO `notifications` (`userId`, `title`, `message`, `type`, `priority`, `isRead`, `relatedId`, `createdAt`) VALUES
('USR001', 'Book Due Soon', 'Your borrowed book "The Signal and the Noise" is due in 2 days.', 'reminder', 'medium', 0, 'TXN024', '2025-10-24 09:00:00'),
('USR002', 'Book Due Soon', 'Your borrowed book "The Power of Habit" is due in 2 days.', 'reminder', 'medium', 0, 'TXN025', '2025-10-24 09:00:00'),
('USR003', 'Borrow Request Approved', 'Your request to borrow "Design Patterns" has been approved.', 'approval', 'high', 1, '3', '2025-10-24 14:30:00'),
('USR006', 'Borrow Request Approved', 'Your request to borrow "Learning Python" has been approved.', 'approval', 'high', 1, '4', '2025-10-23 12:00:00'),
('USR008', 'Borrow Request Rejected', 'Your request to borrow "Brief History of Time" was rejected: All copies currently borrowed', 'approval', 'high', 0, '6', '2025-10-22 16:00:00'),
('USR010', 'Borrow Request Approved', 'Your request to borrow "Sapiens" has been approved.', 'approval', 'high', 1, '8', '2025-10-24 16:45:00'),
('FAC001', 'Borrow Request Approved', 'Your request to borrow "Clean Architecture" has been approved.', 'approval', 'high', 1, '9', '2025-10-23 09:30:00'),
('USR001', 'Borrow Request Approved', 'Your request to borrow "Pride and Prejudice" has been approved.', 'approval', 'high', 1, '11', '2025-10-21 14:45:00'),
('FAC003', 'Borrow Request Rejected', 'Your request to borrow "Gödel Escher Bach" was rejected: User has pending fines', 'approval', 'high', 0, '13', '2025-10-20 14:15:00'),
('USR004', 'Borrow Request Approved', 'Your request to borrow "Thinking Fast and Slow" has been approved.', 'approval', 'high', 1, '15', '2025-10-24 10:00:00'),
('USR003', 'Overdue Book', 'Your book "The Great Gatsby" is overdue. Fine: $15.00', 'overdue', 'high', 1, 'TXN003', '2025-10-05 10:00:00'),
('USR006', 'Overdue Book', 'Your book "Refactoring" is overdue. Fine: $25.00', 'overdue', 'high', 1, 'TXN006', '2025-09-30 10:00:00'),
('USR005', 'Overdue Book', 'Your book "Sapiens" is overdue. Fine: $35.00', 'overdue', 'high', 1, 'TXN015', '2025-09-25 10:00:00'),
('USR008', 'Pending Fine', 'You have an outstanding fine of $20.00 for "Thinking Fast and Slow"', 'fine_paid', 'medium', 0, 'TXN020', '2025-10-13 11:00:00'),
('USR003', 'Fine Payment Received', 'Your fine payment of $15.00 has been received.', 'fine_paid', 'low', 1, 'TXN003', '2025-10-11 14:30:00'),
('USR006', 'Fine Payment Received', 'Your fine payment of $25.00 has been received.', 'fine_paid', 'low', 1, 'TXN006', '2025-10-06 15:45:00'),
('LIB001', 'Book Out of Stock', 'Book "Brief History of Time" is out of stock. All copies borrowed.', 'out_of_stock', 'medium', 0, '9780393979503', '2025-10-22 16:30:00'),
('LIB002', 'System Maintenance', 'Library system maintenance scheduled for Sunday, October 27, 2025 from 2:00 AM to 6:00 AM.', 'system', 'low', 1, NULL, '2025-10-20 08:00:00'),
('ADM001', 'New User Registration', 'New student user registered: james_thomas (USR009)', 'system', 'low', 1, 'USR009', '2025-10-20 09:15:00'),
(NULL, 'Library Announcement', 'New books added to Computer Science collection. Check out the latest titles!', 'system', 'medium', 0, NULL, '2025-10-15 10:00:00');

-- ====================================================================
-- DUMMY DATA: Book Reviews (15 reviews)
-- ====================================================================
INSERT IGNORE INTO `book_reviews` (`userId`, `isbn`, `rating`, `reviewText`, `isApproved`) VALUES
('USR001', '9780134685991', 5, 'Excellent book for Java developers! The best practices and patterns are clearly explained with great examples.', 1),
('USR002', '9780132350884', 5, 'Must-read for every programmer. Changed the way I write code.', 1),
('USR003', '9780743273565', 4, 'Beautiful prose and a captivating story about the American Dream. A true classic.', 1),
('USR004', '9780451524935', 5, 'Eerily relevant today. Orwell\'s vision is both terrifying and prophetic.', 1),
('FAC001', '9780393979503', 5, 'Hawking makes complex physics accessible to everyone. Brilliant!', 1),
('USR005', '9781593279509', 4, 'Great introduction to JavaScript with interesting projects and exercises.', 1),
('USR006', '9780134494166', 5, 'Uncle Bob does it again! Essential reading for software architects.', 1),
('FAC002', '9780465026562', 4, 'Fascinating exploration of consciousness and mathematics. Dense but rewarding.', 1),
('USR007', '9780062316097', 5, 'Mind-blowing perspective on human history. Harari is a master storyteller.', 1),
('USR008', '9780593083857', 5, 'Life-changing book! The habit formation framework actually works.', 1),
('FAC003', '9780201633610', 5, 'The definitive guide to design patterns. Every developer should read this.', 1),
('USR009', '9781449355739', 3, 'Good comprehensive guide but can be overwhelming for complete beginners.', 0),
('USR010', '9780691169866', 4, 'Insightful look into how we make decisions. Eye-opening research.', 1),
('FAC004', '9781617294136', 4, 'Practical patterns for microservices. Very helpful for real-world projects.', 1),
('USR001', '9780062273208', 5, 'Raw and honest advice about running a startup. No sugar-coating here!', 1);

-- ====================================================================
-- DUMMY DATA: Favorites (20 favorites)
-- ====================================================================
INSERT IGNORE INTO `favorites` (`userId`, `isbn`, `notes`) VALUES
('USR001', '9780134685991', 'Great Java reference'),
('USR001', '9780132350884', 'Must re-read annually'),
('USR002', '9780743273565', 'Favorite classic novel'),
('USR002', '9780593083857', 'Actually helped me build better habits'),
('USR003', '9780451524935', 'Reread every few years'),
('USR003', '9780062316097', 'Mind-blowing history'),
('USR004', '9780141439518', 'Relatable coming-of-age story'),
('USR005', '9781593279509', 'Best JS book I have read'),
('USR005', '9780134494166', NULL),
('USR006', '9780061120084', 'Powerful and moving'),
('USR006', '9780316346627', 'Great for self-improvement'),
('USR007', '9781449355739', 'Python bible'),
('USR008', '9780691169866', 'Changed how I think'),
('FAC001', '9780393979503', 'Amazing cosmology'),
('FAC001', '9780201633610', 'Design patterns reference'),
('FAC002', '9780465026562', 'Fascinating read'),
('FAC003', '9780735619678', 'Software construction guide'),
('FAC004', '9781617294136', 'Microservices reference'),
('USR009', '9781591846444', 'Startup inspiration'),
('USR010', '9780062273208', 'Business wisdom');

-- ====================================================================
-- DUMMY DATA: Book Reservations (10 reservations)
-- FIXED: Updated dates to 2025
-- ====================================================================
INSERT IGNORE INTO `book_reservations` (`userId`, `isbn`, `reservationStatus`, `notifiedDate`, `expiryDate`) VALUES
('USR003', '9780393979503', 'Active', NULL, '2025-11-02'),
('USR004', '9780134685991', 'Active', NULL, '2025-11-03'),
('USR005', '9780132350884', 'Notified', '2025-10-24', '2025-10-27'),
('USR006', '9780062316097', 'Active', NULL, '2025-11-01'),
('USR007', '9780061120084', 'Active', NULL, '2025-11-04'),
('USR008', '9780691169866', 'Expired', NULL, '2025-10-23'),
('USR009', '9780743273565', 'Active', NULL, '2025-11-05'),
('FAC001', '9780596517748', 'Notified', '2025-10-25', '2025-10-28'),
('FAC002', '9780201633610', 'Active', NULL, '2025-11-02'),
('USR010', '9781449355739', 'Active', NULL, '2025-11-03');

-- ====================================================================
-- DUMMY DATA: Audit Logs (20 audit entries)
-- FIXED: Updated dates to 2025
-- ====================================================================
INSERT IGNORE INTO `audit_logs` (`userId`, `action`, `entityType`, `entityId`, `changes`, `ipAddress`, `userAgent`) VALUES
('LIB001', 'APPROVE_BORROW', 'borrow_requests', '3', '{"status":"Approved","dueDate":"2025-11-07"}', '192.168.1.101', 'Mozilla/5.0'),
('LIB002', 'APPROVE_BORROW', 'borrow_requests', '4', '{"status":"Approved","dueDate":"2025-11-06"}', '192.168.1.102', 'Mozilla/5.0'),
('LIB001', 'REJECT_BORROW', 'borrow_requests', '6', '{"status":"Rejected","reason":"All copies currently borrowed"}', '192.168.1.101', 'Mozilla/5.0'),
('LIB003', 'APPROVE_BORROW', 'borrow_requests', '8', '{"status":"Approved","dueDate":"2025-11-07"}', '192.168.1.103', 'Mozilla/5.0'),
('LIB001', 'APPROVE_BORROW', 'borrow_requests', '9', '{"status":"Approved","dueDate":"2025-11-06"}', '192.168.1.101', 'Mozilla/5.0'),
('ADM001', 'UPDATE_SETTINGS', 'fine_settings', '1', '{"old_value":"5","new_value":"5"}', '192.168.1.201', 'Mozilla/5.0'),
('LIB001', 'CREATE_BOOK', 'books', '9780134685991', '{"bookName":"Effective Java","totalCopies":5}', '192.168.1.101', 'Mozilla/5.0'),
('LIB002', 'UPDATE_BOOK', 'books', '9780132350884', '{"available":{"old":3,"new":2}}', '192.168.1.102', 'Mozilla/5.0'),
('USR001', 'BORROW_BOOK', 'transactions', 'TXN001', '{"isbn":"9780134685991","borrowDate":"2025-10-01"}', '192.168.1.50', 'Mozilla/5.0'),
('USR003', 'PAY_FINE', 'transactions', 'TXN003', '{"fineAmount":15.00,"paymentMethod":"online"}', '192.168.1.52', 'Mozilla/5.0'),
('LIB001', 'PROCESS_RETURN', 'transactions', 'TXN001', '{"returnDate":"2025-10-12"}', '192.168.1.101', 'Mozilla/5.0'),
('ADM002', 'CREATE_USER', 'users', 'USR009', '{"userType":"Student","emailId":"james.thomas@university.edu"}', '192.168.1.202', 'Mozilla/5.0'),
('LIB002', 'APPROVE_BORROW', 'borrow_requests', '11', '{"status":"Approved","dueDate":"2025-11-04"}', '192.168.1.102', 'Mozilla/5.0'),
('LIB003', 'REJECT_BORROW', 'borrow_requests', '13', '{"status":"Rejected","reason":"User has pending fines"}', '192.168.1.103', 'Mozilla/5.0'),
('LIB001', 'APPROVE_BORROW', 'borrow_requests', '15', '{"status":"Approved","dueDate":"2025-11-07"}', '192.168.1.101', 'Mozilla/5.0'),
('ADM001', 'UPDATE_SETTINGS', 'system_settings', '4', '{"old_value":"5","new_value":"5"}', '192.168.1.201', 'Mozilla/5.0'),
('USR006', 'PAY_FINE', 'transactions', 'TXN006', '{"fineAmount":25.00,"paymentMethod":"card"}', '192.168.1.55', 'Mozilla/5.0'),
('LIB001', 'PROCESS_RETURN', 'transactions', 'TXN009', '{"returnDate":"2025-10-15"}', '192.168.1.101', 'Mozilla/5.0'),
('USR002', 'PAY_FINE', 'transactions', 'TXN012', '{"fineAmount":10.00,"paymentMethod":"cash"}', '192.168.1.51', 'Mozilla/5.0'),
('USR005', 'PAY_FINE', 'transactions', 'TXN015', '{"fineAmount":35.00,"paymentMethod":"online"}', '192.168.1.54', 'Mozilla/5.0');

-- ====================================================================
-- DUMMY DATA: API Logs (15 API call logs)
-- ====================================================================
INSERT IGNORE INTO `api_logs` (`userId`, `endpoint`, `method`, `statusCode`, `responseTime`, `ipAddress`, `userAgent`) VALUES
('USR001', '/api/books/search', 'GET', 200, 125, '192.168.1.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
('USR002', '/api/auth/login', 'POST', 200, 342, '192.168.1.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'),
('USR003', '/api/books/9780134685991', 'GET', 200, 89, '192.168.1.52', 'Mozilla/5.0 (X11; Linux x86_64)'),
('LIB001', '/api/borrow-requests/approve/3', 'POST', 200, 234, '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
('USR004', '/api/transactions/history', 'GET', 200, 156, '192.168.1.53', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)'),
('ADM001', '/api/admin/dashboard/stats', 'GET', 200, 423, '192.168.1.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
('USR005', '/api/books/favorites', 'GET', 200, 98, '192.168.1.54', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'),
('LIB002', '/api/books/update/9780132350884', 'PUT', 200, 187, '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
('USR006', '/api/fines/pay', 'POST', 200, 456, '192.168.1.55', 'Mozilla/5.0 (Android; Mobile)'),
('FAC001', '/api/books/trending', 'GET', 200, 134, '192.168.1.60', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'),
('USR007', '/api/notifications', 'GET', 200, 67, '192.168.1.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
('LIB003', '/api/borrow-requests/pending', 'GET', 200, 298, '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
('USR008', '/api/books/reserve', 'POST', 201, 176, '192.168.1.57', 'Mozilla/5.0 (iPad; CPU OS 15_0 like Mac OS X)'),
('ADM002', '/api/admin/users/list', 'GET', 200, 512, '192.168.1.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
('USR009', '/api/profile/update', 'PUT', 200, 223, '192.168.1.58', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)');

-- ====================================================================
-- DUMMY DATA: Books Borrowed (10 sample records)
-- ====================================================================
INSERT IGNORE INTO `books_borrowed` (`userId`, `isbn`, `borrowDate`, `dueDate`, `returnDate`, `status`, `notes`, `addedBy`) VALUES
('USR001', '9780134685991', '2025-10-15', '2025-10-29', NULL, 'Active', 'Regular borrowing', 'ADM001'),
('USR002', '9780132350884', '2025-10-18', '2025-11-01', NULL, 'Active', NULL, 'LIB001'),
('USR003', '9780743273565', '2025-10-01', '2025-10-15', '2025-10-14', 'Returned', 'Returned on time', 'LIB001'),
('USR004', '9780451524935', '2025-09-25', '2025-10-09', NULL, 'Overdue', 'Needs follow-up', 'ADM001'),
('USR005', '9781593279509', '2025-10-20', '2025-11-03', NULL, 'Active', NULL, 'LIB002'),
('USR006', '9780134757599', '2025-10-10', '2025-10-24', '2025-10-23', 'Returned', NULL, 'LIB001'),
('USR007', '9780061120084', '2025-10-22', '2025-11-05', NULL, 'Active', 'Faculty member', 'ADM001'),
('FAC001', '9780393979503', '2025-10-05', '2025-10-19', '2025-10-18', 'Returned', 'Extended borrowing period', 'LIB003'),
('FAC002', '9781617294136', '2025-10-12', '2025-10-26', NULL, 'Active', NULL, 'LIB002'),
('USR008', '9780141439518', '2025-10-25', '2025-11-08', NULL, 'Active', NULL, 'ADM001');

-- ====================================================================
-- FINE CALCULATION SYSTEM (Simplified - No stored procedures needed)
-- ====================================================================

-- Create a simple view to calculate fines on-the-fly
CREATE OR REPLACE VIEW transaction_fines AS
SELECT 
    t.tid,
    t.userId,
    t.isbn,
    t.borrowDate,
    t.returnDate,
    t.fineStatus,
    t.finePaymentDate,
    t.finePaymentMethod,
    b.bookName,
    u.emailId,
    u.userType,
    -- Calculate days overdue (safe from negative overflow)
    CASE 
        WHEN DATEDIFF(IFNULL(t.returnDate, CURDATE()), DATE_ADD(t.borrowDate, INTERVAL 14 DAY)) > 0
        THEN GREATEST(0, DATEDIFF(IFNULL(t.returnDate, CURDATE()), DATE_ADD(t.borrowDate, INTERVAL 14 DAY)) - 0)
        ELSE 0
    END AS daysOverdue,
    -- Calculate fine amount (safe from negative overflow)
    CASE 
        WHEN DATEDIFF(IFNULL(t.returnDate, CURDATE()), DATE_ADD(t.borrowDate, INTERVAL 14 DAY)) > 0
        THEN LEAST(500.00, GREATEST(0, DATEDIFF(IFNULL(t.returnDate, CURDATE()), DATE_ADD(t.borrowDate, INTERVAL 14 DAY)) - 0) * 5.00)
        ELSE 0.00
    END AS calculatedFine,
    t.fineAmount AS storedFine
FROM transactions t
JOIN books b ON t.isbn = b.isbn
JOIN users u ON t.userId = u.userId;

-- Update existing transaction fines based on calculation (safe version)
UPDATE transactions t
SET t.fineAmount = (
    CASE 
        WHEN DATEDIFF(IFNULL(t.returnDate, CURDATE()), DATE_ADD(t.borrowDate, INTERVAL 14 DAY)) > 0
        THEN LEAST(
            500.00,
            (DATEDIFF(IFNULL(t.returnDate, CURDATE()), DATE_ADD(t.borrowDate, INTERVAL 14 DAY)) - 0) * 5.00
        )
        ELSE 0.00
    END
)
WHERE (t.returnDate IS NULL OR t.fineStatus = 'pending')
  AND t.fineAmount != (
    CASE 
        WHEN DATEDIFF(IFNULL(t.returnDate, CURDATE()), DATE_ADD(t.borrowDate, INTERVAL 14 DAY)) > 0
        THEN LEAST(
            500.00,
            (DATEDIFF(IFNULL(t.returnDate, CURDATE()), DATE_ADD(t.borrowDate, INTERVAL 14 DAY)) - 0) * 5.00
        )
        ELSE 0.00
    END
  );

-- ====================================================================
-- End of fine calculation system
-- ====================================================================

-- Re-enable FK checks after all inserts
SET FOREIGN_KEY_CHECKS = 1;

-- ====================================================================
-- End of database schema and dummy data
-- ====================================================================
-- filepath: c:\xampp\htdocs\Integrated-Library-System\docker\mysql\library.sql
-- ...existing code...

-- ====================================================================
-- Table structure for table `saved_cards`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `saved_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `card_holder` varchar(100) NOT NULL,
  `card_number_masked` varchar(25) NOT NULL,
  `card_expiry` varchar(7) NOT NULL,
  `card_type` varchar(20) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  CONSTRAINT `saved_cards_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ...existing code...