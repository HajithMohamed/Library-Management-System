
DROP DATABASE IF EXISTS library_db;
CREATE DATABASE  library_db;

USE library_db;

CREATE TABLE IF NOT EXISTS users(
    userId VARCHAR(255) PRIMARY KEY, 
    password VARCHAR(255), 
    userType VARCHAR(25), 
    gender VARCHAR(6), 
    dob VARCHAR(10), 
    emailId VARCHAR(255), 
    phoneNumber VARCHAR(10), 
    address VARCHAR(255), 
    isVerified TINYINT(1) DEFAULT 0, 
    otp VARCHAR(10), 
    otpExpiry VARCHAR(20)
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
