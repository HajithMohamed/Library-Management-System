<?php
include_once('config.php');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Inserting Sample Data...</h2>";

// Insert sample users
$sampleUsers = [
    ["userId" => "STU001", "password" => password_hash("password123", PASSWORD_DEFAULT), "userType" => "Student", "name" => "John Doe", "gender" => "Male", "dob" => "2000-05-15", "emailId" => "john.doe@example.com", "phoneNumber" => "1234567890", "address" => "123 Main St, City", "isVerified" => 1],
    ["userId" => "STU002", "password" => password_hash("password123", PASSWORD_DEFAULT), "userType" => "Student", "name" => "Jane Smith", "gender" => "Female", "dob" => "2001-08-22", "emailId" => "jane.smith@example.com", "phoneNumber" => "0987654321", "address" => "456 Oak Ave, Town", "isVerified" => 1],
    ["userId" => "FAC001", "password" => password_hash("password123", PASSWORD_DEFAULT), "userType" => "Faculty", "name" => "Dr. Robert Brown", "gender" => "Male", "dob" => "1975-03-10", "emailId" => "robert.brown@example.com", "phoneNumber" => "5551234567", "address" => "789 Elm Rd, Village", "isVerified" => 1],
    ["userId" => "LIB001", "password" => password_hash("librarian123", PASSWORD_DEFAULT), "userType" => "Librarian", "name" => "Sarah Wilson", "gender" => "Female", "dob" => "1985-11-30", "emailId" => "sarah.wilson@example.com", "phoneNumber" => "5559876543", "address" => "321 Pine St, City", "isVerified" => 1]
];

foreach ($sampleUsers as $user) {
    $checkUser = $conn->query("SELECT userId FROM users WHERE userId='{$user['userId']}'");
    if ($checkUser->num_rows == 0) {
        $sql = "INSERT INTO users (userId, password, userType, name, gender, dob, emailId, phoneNumber, address, isVerified) VALUES ('{$user['userId']}', '{$user['password']}', '{$user['userType']}', '{$user['name']}', '{$user['gender']}', '{$user['dob']}', '{$user['emailId']}', '{$user['phoneNumber']}', '{$user['address']}', {$user['isVerified']})";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Added user: {$user['name']} ({$user['userId']})</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding user {$user['userId']}: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ User {$user['userId']} already exists</p>";
    }
}

// Insert sample books
$sampleBooks = [
    ["isbn" => "9780134685991", "bookName" => "Effective Java", "authorName" => "Joshua Bloch", "publisherName" => "Addison-Wesley", "available" => 5, "borrowed" => 2, "category" => "Technology", "publicationYear" => 2018, "description" => "A comprehensive guide to Java programming best practices."],
    ["isbn" => "9780735619678", "bookName" => "Code Complete", "authorName" => "Steve McConnell", "publisherName" => "Microsoft Press", "available" => 3, "borrowed" => 1, "category" => "Technology", "publicationYear" => 2004, "description" => "A practical handbook of software construction."],
    ["isbn" => "9780262033848", "bookName" => "Introduction to Algorithms", "authorName" => "Thomas H. Cormen", "publisherName" => "MIT Press", "available" => 4, "borrowed" => 0, "category" => "Science", "publicationYear" => 2009, "description" => "Comprehensive introduction to the modern study of computer algorithms."],
    ["isbn" => "9780545010221", "bookName" => "Harry Potter and the Deathly Hallows", "authorName" => "J.K. Rowling", "publisherName" => "Scholastic", "available" => 10, "borrowed" => 5, "category" => "Fiction", "publicationYear" => 2007, "description" => "The final book in the Harry Potter series."],
    ["isbn" => "9780061120084", "bookName" => "To Kill a Mockingbird", "authorName" => "Harper Lee", "publisherName" => "HarperCollins", "available" => 6, "borrowed" => 2, "category" => "Fiction", "publicationYear" => 1960, "description" => "A classic of modern American literature."]
];

foreach ($sampleBooks as $book) {
    $checkBook = $conn->query("SELECT isbn FROM books WHERE isbn='{$book['isbn']}'");
    if ($checkBook->num_rows == 0) {
        $totalCopies = $book['available'] + $book['borrowed'];
        $sql = "INSERT INTO books (isbn, bookName, authorName, publisherName, available, borrowed, bookImage, description, category, publicationYear, totalCopies) VALUES ('{$book['isbn']}', '{$book['bookName']}', '{$book['authorName']}', '{$book['publisherName']}', {$book['available']}, {$book['borrowed']}, '', '{$book['description']}', '{$book['category']}', {$book['publicationYear']}, $totalCopies)";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Added book: {$book['bookName']} ({$book['isbn']})</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding book {$book['isbn']}: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Book {$book['isbn']} already exists</p>";
    }
}

// Insert sample borrow requests
$sampleRequests = [
    ["isbn" => "9780134685991", "userId" => "STU001"],
    ["isbn" => "9780735619678", "userId" => "STU002"],
    ["isbn" => "9780545010221", "userId" => "FAC001"],
    ["isbn" => "9780262033848", "userId" => "STU001"]
];

foreach ($sampleRequests as $request) {
    $checkRequest = $conn->query("SELECT id FROM borrow_requests WHERE isbn='{$request['isbn']}' AND userId='{$request['userId']}' AND status='Pending'");
    if ($checkRequest->num_rows == 0) {
        $sql = "INSERT INTO borrow_requests (isbn, userId, requestDate, status) VALUES ('{$request['isbn']}', '{$request['userId']}', NOW(), 'Pending')";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Added borrow request: {$request['userId']} requested {$request['isbn']}</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding borrow request: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Borrow request for {$request['isbn']} by {$request['userId']} already exists</p>";
    }
}

echo "<h3>Sample data insertion completed!</h3>";
echo "<p><strong>Sample Login Credentials:</strong></p>";
echo "<ul>";
echo "<li>Librarian - Username: LIB001, Password: librarian123</li>";
echo "<li>Student 1 - Username: STU001, Password: password123</li>";
echo "<li>Student 2 - Username: STU002, Password: password123</li>";
echo "<li>Faculty - Username: FAC001, Password: password123</li>";
echo "</ul>";
echo "<p><a href='../src/admin/adminDashboard.php'>Go to Admin Dashboard</a></p>";
echo "<p><a href='../index.php'>Go to Login Page</a></p>";

$conn->close();
?>
