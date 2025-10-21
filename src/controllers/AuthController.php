<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Helpers\AuthHelper;

class AuthController
{
  private $userModel;
  private $authService;
  private $authHelper;

  public function __construct()
  {
    $this->userModel = new User();
    $this->authService = new AuthService();
    $this->authHelper = new AuthHelper();
  }

  /**
   * Handle login (both GET and POST)
   */
  public function login()
  {
    // Redirect if already logged in
    if ($this->authHelper->isLoggedIn()) {
      $this->authHelper->redirectByUserType();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $username = $_POST['username'] ?? '';
      $password = $_POST['password'] ?? '';

      if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please enter both username and password.';
        $this->redirect('/');
        return;
      }

      // Get user and verify password to handle unverified flow gracefully
      $user = $this->userModel->getUserByUsername($username);

      if ($user && $this->authHelper->verifyPassword($password, $user['password'])) {
        if (empty($user['isVerified'])) {
          // Redirect unverified users to OTP verification
          $_SESSION['signup_userId'] = $user['userId'];
          $_SESSION['error'] = 'Please verify your email to continue.';
          $this->redirect('/verify-otp');
          return;
        }

        // Verified user -> set session and redirect by role
        $_SESSION['userId'] = $user['userId'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['userType'] = $user['userType'];
        $_SESSION['emailId'] = $user['emailId'];

        $_SESSION['success'] = 'Welcome back, ' . $user['username'] . '!';
        $this->authHelper->redirectByUserType();
      } else {
        $_SESSION['error'] = 'Invalid username or password.';
        $this->redirect('/');
      }
    } else {
      // Show login form
      $this->render('auth/login');
    }
  }

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

  /**
   * Logout user
   */
  public function logout()
  {
    $this->authHelper->logout();
  }

  /**
   * Handle OTP verification (both GET and POST)
   */
  public function verifyOtp()
  {
    // Block access for logged-in users
    if ($this->authHelper->isLoggedIn()) {
      $this->authHelper->redirectByUserType();
      return;
    }

    if (!isset($_SESSION['signup_userId'])) {
      $this->redirect('/');
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $userId = $_SESSION['signup_userId'] ?? '';
      $otp = $_POST['otp'] ?? '';

      if (empty($userId) || empty($otp)) {
        $_SESSION['error'] = 'Please enter the verification code.';
        $this->redirect('/verify-otp');
        return;
      }

      if ($this->userModel->verifyUser($userId, $otp)) {
        unset($_SESSION['signup_userId']);
        $_SESSION['success'] = 'Account verified successfully! You can now login.';
        $this->redirect('/');
      } else {
        $_SESSION['error'] = 'Invalid or expired verification code.';
        $this->redirect('/verify-otp');
      }
    } else {
      // Show OTP verification form
      $this->render('auth/verify-otp');
    }
  }

  /**
   * Render a view with data
   */
  private function render($view, $data = [])
  {
    extract($data);
    $viewFile = APP_ROOT . '/views/' . $view . '.php';

    if (file_exists($viewFile)) {
      include $viewFile;
    } else {
      http_response_code(404);
      include APP_ROOT . '/views/errors/404.php';
    }
  }

  /**
   * Redirect to a URL
   */
  private function redirect($url)
  {
    header('Location: ' . BASE_URL . ltrim($url, '/'));
    exit;
  }
}
