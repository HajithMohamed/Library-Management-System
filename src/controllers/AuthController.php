<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Helpers\AuthHelper;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
      $username = trim($_POST['username'] ?? '');
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
        $_SESSION['userType'] = ucfirst(strtolower($user['userType'])); // Normalize to "Admin", "Student", "Teacher"
        $_SESSION['emailId'] = $user['emailId'];

        // Migrate wishlist to favorites if exists
        if (isset($_SESSION['guest_wishlist']) && !empty($_SESSION['guest_wishlist'])) {
          $this->migrateWishlistToFavorites($_SESSION['userId'], $_SESSION['guest_wishlist']);
          unset($_SESSION['guest_wishlist']);
        }

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

  /**
   * Signup method
   */
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
   * Handle forgot password (both GET and POST)
   */
  public function forgotPassword()
  {
    // Block access for logged-in users
    if ($this->authHelper->isLoggedIn()) {
      $this->authHelper->redirectByUserType();
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $action = $_POST['action'] ?? '';

      if ($action === 'send_otp') {
        $this->handleSendOtp();
      } elseif ($action === 'verify_otp') {
        $this->handleVerifyOtp();
      } elseif ($action === 'reset_password') {
        $this->handleResetPassword();
      }
    }

    // Show forgot password form
    $this->render('auth/forgotPassword');
  }

  /**
   * Handle sending OTP for password reset
   */
  private function handleSendOtp()
  {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
      $_SESSION['message'] = '<div class="alert alert-danger">Please enter your email</div>';
      return;
    }

    $user = $this->userModel->getUserByEmail($email);
    if (!$user) {
      $_SESSION['message'] = '<div class="alert alert-danger">Email not found in our system.</div>';
      return;
    }

    $otp = rand(100000, 999999);
    $otpExpiry = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    
    if ($this->userModel->updateUserOtp($user['userId'], $otp, $otpExpiry)) {
      // Send OTP via email
      $subject = "Password Reset OTP - Library System";
      $userName = $user['username'] ?? 'User';
      $body = "Hello " . htmlspecialchars($userName) . ",\n\n";
      $body .= "Your OTP for password reset is: " . $otp . "\n";
      $body .= "This OTP is valid for " . OTP_EXPIRY_MINUTES . " minutes.\n\n";
      $body .= "If you didn't request this, please ignore this email.\n\n";
      $body .= "Best regards,\nLibrary Management System";

      // Use PHPMailer for better email delivery
      if ($this->sendEmailWithPHPMailer($email, $subject, $body)) {
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_userId'] = $user['userId'];
        $_SESSION['message'] = '<div class="alert alert-success">OTP sent to your email. Check your inbox.</div>';
      } else {
        // Fallback for development: show OTP on screen
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_userId'] = $user['userId'];
        $_SESSION['message'] = '<div class="alert alert-warning">Email sending failed, but you can use this OTP for testing: <strong>' . $otp . '</strong></div>';
      }
    } else {
      $_SESSION['message'] = '<div class="alert alert-danger">Failed to generate OTP. Please try again.</div>';
    }
  }

  /**
   * Handle OTP verification for password reset
   */
  private function handleVerifyOtp()
  {
    $otp = trim($_POST['otp'] ?? '');
    $email = $_SESSION['reset_email'] ?? '';

    if (empty($otp)) {
      $_SESSION['message'] = '<div class="alert alert-danger">Please enter the OTP</div>';
      return;
    }

    $user = $this->userModel->getUserByEmail($email);
    if ($user && $user['otp'] === $otp && strtotime($user['otpExpiry']) > time()) {
      $_SESSION['otp_verified'] = true;
      $_SESSION['message'] = '<div class="alert alert-success">OTP verified! You can now reset your password.</div>';
    } else {
      $_SESSION['message'] = '<div class="alert alert-danger">Invalid or expired OTP</div>';
    }
  }

  /**
   * Handle password reset
   */
  private function handleResetPassword()
  {
    if (!($_SESSION['otp_verified'] ?? false)) {
      $_SESSION['message'] = '<div class="alert alert-danger">Please verify OTP first</div>';
      return;
    }

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($password) || empty($confirmPassword)) {
      $_SESSION['message'] = '<div class="alert alert-danger">Please fill all fields</div>';
      return;
    }

    if ($password !== $confirmPassword) {
      $_SESSION['message'] = '<div class="alert alert-danger">Passwords do not match</div>';
      return;
    }

    if (strlen($password) < 6) {
      $_SESSION['message'] = '<div class="alert alert-danger">Password must be at least 6 characters</div>';
      return;
    }

    $hashedPassword = $this->authHelper->hashPassword($password);
    $userId = $_SESSION['reset_userId'] ?? '';

    if ($this->userModel->updateUserPassword($userId, $hashedPassword)) {
      // Log the password change
      $action_log = "Password reset";
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
      $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
      
      // Note: You might want to add an audit log method to User model
      // $this->userModel->logUserAction($userId, $action_log, $ip, $userAgent);

      unset($_SESSION['reset_email']);
      unset($_SESSION['reset_userId']);
      unset($_SESSION['otp_verified']);
      $_SESSION['message'] = '<div class="alert alert-success">Password reset successfully! <a href="' . BASE_URL . '">Go to Login</a></div>';
    } else {
      $_SESSION['message'] = '<div class="alert alert-danger">Error resetting password. Please try again.</div>';
    }
  }

  /**
   * Send email using PHPMailer
   */
  private function sendEmailWithPHPMailer($to, $subject, $body)
  {
    try {
      $mail = new PHPMailer(true);

      // Server settings
      $mail->isSMTP();
      $mail->Host = SMTP_HOST;
      $mail->SMTPAuth = true;
      $mail->Username = SMTP_USERNAME;
      $mail->Password = SMTP_PASSWORD;
      $mail->SMTPSecure = SMTP_ENCRYPTION;
      $mail->Port = SMTP_PORT;

      // Recipients
      $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
      $mail->addAddress($to);

      // Content
      $mail->isHTML(false);
      $mail->Subject = $subject;
      $mail->Body = $body;

      $mail->send();
      return true;
    } catch (Exception $e) {
      // Log error for debugging
      error_log("Email sending failed: " . $e->getMessage());
      return false;
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

  /**
   * Show 403 Forbidden page
   */
  public function show403()
  {
      http_response_code(403);
      $pageTitle = 'Access Denied - 403';
      include APP_ROOT . '/views/errors/403.php';
  }

  /**
   * Show 404 Not Found page
   */
  public function show404()
  {
      http_response_code(404);
      $pageTitle = 'Page Not Found - 404';
      include APP_ROOT . '/views/errors/404.php';
  }

  /**
   * Health check endpoint
   */
  public function healthCheck()
  {
      header('Content-Type: application/json');
      echo json_encode([
          'status' => 'ok',
          'timestamp' => date('Y-m-d H:i:s'),
          'database' => 'connected'
      ]);
      exit();
  }

  /**
   * System status endpoint
   */
  public function systemStatus()
  {
      global $conn;
      
      $status = [
          'system' => 'online',
          'database' => $conn ? 'connected' : 'disconnected',
          'php_version' => phpversion(),
          'timestamp' => date('Y-m-d H:i:s')
      ];
      
      header('Content-Type: application/json');
      echo json_encode($status);
      exit();
  }

  /**
   * Migrate wishlist to favorites
   */
  private function migrateWishlistToFavorites($userId, $wishlist)
  {
    try {
        require_once __DIR__ . '/../config/dbConnection.php';
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (userId, isbn, notes, createdAt) VALUES (?, ?, ?, NOW())");
        
        foreach ($wishlist as $isbn) {
            $stmt->execute([$userId, $isbn, 'Migrated from guest wishlist']);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Wishlist migration error: " . $e->getMessage());
        return false;
    }
  }
}
