-- ============================================
-- Library Management System Database
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================
-- Database Creation
-- ============================================

DROP DATABASE IF EXISTS `integrated_library_system`;
CREATE DATABASE `integrated_library_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `integrated_library_system`;

-- ============================================
-- Table: users
-- ============================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(10) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `userType` varchar(25) DEFAULT 'Student',
  `gender` varchar(6) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `emailId` varchar(255) DEFAULT NULL,
  `phoneNumber` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `isVerified` tinyint(1) DEFAULT 0,
  `otp` varchar(10) DEFAULT NULL,
  `otpExpiry` datetime DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `emailId` (`emailId`),
  KEY `idx_userId` (`userId`),
  KEY `idx_username` (`username`),
  KEY `idx_emailId` (`emailId`),
  KEY `idx_userType` (`userType`),
  KEY `idx_createdAt` (`createdAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: books
-- ============================================

CREATE TABLE IF NOT EXISTS `books` (
  `isbn` varchar(20) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `bookName` varchar(255) NOT NULL,
  `authorName` varchar(255) NOT NULL,
  `publisherName` varchar(255) NOT NULL,
  `available` int(11) NOT NULL DEFAULT 0,
  `borrowed` int(11) NOT NULL DEFAULT 0,
  `totalCopies` int(11) NOT NULL DEFAULT 0,
  `borrowCount` int(11) NOT NULL DEFAULT 0 COMMENT 'Total times this book has been borrowed',
  `bookImage` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `publicationYear` int(4) DEFAULT NULL,
  `isTrending` tinyint(1) DEFAULT 0,
  `isSpecial` tinyint(1) DEFAULT 0,
  `specialBadge` varchar(50) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: transactions
-- ============================================

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(10) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `borrowDate` date NOT NULL,
  `dueDate` date NOT NULL,
  `returnDate` date DEFAULT NULL,
  `fineAmount` decimal(10,2) DEFAULT 0.00,
  `fineStatus` enum('pending','paid','waived') DEFAULT 'pending',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `isbn` (`isbn`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: borrow_requests
-- ============================================

CREATE TABLE IF NOT EXISTS `borrow_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(10) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `requestDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approvedBy` varchar(10) DEFAULT NULL,
  `dueDate` date DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `isbn` (`isbn`),
  KEY `approvedBy` (`approvedBy`),
  CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE,
  CONSTRAINT `borrow_requests_ibfk_3` FOREIGN KEY (`approvedBy`) REFERENCES `users` (`userId`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: notifications
-- ============================================

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(10) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('overdue','out_of_stock','reminder','approval','general') NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `isRead` tinyint(1) DEFAULT 0,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: fine_settings
-- ============================================

CREATE TABLE IF NOT EXISTS `fine_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `updatedBy` varchar(10) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_name` (`setting_name`),
  KEY `updatedBy` (`updatedBy`),
  CONSTRAINT `fine_settings_ibfk_1` FOREIGN KEY (`updatedBy`) REFERENCES `users` (`userId`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: analytics_stats (for performance optimization)
-- ============================================

CREATE TABLE IF NOT EXISTS `analytics_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stat_month` int(2) NOT NULL COMMENT 'Month (1-12)',
  `stat_year` int(4) NOT NULL COMMENT 'Year (YYYY)',
  `total_borrowings` int(11) DEFAULT 0,
  `total_returns` int(11) DEFAULT 0,
  `total_fines_collected` decimal(10,2) DEFAULT 0.00,
  `new_members` int(11) DEFAULT 0,
  `overdue_count` int(11) DEFAULT 0,
  `active_members` int(11) DEFAULT 0,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `month_year` (`stat_month`, `stat_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Sample Data: Admin User
-- ============================================

INSERT INTO `users` (`userId`, `username`, `password`, `userType`, `gender`, `dob`, `emailId`, `phoneNumber`, `address`, `isVerified`) VALUES
('ADMIN001', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Male', '1990-01-01', 'admin@library.com', '1234567890', 'Library Office, Main Campus', 1);

-- ============================================
-- Insert Sample Data: Students
-- ============================================

INSERT INTO `users` (`userId`, `username`, `password`, `userType`, `gender`, `dob`, `emailId`, `phoneNumber`, `address`, `isVerified`) VALUES
('STU001', 'john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Male', '2000-01-01', 'john.doe@student.com', '9876543210', '123 Student St', 1),
('STU002', 'jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Male', '2000-01-01', 'jane.smith@student.com', '9876543211', '123 Student St', 1),
('STU003', 'bob_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Male', '2000-01-01', 'bob.wilson@student.com', '9876543212', '123 Student St', 1),
('STU004', 'alice_brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Male', '2000-01-01', 'alice.brown@student.com', '9876543213', '123 Student St', 1),
('STU005', 'charlie_davis', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Male', '2000-01-01', 'charlie.davis@student.com', '9876543214', '123 Student St', 1);

-- ============================================
-- Insert Sample Data: Books
-- ============================================

INSERT INTO `books` (`isbn`, `barcode`, `bookName`, `authorName`, `publisherName`, `available`, `borrowed`, `totalCopies`, `borrowCount`, `bookImage`, `description`, `category`, `publicationYear`, `isTrending`, `isSpecial`, `specialBadge`) VALUES
('9780134685991', 'BC9780134685991', 'Effective Java', 'Joshua Bloch', 'Addison-Wesley', 8, 2, 10, 156, '', 'A great book for learning and entertainment.', 'Technology', 2018, 1, 0, NULL),
('9780735619678', 'BC9780735619678', 'Code Complete', 'Steve McConnell', 'Microsoft Press', 5, 3, 8, 89, '', 'A great book for learning and entertainment.', 'Technology', 2004, 1, 1, 'Best Seller'),
('9780262033848', 'BC9780262033848', 'Introduction to Algorithms', 'Thomas H. Cormen', 'MIT Press', 6, 1, 7, 67, '', 'A great book for learning and entertainment.', 'Science', 2009, 0, 1, 'Classic'),
('9780545010221', 'BC9780545010221', 'Harry Potter and the Deathly Hallows', 'J.K. Rowling', 'Scholastic', 12, 8, 20, 112, '', 'A great book for learning and entertainment.', 'Fiction', 2007, 1, 1, 'Popular'),
('9780061120084', 'BC9780061120084', 'To Kill a Mockingbird', 'Harper Lee', 'HarperCollins', 4, 2, 6, 142, '', 'A great book for learning and entertainment.', 'Fiction', 1960, 0, 1, 'Classic'),
('9780316769488', 'BC9780316769488', 'The Catcher in the Rye', 'J.D. Salinger', 'Little Brown', 3, 2, 5, 118, '', 'A great book for learning and entertainment.', 'Fiction', 1951, 0, 0, NULL),
('9780451524935', 'BC9780451524935', '1984', 'George Orwell', 'Signet Classic', 7, 3, 10, 138, '', 'A great book for learning and entertainment.', 'Fiction', 1949, 1, 1, 'Must Read'),
('9780743273565', 'BC9780743273565', 'The Great Gatsby', 'F. Scott Fitzgerald', 'Scribner', 5, 1, 6, 156, '', 'A great book for learning and entertainment.', 'Fiction', 1925, 0, 0, NULL),
('9780140283334', 'BC9780140283334', 'Pride and Prejudice', 'Jane Austen', 'Penguin Classics', 4, 2, 6, 124, '', 'A great book for learning and entertainment.', 'Fiction', 1813, 0, 1, 'Classic'),
('9780439023528', 'BC9780439023528', 'The Hunger Games', 'Suzanne Collins', 'Scholastic', 0, 10, 10, 92, '', 'A great book for learning and entertainment.', 'Fiction', 2008, 1, 0, NULL);

-- ============================================
-- Insert Sample Data: Transactions
-- ============================================

INSERT INTO `transactions` (`userId`, `isbn`, `borrowDate`, `dueDate`, `returnDate`, `fineAmount`, `fineStatus`) VALUES
('STU001', '9780134685991', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), NULL, 0.00, 'pending'),
('STU002', '9780735619678', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY), NULL, 0.00, 'pending'),
('STU003', '9780545010221', DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY), NULL, 30.00, 'pending'),
('STU004', '9780451524935', DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), NULL, 0.00, 'pending');

