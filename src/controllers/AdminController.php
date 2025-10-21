<?php
// In src/controllers/AuthController.php
// Replace the signup() method with this fixed version:

public function signup()
{
    // Redirect if already logged in
    if ($this->authHelper->isLoggedIn()) {
        $this->authHelper->redirectByUserType();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'userType' => 'Student', // Automatically set to Student
            'gender' => $_POST['gender'] ?? '',
            'dob' => $_POST['dob'] ?? '',
            'emailId' => $_POST['emailId'] ?? '',
            'phoneNumber' => $_POST['phoneNumber'] ?? '',
            'address' => $_POST['address'] ?? '',
            'isVerified' => 0,
            'otp' => null,
            'otpExpiry' => null
        ];

        // Validate user data
        $errors = $this->userModel->validateUserData($data);
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            $this->redirect('/signup');
            return;
        }

        // Check if username already exists
        if ($this->userModel->usernameExists($data['username'])) {
            $_SESSION['error'] = 'Username already exists. Please choose a different username.';
            $this->redirect('/signup');
            return;
        }

        // Check if email already exists
        if ($this->userModel->emailExists($data['emailId'])) {
            $_SESSION['error'] = 'Email address already exists. Please use a different email.';
            $this->redirect('/signup');
            return;
        }

        // Hash password
        $data['password'] = $this->authHelper->hashPassword($data['password']);

        // Generate OTP
        $otp = rand(100000, 999999);
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $data['otp'] = $otp;
        $data['otpExpiry'] = $otpExpiry;

        // Create user (user ID will be auto-generated)
        if ($this->userModel->createUser($data)) {
            // Get the generated user ID
            $generatedUserId = $this->userModel->getLastGeneratedUserId();
            
            // Send OTP email
            if ($this->authService->sendOTPEmail($data['emailId'], $otp)) {
                $_SESSION['success'] = 'Account created! Check your email for the verification code. Your Student ID: ' . $generatedUserId;
                $_SESSION['signup_userId'] = $generatedUserId;
                $_SESSION['signup_email'] = $data['emailId']; // Store email for reference
                
                // FIXED: Redirect to verify-otp page instead of login
                $this->redirect('/verify-otp');
            } else {
                $_SESSION['error'] = 'Account created but failed to send verification email. Please contact support.';
                $_SESSION['signup_userId'] = $generatedUserId;
                
                // Still redirect to verify-otp so they can try again
                $this->redirect('/verify-otp');
            }
        } else {
            $_SESSION['error'] = 'Failed to create account. Please try again.';
            $this->redirect('/signup');
        }
    } else {
        // Show signup form
        $this->render('auth/signup');
    }
}