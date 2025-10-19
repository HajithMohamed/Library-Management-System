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
      $userId = $_POST['userId'] ?? '';
      $password = $_POST['password'] ?? '';

      if (empty($userId) || empty($password)) {
        $_SESSION['error'] = 'Please enter both User ID and password.';
        $this->redirect('/');
        return;
      }

      $user = $this->userModel->authenticate($userId, $password);

      if ($user) {
        // Set session variables
        $_SESSION['userId'] = $user['userId'];
        $_SESSION['userType'] = $user['userType'];
        $_SESSION['emailId'] = $user['emailId'];

        $_SESSION['success'] = 'Welcome back, ' . $user['userId'] . '!';
        $this->authHelper->redirectByUserType();
      } else {
        $_SESSION['error'] = 'Invalid User ID or password.';
        $this->redirect('/');
      }
    } else {
      // Show login form
      $this->render('auth/login');
    }
  }

  /**
   * Handle signup (both GET and POST)
   */
  public function signup()
  {
    // Redirect if already logged in
    if ($this->authHelper->isLoggedIn()) {
      $this->authHelper->redirectByUserType();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'userId' => $_POST['userId'] ?? '',
        'password' => $_POST['password'] ?? '',
        'userType' => $_POST['userType'] ?? '',
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

      // Check if user ID already exists
      if ($this->userModel->userIdExists($data['userId'])) {
        $_SESSION['error'] = 'User ID already exists. Please choose a different one.';
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

      // Create user
      if ($this->userModel->createUser($data)) {
        // Send OTP email
        if ($this->authService->sendOTPEmail($data['emailId'], $otp)) {
          $_SESSION['success'] = 'Account created successfully! Please check your email for verification code.';
          $_SESSION['signup_userId'] = $data['userId'];
          $this->redirect('/verify-otp');
        } else {
          $_SESSION['error'] = 'Account created but failed to send verification email. Please contact support.';
          $this->redirect('/signup');
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
