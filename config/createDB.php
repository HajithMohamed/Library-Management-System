<?php
include_once('config.php');
$conn=mysqli_connect( DB_HOST, DB_USER , DB_PASSWORD , "", DB_PORT );//Create Connection
if($conn->connect_error)//Check Connection
{
     die("Connection failed: ".$conn->connect_error);
}
$sql="CREATE DATABASE IF NOT EXISTS ".DB_NAME; // Creating the database if not exists

if($conn->query($sql)===TRUE)
{
     include_once('dbConnection.php');

    // Creating tables inside the database if not exists
    $sql1="CREATE TABLE IF NOT EXISTS users( userId VARCHAR(255) PRIMARY KEY, password VARCHAR(255), 
    userType VARCHAR(25), gender VARCHAR(6), dob VARCHAR(10), emailId VARCHAR(255), phoneNumber VARCHAR(10), 
    address VARCHAR(255), isVerified TINYINT(1) DEFAULT 0, otp VARCHAR(10), otpExpiry VARCHAR(20))";
     
     $sql2="CREATE TABLE IF NOT EXISTS books( isbn VARCHAR(13) PRIMARY KEY, bookName VARCHAR(255), 
     authorName VARCHAR(255), publisherName VARCHAR(255), available int, borrowed int)";
      
     $sql3="CREATE TABLE IF NOT EXISTS transactions( tid VARCHAR(25) PRIMARY KEY, userId VARCHAR(255), 
     isbn VARCHAR(13), fine int, borrowDate VARCHAR(10), returnDate VARCHAR(10), lastFinePaymentDate VARCHAR(10), FOREIGN KEY(userId) 
     REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE, FOREIGN KEY(isbn) REFERENCES books(isbn) 
     ON UPDATE CASCADE ON DELETE CASCADE)";

    // Ensure new columns exist on older deployments
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS isVerified TINYINT(1) DEFAULT 0");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS otp VARCHAR(10)");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS otpExpiry VARCHAR(20)");

    if($conn->query($sql1)===FALSE || $conn->query($sql2)===FALSE || $conn->query($sql3)===FALSE)
     {
          echo "Error creating table: ".$conn->error;
     }
}
else
{
     echo "Error: ".$sql."<br>".$conn->error;
}
$conn->close();
?>