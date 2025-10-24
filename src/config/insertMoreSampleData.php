<?php
include_once('config.php');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Inserting Enhanced Sample Data...</h2>";

// Insert Admin User
$adminUser = [
    "userId" => "ADMIN001",
    "username" => "admin",
    "password" => password_hash("admin123", PASSWORD_DEFAULT),
    "userType" => "Admin",
    "gender" => "Male",
    "dob" => "1990-01-01",
    "emailId" => "admin@library.com",
    "phoneNumber" => "1234567890",
    "address" => "Library Office, Main Campus",
    "isVerified" => 1
];

$checkUser = $conn->query("SELECT userId FROM users WHERE userId='ADMIN001'");
if ($checkUser->num_rows == 0) {
    $sql = "INSERT INTO users (userId, username, password, userType, gender, dob, emailId, phoneNumber, address, isVerified) 
            VALUES ('{$adminUser['userId']}', '{$adminUser['username']}', '{$adminUser['password']}', '{$adminUser['userType']}', 
            '{$adminUser['gender']}', '{$adminUser['dob']}', '{$adminUser['emailId']}', '{$adminUser['phoneNumber']}', 
            '{$adminUser['address']}', {$adminUser['isVerified']})";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Added admin user: admin (ADMIN001) - Password: admin123</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ Admin user already exists</p>";
}

// Insert Sample Students
$students = [
    ["userId" => "STU001", "username" => "john_doe", "email" => "john.doe@student.com", "phone" => "9876543210"],
    ["userId" => "STU002", "username" => "jane_smith", "email" => "jane.smith@student.com", "phone" => "9876543211"],
    ["userId" => "STU003", "username" => "bob_wilson", "email" => "bob.wilson@student.com", "phone" => "9876543212"],
    ["userId" => "STU004", "username" => "alice_brown", "email" => "alice.brown@student.com", "phone" => "9876543213"],
    ["userId" => "STU005", "username" => "charlie_davis", "email" => "charlie.davis@student.com", "phone" => "9876543214"],
];

foreach ($students as $student) {
    $checkUser = $conn->query("SELECT userId FROM users WHERE userId='{$student['userId']}'");
    if ($checkUser->num_rows == 0) {
        $password = password_hash("student123", PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (userId, username, password, userType, gender, dob, emailId, phoneNumber, address, isVerified) 
                VALUES ('{$student['userId']}', '{$student['username']}', '$password', 'Student', 'Male', '2000-01-01', 
                '{$student['email']}', '{$student['phone']}', '123 Student St', 1)";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Added student: {$student['username']} ({$student['userId']}) - Password: student123</p>";
        }
    }
}

