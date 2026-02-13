<?php
// Migration script to create book_recommendations table

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/dbConnection.php';

$conn = $GLOBALS['conn'];


$sql = 'CREATE TABLE IF NOT EXISTS book_recommendations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NOT NULL,
  isbn VARCHAR(20),
  publisher VARCHAR(255),
  edition VARCHAR(50),
  year INT,
  subject_category VARCHAR(100),
  justification TEXT NOT NULL,
  estimated_price DECIMAL(10,2),
  recommended_by VARCHAR(255) NOT NULL,
  status ENUM("pending", "approved", "rejected", "ordered", "received") DEFAULT "pending",
  admin_notes TEXT,
  rejection_reason TEXT,
  reviewed_by VARCHAR(255),
  review_date DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (recommended_by) REFERENCES users(userId) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_by) REFERENCES users(userId) ON DELETE SET NULL
);';

if ($conn->query($sql) === TRUE) {
    echo "book_recommendations table created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
