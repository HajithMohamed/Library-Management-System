
DROP DATABASE IF EXISTS integrated_library_system;
CREATE DATABASE integrated_library_system;

USE integrated_library_system;

CREATE TABLE IF NOT EXISTS users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(10) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255),
    userType VARCHAR(25) DEFAULT 'Student',
    gender VARCHAR(6),
    dob DATE,
    emailId VARCHAR(255) UNIQUE,
    phoneNumber VARCHAR(15),
    address TEXT,
    isVerified TINYINT(1) DEFAULT 0,
    otp VARCHAR(10),
    otpExpiry DATETIME,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_userId (userId),
    INDEX idx_username (username),
    INDEX idx_emailId (emailId),
    INDEX idx_userType (userType),
    INDEX idx_createdAt (createdAt)
);

CREATE TABLE IF NOT EXISTS books(
    isbn VARCHAR(13) PRIMARY KEY,
    bookName VARCHAR(255),
    authorName VARCHAR(255),
    publisherName VARCHAR(255),
    available INT,
    borrowed INT
);

CREATE TABLE IF NOT EXISTS transactions(
    tid VARCHAR(25) PRIMARY KEY,
    userId VARCHAR(255),
    isbn VARCHAR(13),
    fine INT,
    borrowDate VARCHAR(10),
    returnDate VARCHAR(10),
    lastFinePaymentDate VARCHAR(10),
    FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE
);
