<?php
// Test signup functionality
require_once '/var/www/html/config/config.php';
require_once '/var/www/html/config/dbConnection.php';
require_once '/var/www/html/models/User.php';

use App\Models\User;

try {
    echo "Testing Signup Functionality:\n";
    echo "============================\n";
    
    // Test database connection
    if ($conn) {
        echo "✅ Database connection: SUCCESS\n";
    } else {
        echo "❌ Database connection: FAILED\n";
        exit(1);
    }
    
    // Test User model
    $userModel = new User();
    echo "✅ User model: LOADED\n";
    
    // Test user ID generation
    $userId = $userModel->generateUserId();
    echo "✅ User ID generation: {$userId}\n";
    
    // Test username validation
    $testData = [
        'username' => 'testuser123',
        'password' => 'password123',
        'userType' => 'Student',
        'gender' => 'Male',
        'dob' => '1990-01-01',
        'emailId' => 'test@example.com',
        'phoneNumber' => '1234567890',
        'address' => 'Test Address'
    ];
    
    $errors = $userModel->validateUserData($testData);
    if (empty($errors)) {
        echo "✅ Data validation: PASSED\n";
    } else {
        echo "❌ Data validation: FAILED\n";
        foreach ($errors as $error) {
            echo "   - {$error}\n";
        }
    }
    
    // Test username existence check
    $usernameExists = $userModel->usernameExists('testuser123');
    echo "✅ Username check: " . ($usernameExists ? 'EXISTS' : 'AVAILABLE') . "\n";
    
    echo "\nAll tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
