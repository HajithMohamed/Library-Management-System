-- University Library Management System Database Schema
-- This file is automatically executed when the Docker container starts
-- Version: 2.0 (Updated with Admin Dashboard Tables)
-- Date: 2025-10-26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
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
-- ====================================================================
CREATE TABLE IF NOT EXISTS `transactions` (
  `tid` varchar(255) NOT NULL,
  `userId` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `fine` decimal(10,2) DEFAULT 0.00,
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
  CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE
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
  `settingValue` text,
  `settingType` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text,
  `updatedBy` varchar(255),
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_key` (`settingKey`),
  KEY `idx_settingKey` (`settingKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `dashboard_stats`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `dashboard_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `statKey` varchar(100) NOT NULL,
  `statValue` int(11) NOT NULL DEFAULT 0,
  `statDate` date NOT NULL,
  `description` text,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_stat_date` (`statKey`,`statDate`),
  KEY `idx_statKey` (`statKey`),
  KEY `idx_statDate` (`statDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `user_login_logs`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `user_login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `loginTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `logoutTime` datetime,
  `ipAddress` varchar(45) DEFAULT NULL,
  `userAgent` text,
  `loginStatus` enum('success','failed','blocked') DEFAULT 'success',
  `failureReason` varchar(255),
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_loginTime` (`loginTime`),
  CONSTRAINT `user_login_logs_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE
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
-- Table structure for table `book_reservations`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `book_reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `reservationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiryDate` date NOT NULL,
  `status` enum('Active','Fulfilled','Expired','Cancelled') DEFAULT 'Active',
  `notificationSent` tinyint(1) DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_isbn` (`isbn`),
  KEY `idx_status` (`status`),
  KEY `idx_expiryDate` (`expiryDate`),
  CONSTRAINT `book_reservations_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `book_reservations_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `book_inventory`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `book_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(13) NOT NULL,
  `status` enum('Available','Lost','Damaged','Under Repair','Withdrawn') DEFAULT 'Available',
  `copies` int(11) NOT NULL DEFAULT 1,
  `lastVerifiedDate` date,
  `verifiedBy` varchar(255),
  `notes` text,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_isbn` (`isbn`),
  KEY `idx_status` (`status`),
  CONSTRAINT `book_inventory_ibfk_1` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `book_requests`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `book_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `bookName` varchar(255) NOT NULL,
  `authorName` varchar(255),
  `isbn` varchar(13),
  `category` varchar(100),
  `reason` text,
  `requestDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending','Approved','Rejected','Ordered','Received') DEFAULT 'Pending',
  `processedBy` varchar(255),
  `processedDate` datetime,
  `rejectionReason` text,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_status` (`status`),
  KEY `idx_requestDate` (`requestDate`),
  CONSTRAINT `book_requests_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `feedback`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `feedbackType` enum('complaint','suggestion','appreciation','bug_report') DEFAULT 'suggestion',
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `rating` int(1),
  `status` enum('New','Reviewed','In Progress','Resolved','Closed') DEFAULT 'New',
  `resolvedBy` varchar(255),
  `resolutionNotes` text,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_feedbackType` (`feedbackType`),
  KEY `idx_status` (`status`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `announcements`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `announcementType` enum('General','Maintenance','Event','Warning','Update') DEFAULT 'General',
  `targetAudience` enum('All','Students','Faculty','Librarians','Admin') DEFAULT 'All',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `startDate` datetime NOT NULL,
  `endDate` datetime,
  `isActive` tinyint(1) DEFAULT 1,
  `createdBy` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_startDate` (`startDate`),
  KEY `idx_announcementType` (`announcementType`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `users` (`userId`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `fine_payments`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `fine_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transactionId` varchar(255) NOT NULL,
  `userId` varchar(255) NOT NULL,
  `paymentAmount` decimal(10,2) NOT NULL,
  `paymentMethod` enum('cash','online','card','cheque') DEFAULT 'cash',
  `paymentStatus` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `transactionReference` varchar(255),
  `paymentGateway` varchar(100),
  `receiptNumber` varchar(100),
  `paymentDate` datetime,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_paymentStatus` (`paymentStatus`),
  KEY `idx_paymentDate` (`paymentDate`),
  CONSTRAINT `fine_payments_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `fine_payments_ibfk_2` FOREIGN KEY (`transactionId`) REFERENCES `transactions` (`tid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `library_hours`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `library_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dayOfWeek` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `openingTime` time NOT NULL,
  `closingTime` time NOT NULL,
  `isClosed` tinyint(1) DEFAULT 0,
  `notes` text,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_day` (`dayOfWeek`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `holiday_calendar`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `holiday_calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holidayName` varchar(255) NOT NULL,
  `holidayDate` date NOT NULL,
  `endDate` date,
  `description` text,
  `isRecurring` tinyint(1) DEFAULT 0,
  `createdBy` varchar(255),
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_holidayDate` (`holidayDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table structure for table `api_logs`
-- ====================================================================
CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `endpoint` varchar(255) NOT NULL,
  `method` enum('GET','POST','PUT','DELETE','PATCH') DEFAULT 'GET',
  `userId` varchar(255),
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
-- Insert default fine settings (only if table is empty)
-- ====================================================================
INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT * FROM (SELECT 'fine_per_day', '5', 'Fine amount per day for overdue books', 'system') AS tmp
WHERE NOT EXISTS (
    SELECT setting_name FROM `fine_settings` WHERE setting_name = 'fine_per_day'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT * FROM (SELECT 'max_borrow_days', '14', 'Maximum days a book can be borrowed', 'system') AS tmp
WHERE NOT EXISTS (
    SELECT setting_name FROM `fine_settings` WHERE setting_name = 'max_borrow_days'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT * FROM (SELECT 'grace_period_days', '0', 'Grace period before fines start', 'system') AS tmp
WHERE NOT EXISTS (
    SELECT setting_name FROM `fine_settings` WHERE setting_name = 'grace_period_days'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT * FROM (SELECT 'max_fine_amount', '500', 'Maximum fine amount per book', 'system') AS tmp
WHERE NOT EXISTS (
    SELECT setting_name FROM `fine_settings` WHERE setting_name = 'max_fine_amount'
);

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) 
SELECT * FROM (SELECT 'fine_calculation_method', 'daily', 'Method for calculating fines: daily or fixed', 'system') AS tmp
WHERE NOT EXISTS (
    SELECT setting_name FROM `fine_settings` WHERE setting_name = 'fine_calculation_method'
);

-- ====================================================================
-- Insert default system settings
-- ====================================================================
INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT * FROM (SELECT 'library_name', 'University Central Library', 'string', 'Name of the library', 'system') AS tmp
WHERE NOT EXISTS (SELECT settingKey FROM `system_settings` WHERE settingKey = 'library_name');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT * FROM (SELECT 'library_email', 'library@university.edu', 'string', 'Library contact email', 'system') AS tmp
WHERE NOT EXISTS (SELECT settingKey FROM `system_settings` WHERE settingKey = 'library_email');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT * FROM (SELECT 'library_phone', '+1-555-0100', 'string', 'Library contact phone', 'system') AS tmp
WHERE NOT EXISTS (SELECT settingKey FROM `system_settings` WHERE settingKey = 'library_phone');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT * FROM (SELECT 'max_books_per_user', '5', 'number', 'Maximum books a user can borrow', 'system') AS tmp
WHERE NOT EXISTS (SELECT settingKey FROM `system_settings` WHERE settingKey = 'max_books_per_user');

INSERT INTO `system_settings` (`settingKey`, `settingValue`, `settingType`, `description`, `updatedBy`) 
SELECT * FROM (SELECT 'enable_notifications', 'true', 'boolean', 'Enable system notifications', 'system') AS tmp
WHERE NOT EXISTS (SELECT settingKey FROM `system_settings` WHERE settingKey = 'enable_notifications');

-- ====================================================================
-- Insert default role permissions
-- ====================================================================
INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT * FROM (SELECT 'Student', 'view_catalog', 1, 0, 0, 0, 'Can view library catalog') AS tmp
WHERE NOT EXISTS (SELECT * FROM `role_permissions` WHERE role = 'Student' AND permission = 'view_catalog');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT * FROM (SELECT 'Student', 'borrow_book', 1, 1, 0, 0, 'Can borrow books') AS tmp
WHERE NOT EXISTS (SELECT * FROM `role_permissions` WHERE role = 'Student' AND permission = 'borrow_book');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT * FROM (SELECT 'Librarian', 'manage_books', 1, 1, 1, 0, 'Can manage books') AS tmp
WHERE NOT EXISTS (SELECT * FROM `role_permissions` WHERE role = 'Librarian' AND permission = 'manage_books');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT * FROM (SELECT 'Librarian', 'approve_requests', 1, 0, 0, 1, 'Can approve borrow requests') AS tmp
WHERE NOT EXISTS (SELECT * FROM `role_permissions` WHERE role = 'Librarian' AND permission = 'approve_requests');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT * FROM (SELECT 'Admin', 'manage_users', 1, 1, 1, 0, 'Can manage all users') AS tmp
WHERE NOT EXISTS (SELECT * FROM `role_permissions` WHERE role = 'Admin' AND permission = 'manage_users');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT * FROM (SELECT 'Admin', 'view_reports', 1, 0, 0, 0, 'Can view system reports') AS tmp
WHERE NOT EXISTS (SELECT * FROM `role_permissions` WHERE role = 'Admin' AND permission = 'view_reports');

INSERT INTO `role_permissions` (`role`, `permission`, `canRead`, `canWrite`, `canDelete`, `canApprove`, `description`) 
SELECT * FROM (SELECT 'Admin', 'system_settings', 1, 1, 0, 0, 'Can manage system settings') AS tmp
WHERE NOT EXISTS (SELECT * FROM `role_permissions` WHERE role = 'Admin' AND permission = 'system_settings');

-- ====================================================================
-- Insert default library hours
-- ====================================================================
INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT * FROM (SELECT 'Monday', '08:00:00', '20:00:00', 0) AS tmp
WHERE NOT EXISTS (SELECT * FROM `library_hours` WHERE dayOfWeek = 'Monday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT * FROM (SELECT 'Tuesday', '08:00:00', '20:00:00', 0) AS tmp
WHERE NOT EXISTS (SELECT * FROM `library_hours` WHERE dayOfWeek = 'Tuesday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT * FROM (SELECT 'Wednesday', '08:00:00', '20:00:00', 0) AS tmp
WHERE NOT EXISTS (SELECT * FROM `library_hours` WHERE dayOfWeek = 'Wednesday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT * FROM (SELECT 'Thursday', '08:00:00', '20:00:00', 0) AS tmp
WHERE NOT EXISTS (SELECT * FROM `library_hours` WHERE dayOfWeek = 'Thursday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT * FROM (SELECT 'Friday', '08:00:00', '18:00:00', 0) AS tmp
WHERE NOT EXISTS (SELECT * FROM `library_hours` WHERE dayOfWeek = 'Friday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT * FROM (SELECT 'Saturday', '10:00:00', '16:00:00', 0) AS tmp
WHERE NOT EXISTS (SELECT * FROM `library_hours` WHERE dayOfWeek = 'Saturday');

INSERT INTO `library_hours` (`dayOfWeek`, `openingTime`, `closingTime`, `isClosed`) 
SELECT * FROM (SELECT 'Sunday', '00:00:00', '00:00:00', 1) AS tmp
WHERE NOT EXISTS (SELECT * FROM `library_hours` WHERE dayOfWeek = 'Sunday');

COMMIT;