-- ============================================
-- Insert Sample Data: Borrow Requests
-- ============================================

INSERT INTO `borrow_requests` (`userId`, `isbn`, `requestDate`, `status`, `approvedBy`, `dueDate`) VALUES
('STU001', '9780262033848', NOW(), 'Pending', NULL, NULL),
('STU002', '9780316769488', NOW(), 'Pending', NULL, NULL),
('STU003', '9780743273565', NOW(), 'Approved', 'ADMIN001', DATE_ADD(CURDATE(), INTERVAL 14 DAY)),
('STU004', '9780140283334', NOW(), 'Rejected', 'ADMIN001', NULL);

-- ============================================
-- Insert Sample Data: Notifications
-- ============================================

INSERT INTO `notifications` (`userId`, `title`, `message`, `type`, `priority`, `isRead`) VALUES
('STU003', 'Overdue Book Alert', 'Your borrowed book \'Harry Potter and the Deathly Hallows\' is overdue. Fine: ₹30.00', 'overdue', 'high', 0),
(NULL, 'Low Stock Alert', 'Book \'The Hunger Games\' is out of stock', 'out_of_stock', 'medium', 0),
('STU001', 'Book Due Soon', 'Your borrowed book \'Effective Java\' is due in 3 days', 'reminder', 'medium', 0),
('STU004', 'Request Rejected', 'Your borrow request for \'Pride and Prejudice\' has been rejected', 'approval', 'low', 0);

