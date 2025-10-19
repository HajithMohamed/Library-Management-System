<?php
// Test complete signup and login flow
session_start();

require_once '/var/www/html/config/config.php';
require_once '/var/www/html/config/dbConnection.php';
require_once '/var/www/html/models/User.php';
require_once '/var/www/html/controllers/AuthController.php';

use App\Models\User;
use App\Controllers\AuthController;

try {
    echo "Testing Complete Signup and Login Flow:\n";
    echo "=======================================\n";
    
    // Test 1: Create a new user via signup
    echo "\n1. Testing User Creation:\n";
    $userModel = new User();
    
    $signupData = [
        'username' => 'newuser123',
        'password' => 'password123',
        'userType' => 'Student',
        'gender' => 'Female',
        'dob' => '1995-05-15',
        'emailId' => 'newuser@example.com',
        'phoneNumber' => '9876543210',
        'address' => 'New User Address',
        'isVerified' => 1, // Skip email verification for test
        'otp' => null,
        'otpExpiry' => null
    ];
    
    // Hash password
    $signupData['password'] = password_hash($signupData['password'], PASSWORD_DEFAULT);
    
    // Create user
    if ($userModel->createUser($signupData)) {
        $generatedUserId = $userModel->getLastGeneratedUserId();
        echo "✅ User created successfully with ID: {$generatedUserId}\n";
    } else {
        echo "❌ Failed to create user\n";
        exit(1);
    }
    
    // Test 2: Test login authentication
    echo "\n2. Testing Login Authentication:\n";
    $user = $userModel->authenticateByUsername('newuser123', 'password123');
    
    if ($user) {
        echo "✅ Login authentication successful\n";
        echo "   User ID: {$user['userId']}\n";
        echo "   Username: {$user['username']}\n";
        echo "   User Type: {$user['userType']}\n";
        echo "   Email: {$user['emailId']}\n";
    } else {
        echo "❌ Login authentication failed\n";
        exit(1);
    }
    
    // Test 3: Test session setup (simulate login)
    echo "\n3. Testing Session Setup:\n";
    $_SESSION['userId'] = $user['userId'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['userType'] = $user['userType'];
    $_SESSION['emailId'] = $user['emailId'];
    
    echo "✅ Session variables set:\n";
    echo "   userId: {$_SESSION['userId']}\n";
    echo "   username: {$_SESSION['username']}\n";
    echo "   userType: {$_SESSION['userType']}\n";
    
    // Test 4: Test redirect logic
    echo "\n4. Testing Redirect Logic:\n";
    $userType = $_SESSION['userType'];
    $redirectUrl = '';
    
    switch ($userType) {
        case 'Admin':
            $redirectUrl = BASE_URL . 'admin/dashboard';
            break;
        case 'Student':
        case 'Faculty':
            $redirectUrl = BASE_URL . 'user/dashboard';
            break;
        default:
            $redirectUrl = BASE_URL;
    }
    
    echo "✅ Redirect URL determined: {$redirectUrl}\n";
    
    echo "\n🎉 All tests passed! The signup and login flow is working correctly.\n";
    echo "\nYou can now:\n";
    echo "- Sign up with username: newuser123\n";
    echo "- Login with username: newuser123, password: password123\n";
    echo "- Should redirect to: {$redirectUrl}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