// Insert Sample Books with Trending/Special flags
$books = [
    ["isbn" => "9780134685991", "bookName" => "Effective Java", "authorName" => "Joshua Bloch", "publisherName" => "Addison-Wesley", "available" => 8, "borrowed" => 2, "totalCopies" => 10, "isTrending" => 1, "isSpecial" => 0],
    ["isbn" => "9780735619678", "bookName" => "Code Complete", "authorName" => "Steve McConnell", "publisherName" => "Microsoft Press", "available" => 5, "borrowed" => 3, "totalCopies" => 8, "isTrending" => 1, "isSpecial" => 1, "specialBadge" => "Best Seller"],
    ["isbn" => "9780262033848", "bookName" => "Introduction to Algorithms", "authorName" => "Thomas H. Cormen", "publisherName" => "MIT Press", "available" => 6, "borrowed" => 1, "totalCopies" => 7, "isTrending" => 0, "isSpecial" => 1, "specialBadge" => "Classic"],
    ["isbn" => "9780545010221", "bookName" => "Harry Potter and the Deathly Hallows", "authorName" => "J.K. Rowling", "publisherName" => "Scholastic", "available" => 12, "borrowed" => 8, "totalCopies" => 20, "isTrending" => 1, "isSpecial" => 1, "specialBadge" => "Popular"],
    ["isbn" => "9780061120084", "bookName" => "To Kill a Mockingbird", "authorName" => "Harper Lee", "publisherName" => "HarperCollins", "available" => 4, "borrowed" => 2, "totalCopies" => 6, "isTrending" => 0, "isSpecial" => 1, "specialBadge" => "Classic"],
    ["isbn" => "9780316769488", "bookName" => "The Catcher in the Rye", "authorName" => "J.D. Salinger", "publisherName" => "Little Brown", "available" => 3, "borrowed" => 2, "totalCopies" => 5, "isTrending" => 0, "isSpecial" => 0],
    ["isbn" => "9780451524935", "bookName" => "1984", "authorName" => "George Orwell", "publisherName" => "Signet Classic", "available" => 7, "borrowed" => 3, "totalCopies" => 10, "isTrending" => 1, "isSpecial" => 1, "specialBadge" => "Must Read"],
    ["isbn" => "9780743273565", "bookName" => "The Great Gatsby", "authorName" => "F. Scott Fitzgerald", "publisherName" => "Scribner", "available" => 5, "borrowed" => 1, "totalCopies" => 6, "isTrending" => 0, "isSpecial" => 0],
    ["isbn" => "9780140283334", "bookName" => "Pride and Prejudice", "authorName" => "Jane Austen", "publisherName" => "Penguin Classics", "available" => 4, "borrowed" => 2, "totalCopies" => 6, "isTrending" => 0, "isSpecial" => 1, "specialBadge" => "Classic"],
    ["isbn" => "9780439023528", "bookName" => "The Hunger Games", "authorName" => "Suzanne Collins", "publisherName" => "Scholastic", "available" => 0, "borrowed" => 10, "totalCopies" => 10, "isTrending" => 1, "isSpecial" => 0],
];

foreach ($books as $book) {
    $checkBook = $conn->query("SELECT isbn FROM books WHERE isbn='{$book['isbn']}'");
    if ($checkBook->num_rows == 0) {
        $specialBadge = isset($book['specialBadge']) ? "'{$book['specialBadge']}'" : "NULL";
        $sql = "INSERT INTO books (isbn, bookName, authorName, publisherName, available, borrowed, totalCopies, isTrending, isSpecial, specialBadge, bookImage, description) 
                VALUES ('{$book['isbn']}', '{$book['bookName']}', '{$book['authorName']}', '{$book['publisherName']}', 
                {$book['available']}, {$book['borrowed']}, {$book['totalCopies']}, {$book['isTrending']}, {$book['isSpecial']}, 
                $specialBadge, '', 'A great book for learning and entertainment.')";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Added book: {$book['bookName']} ({$book['isbn']})</p>";
        }
    }
}

// Insert Sample Transactions
$transactions = [
    ["userId" => "STU001", "isbn" => "9780134685991", "borrowDate" => date('Y-m-d', strtotime('-5 days')), "dueDate" => date('Y-m-d', strtotime('+9 days'))],
    ["userId" => "STU002", "isbn" => "9780735619678", "borrowDate" => date('Y-m-d', strtotime('-10 days')), "dueDate" => date('Y-m-d', strtotime('+4 days'))],
    ["userId" => "STU003", "isbn" => "9780545010221", "borrowDate" => date('Y-m-d', strtotime('-20 days')), "dueDate" => date('Y-m-d', strtotime('-6 days')), "fineAmount" => 30.00],
    ["userId" => "STU004", "isbn" => "9780451524935", "borrowDate" => date('Y-m-d', strtotime('-3 days')), "dueDate" => date('Y-m-d', strtotime('+11 days'))],
];

foreach ($transactions as $trans) {
    $fineAmount = isset($trans['fineAmount']) ? $trans['fineAmount'] : 0;
    $fineStatus = $fineAmount > 0 ? 'pending' : 'pending';
    
    $sql = "INSERT INTO transactions (userId, isbn, borrowDate, dueDate, returnDate, fineAmount, fineStatus) 
            VALUES ('{$trans['userId']}', '{$trans['isbn']}', '{$trans['borrowDate']}', '{$trans['dueDate']}', NULL, $fineAmount, '$fineStatus')";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Added transaction: {$trans['userId']} borrowed {$trans['isbn']}</p>";
    }
}