-- ============================================
-- Insert Sample Data: Fine Settings
-- ============================================

INSERT INTO `fine_settings` (`setting_name`, `setting_value`, `description`, `updatedBy`) VALUES
('fine_per_day', '5', 'Fine amount per day for overdue books', 'ADMIN001'),
('max_borrow_days', '14', 'Maximum days a book can be borrowed', 'ADMIN001'),
('grace_period_days', '0', 'Grace period before fines start', 'ADMIN001'),
('max_fine_amount', '500', 'Maximum fine amount per book', 'ADMIN001'),
('fine_calculation_method', 'daily', 'Method for calculating fines', 'ADMIN001');

-- ============================================
-- Insert Sample Data: Analytics Stats
-- ============================================

INSERT INTO `analytics_stats` (`stat_month`, `stat_year`, `total_borrowings`, `total_returns`, `total_fines_collected`, `new_members`, `overdue_count`, `active_members`) VALUES
(1, 2024, 245, 230, 3250.00, 45, 15, 1180),
(2, 2024, 289, 275, 3840.00, 58, 18, 1238),
(3, 2024, 312, 298, 4125.00, 62, 20, 1300),
(4, 2024, 278, 265, 3675.00, 71, 16, 1371),
(5, 2024, 324, 310, 4380.00, 85, 22, 1456),
(6, 2024, 356, 340, 4720.00, 92, 19, 1548),
(7, 2024, 389, 375, 5145.00, 108, 24, 1656),
(8, 2024, 412, 398, 5580.00, 115, 21, 1771),
(9, 2024, 398, 385, 5230.00, 128, 18, 1899),
(10, 2024, 445, 430, 6015.00, 142, 26, 2041),
(11, 2024, 468, 455, 6345.00, 156, 23, 2197),
(12, 2024, 492, 478, 6780.00, 165, 20, 2362),
(1, 2023, 198, 185, 2680.00, 38, 12, 985),
(2, 2023, 234, 220, 3120.00, 42, 14, 1027),
(3, 2023, 267, 255, 3540.00, 51, 16, 1078),
(4, 2023, 245, 235, 3280.00, 48, 13, 1126),
(5, 2023, 289, 275, 3890.00, 59, 18, 1185),
(6, 2023, 312, 298, 4180.00, 65, 17, 1250);

-- ============================================
-- Commit Transaction
-- ============================================

COMMIT;

-- ============================================
-- Login Credentials
-- ============================================
-- Admin: username = admin, password = admin123
-- Students: username = john_doe/jane_smith/bob_wilson/alice_brown/charlie_davis, password = student123
-- Note: All passwords are hashed using PASSWORD_DEFAULT (bcrypt)
-- ============================================