// Insert Sample Borrow Requests
$requests = [
    ["userId" => "STU001", "isbn" => "9780262033848", "status" => "Pending"],
    ["userId" => "STU002", "isbn" => "9780316769488", "status" => "Pending"],
    ["userId" => "STU003", "isbn" => "9780743273565", "status" => "Approved", "approvedBy" => "ADMIN001", "dueDate" => date('Y-m-d', strtotime('+14 days'))],
    ["userId" => "STU004", "isbn" => "9780140283334", "status" => "Rejected", "approvedBy" => "ADMIN001"],
];

foreach ($requests as $req) {
    $dueDate = isset($req['dueDate']) ? "'{$req['dueDate']}'" : "NULL";
    $approvedBy = isset($req['approvedBy']) ? "'{$req['approvedBy']}'" : "NULL";
    
    $sql = "INSERT INTO borrow_requests (userId, isbn, requestDate, status, approvedBy, dueDate) 
            VALUES ('{$req['userId']}', '{$req['isbn']}', NOW(), '{$req['status']}', $approvedBy, $dueDate)";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Added borrow request: {$req['userId']} requested {$req['isbn']}</p>";
    }
}

// Insert Sample Notifications
$notifications = [
    ["userId" => "STU003", "title" => "Overdue Book Alert", "message" => "Your borrowed book 'Harry Potter and the Deathly Hallows' is overdue. Fine: ₹30.00", "type" => "overdue", "priority" => "high"],
    ["userId" => NULL, "title" => "Low Stock Alert", "message" => "Book 'The Hunger Games' is out of stock", "type" => "out_of_stock", "priority" => "medium"],
    ["userId" => "STU001", "title" => "Book Due Soon", "message" => "Your borrowed book 'Effective Java' is due in 3 days", "type" => "reminder", "priority" => "medium"],
    ["userId" => "STU004", "title" => "Request Rejected", "message" => "Your borrow request for 'Pride and Prejudice' has been rejected", "type" => "approval", "priority" => "low"],
];

foreach ($notifications as $notif) {
    $userId = $notif['userId'] ? "'{$notif['userId']}'" : "NULL";
    
    $sql = "INSERT INTO notifications (userId, title, message, type, priority, isRead, createdAt) 
            VALUES ($userId, '{$notif['title']}', '{$notif['message']}', '{$notif['type']}', '{$notif['priority']}', 0, NOW())";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Added notification: {$notif['title']}</p>";
    }
}

// Insert Fine Settings
$fineSettings = [
    ['fine_per_day', '5', 'Fine amount per day for overdue books'],
    ['max_borrow_days', '14', 'Maximum days a book can be borrowed'],
    ['grace_period_days', '0', 'Grace period before fines start'],
    ['max_fine_amount', '500', 'Maximum fine amount per book'],
    ['fine_calculation_method', 'daily', 'Method for calculating fines']
];

foreach ($fineSettings as $setting) {
    $checkSetting = $conn->query("SELECT id FROM fine_settings WHERE setting_name='{$setting[0]}'");
    if ($checkSetting->num_rows == 0) {
        $sql = "INSERT INTO fine_settings (setting_name, setting_value, description, updatedBy) 
                VALUES ('{$setting[0]}', '{$setting[1]}', '{$setting[2]}', 'ADMIN001')";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Added fine setting: {$setting[0]}</p>";
        }
    }
}

echo "<hr>";
echo "<h3>✅ Sample Data Inserted Successfully!</h3>";
echo "<h4>Login Credentials:</h4>";
echo "<ul>";
echo "<li><strong>Admin:</strong> Username: <code>admin</code>, Password: <code>admin123</code></li>";
echo "<li><strong>Students:</strong> Username: <code>john_doe, jane_smith, bob_wilson, alice_brown, charlie_davis</code>, Password: <code>student123</code></li>";
echo "</ul>";
echo "<p><a href='" . BASE_URL . "login' class='btn btn-primary'>Go to Login</a></p>";

$conn->close();
?>
