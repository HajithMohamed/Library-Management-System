<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Helpers\AuthHelper;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Helpers\NotificationHelper;

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
        NotificationHelper::error('Please enter both username and password.');
        $this->redirect('/');
        return;
      }

      // Get user and verify password to handle unverified flow gracefully
      $user = $this->userModel->getUserByUsername($username);

      if ($user && $this->authHelper->verifyPassword($password, $user['password'])) {
        if (empty($user['isVerified'])) {
          // Redirect unverified users to OTP verification
          $_SESSION['signup_userId'] = $user['userId'];
          NotificationHelper::error('Please verify your email to continue.');
          $this->redirect('/verify-otp');
          return;
        }

        // Verified user -> set session and redirect by role
        $_SESSION['userId'] = $user['userId'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['userType'] = ucfirst(strtolower($user['userType'])); // Normalize to "Admin", "Student", "Teacher"
        $_SESSION['emailId'] = $user['emailId'];

        // ADDED: Also set these for compatibility
        $_SESSION['user_id'] = $user['userId'];
        $_SESSION['name'] = $user['username'];
        $_SESSION['email'] = $user['emailId'];
        $_SESSION['role'] = strtolower($user['userType']);
        $_SESSION['logged_in'] = true;

        // Migrate wishlist to favorites if exists
        if (isset($_SESSION['guest_wishlist']) && !empty($_SESSION['guest_wishlist'])) {
          $this->migrateWishlistToFavorites($_SESSION['userId'], $_SESSION['guest_wishlist']);
          unset($_SESSION['guest_wishlist']);
        }

        // Check if this is a first login (admin-created account needing password change)
        // Also check force_password_change flag and password expiry
        $needsChange = false;
        $changeReason = '';

        if (!empty($user['first_login']) && empty($user['password_changed'])) {
          $needsChange = true;
          $changeReason = 'Welcome! Please change your temporary password to continue.';
        } elseif (!empty($user['force_password_change'])) {
          $needsChange = true;
          $changeReason = 'Your account requires a password change. Please update your password to continue.';
        } elseif (!empty($user['password_changed_at'])) {
          // Check 90-day expiry
          $lastChange = strtotime($user['password_changed_at']);
          $expiryTime = $lastChange + (90 * 86400);
          if (time() > $expiryTime) {
            $needsChange = true;
            $changeReason = 'Your password has expired. Please set a new password to continue.';
          }
        }

        if ($needsChange) {
          $_SESSION['force_password_change'] = true;
          NotificationHelper::success($changeReason);
          $this->redirect('/force-change-password');
          return;
        }

        NotificationHelper::success('Welcome back, ' . $user['username'] . '!');
        $this->authHelper->redirectByUserType();
      } else {
        NotificationHelper::error('Invalid username or password.');
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
        'username' => $_POST['username'] ?? $_POST['name'] ?? '', // ADDED: Support both 'name' and 'username'
        'password' => $_POST['password'] ?? '',
        'userType' => 'Student', // Automatically set to Student
        'gender' => $_POST['gender'] ?? '',
        'dob' => $_POST['dob'] ?? '',
        'emailId' => $_POST['emailId'] ?? $_POST['email'] ?? '', // ADDED: Support both 'email' and 'emailId'
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
        NotificationHelper::error('Username already exists. Please choose a different username.');
        $this->redirect('/signup');
        return;
      }

      // Check if email already exists
      if ($this->userModel->emailExists($data['emailId'])) {
        NotificationHelper::error('Email address already exists. Please use a different email.');
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
          NotificationHelper::success('Account created! Check your email for the verification code. Your Student ID: ' . $generatedUserId);
          $_SESSION['signup_userId'] = $generatedUserId;
          $_SESSION['signup_email'] = $data['emailId']; // Store email for reference
          $this->redirect('/verify-otp');
        } else {
          NotificationHelper::error('Account created but failed to send verification email. Please contact support.');
          $_SESSION['signup_userId'] = $generatedUserId;
          $this->redirect('/verify-otp');
        }
      } else {
        NotificationHelper::error('Failed to create account. Please try again.');
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
        // Get user details to set session
        $user = $this->userModel->getUserById($userId);

        if ($user) {
          // Clear signup session variables
          unset($_SESSION['signup_userId']);
          unset($_SESSION['signup_email']);

          // Set user session (auto-login)
          $_SESSION['userId'] = $user['userId'];
          $_SESSION['username'] = $user['username'];
          $_SESSION['userType'] = ucfirst(strtolower($user['userType'])); // Normalize to "Admin", "Student", "Teacher"
          $_SESSION['emailId'] = $user['emailId'];

          // ADDED: Also set these for compatibility
          $_SESSION['user_id'] = $user['userId'];
          $_SESSION['name'] = $user['username'];
          $_SESSION['email'] = $user['emailId'];
          $_SESSION['role'] = strtolower($user['userType']);
          $_SESSION['logged_in'] = true;

          // Migrate wishlist to favorites if exists
          if (isset($_SESSION['guest_wishlist']) && !empty($_SESSION['guest_wishlist'])) {
            $this->migrateWishlistToFavorites($_SESSION['userId'], $_SESSION['guest_wishlist']);
            unset($_SESSION['guest_wishlist']);
          }

          $_SESSION['success'] = 'Account verified successfully! Welcome, ' . $user['username'] . '!';

          // Redirect to appropriate dashboard based on user type
          $this->authHelper->redirectByUserType();
        } else {
          $_SESSION['error'] = 'Account verification successful but unable to log you in. Please login manually.';
          $this->redirect('/');
        }
      } else {
        $_SESSION['error'] = 'Invalid or expired verification code.';
        $this->redirect('/verify-otp');
      }
    } else {
      // Show OTP verification form
      $this->render('auth/verify-otp');
    }
  }

  // ...existing code for forgotPassword, handleSendOtp, handleVerifyOtp, handleResetPassword...

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
      // Clear session variables
      unset($_SESSION['reset_email']);
      unset($_SESSION['reset_userId']);
      unset($_SESSION['otp_verified']);
      $_SESSION['success'] = 'Password reset successfully! You can now login.';
      $_SESSION['message'] = '<div class="alert alert-success">Password reset successfully! <a href="' . BASE_URL . '">Go to Login</a></div>';
    } else {
      $_SESSION['message'] = '<div class="alert alert-danger">Error resetting password. Please try again.</div>';
    }
  }

  /**
   * Force change password for first-login users or forced resets (GET and POST)
   */
  public function forceChangePassword()
  {
    // Must be logged in
    if (!isset($_SESSION['userId'])) {
      $this->redirect('/');
      return;
    }

    $user = $this->userModel->getUserById($_SESSION['userId']);

    // Check if user actually needs to change password
    $needsChange = false;
    if ($user) {
      if (!empty($user['first_login']) && empty($user['password_changed'])) {
        $needsChange = true;
      } elseif (!empty($user['force_password_change'])) {
        $needsChange = true;
      } elseif (!empty($user['password_changed_at'])) {
        $lastChange = strtotime($user['password_changed_at']);
        $expiryTime = $lastChange + (90 * 86400);
        if (time() > $expiryTime) {
          $needsChange = true;
        }
      }
    }

    if (!$user || !$needsChange) {
      unset($_SESSION['force_password_change']);
      $this->authHelper->redirectByUserType();
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $newPassword = $_POST['new_password'] ?? '';
      $confirmPassword = $_POST['confirm_password'] ?? '';

      if (empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error'] = 'Please fill in both password fields.';
        $this->redirect('/force-change-password');
        return;
      }

      if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'Passwords do not match.';
        $this->redirect('/force-change-password');
        return;
      }

      // Validate password strength (min 8 chars, upper, lower, number, special)
      $strengthErrors = [];
      if (strlen($newPassword) < 8) {
        $strengthErrors[] = 'Password must be at least 8 characters long.';
      }
      if (!preg_match('/[A-Z]/', $newPassword)) {
        $strengthErrors[] = 'Password must contain at least one uppercase letter.';
      }
      if (!preg_match('/[a-z]/', $newPassword)) {
        $strengthErrors[] = 'Password must contain at least one lowercase letter.';
      }
      if (!preg_match('/[0-9]/', $newPassword)) {
        $strengthErrors[] = 'Password must contain at least one number.';
      }
      if (!preg_match('/[@$!%*?&#^()_+\-=\[\]{};:\'",.<>\/\\\\|`~]/', $newPassword)) {
        $strengthErrors[] = 'Password must contain at least one special character.';
      }
      // Cannot be same as username or email
      if ($user) {
        $lowerPw = strtolower($newPassword);
        if (!empty($user['username']) && strtolower($user['username']) === $lowerPw) {
          $strengthErrors[] = 'Password cannot be the same as your username.';
        }
        if (!empty($user['emailId']) && strtolower($user['emailId']) === $lowerPw) {
          $strengthErrors[] = 'Password cannot be the same as your email address.';
        }
      }

      if (!empty($strengthErrors)) {
        $_SESSION['error'] = implode('<br>', $strengthErrors);
        $this->redirect('/force-change-password');
        return;
      }

      // Check password history
      if ($this->userModel->isPasswordInHistory($_SESSION['userId'], $newPassword)) {
        $_SESSION['error'] = 'You cannot reuse any of your last 3 passwords. Please choose a different password.';
        $this->redirect('/force-change-password');
        return;
      }

      // All validations passed - generate OTP and send to email
      $otp = rand(100000, 999999);
      $otpExpiry = time() + (15 * 60); // 15 minutes

      // Store pending data in session
      $_SESSION['force_pw_otp'] = $otp;
      $_SESSION['force_pw_otp_expiry'] = $otpExpiry;
      $_SESSION['force_pw_new_password'] = $newPassword;
      $_SESSION['force_pw_otp_attempts'] = 0;

      // Send OTP email
      $email = $user['emailId'] ?? '';
      $username = $user['username'] ?? 'User';

      if (!empty($email)) {
        $subject = "Password Change Verification - Library System";
        $body = "Hello {$username},\n\n";
        $body .= "You are changing your password for the Library Management System.\n\n";
        $body .= "Your verification code is: {$otp}\n\n";
        $body .= "This code is valid for 15 minutes.\n\n";
        $body .= "If you did NOT request this, please contact support immediately.\n\n";
        $body .= "Best regards,\nLibrary Management System\n";

        $this->authService->sendEmail($email, $subject, $body);

        // Mask email for display
        $parts = explode('@', $email);
        $maskedName = substr($parts[0], 0, 2) . str_repeat('*', max(3, strlen($parts[0]) - 2));
        $_SESSION['force_pw_masked_email'] = $maskedName . '@' . $parts[1];
        $_SESSION['success'] = "A 6-digit verification code has been sent to {$_SESSION['force_pw_masked_email']}. Please enter it below.";
      } else {
        $_SESSION['error'] = 'No email address found on your account. Please contact support.';
        $this->redirect('/force-change-password');
        return;
      }

      $this->redirect('/verify-force-password-otp');
    } else {
      // Show force change password form
      $this->render('auth/force-change-password');
    }
  }

  /**
   * Show OTP verification form for forced password change (GET)
   */
  public function verifyForcePasswordOtpForm()
  {
    if (!isset($_SESSION['userId'])) {
      $this->redirect('/');
      return;
    }

    if (!isset($_SESSION['force_pw_otp'])) {
      $_SESSION['error'] = 'No pending password change. Please start again.';
      $this->redirect('/force-change-password');
      return;
    }

    // Check if OTP expired
    if (time() > $_SESSION['force_pw_otp_expiry']) {
      $this->clearForcePendingData();
      $_SESSION['error'] = 'Verification code has expired. Please start the password change again.';
      $this->redirect('/force-change-password');
      return;
    }

    $this->render('auth/verify-force-password-otp');
  }

  /**
   * Verify OTP and complete forced password change (POST)
   */
  public function verifyForcePasswordOtp()
  {
    if (!isset($_SESSION['userId'])) {
      $this->redirect('/');
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->verifyForcePasswordOtpForm();
      return;
    }

    if (!isset($_SESSION['force_pw_otp'])) {
      $_SESSION['error'] = 'No pending password change. Please start again.';
      $this->redirect('/force-change-password');
      return;
    }

    // Check if OTP expired
    if (time() > $_SESSION['force_pw_otp_expiry']) {
      $this->clearForcePendingData();
      $_SESSION['error'] = 'Verification code has expired. Please start again.';
      $this->redirect('/force-change-password');
      return;
    }

    $enteredOtp = trim($_POST['otp'] ?? '');

    if (empty($enteredOtp)) {
      $_SESSION['error'] = 'Please enter the verification code.';
      $this->redirect('/verify-force-password-otp');
      return;
    }

    // Track attempts
    $_SESSION['force_pw_otp_attempts'] = ($_SESSION['force_pw_otp_attempts'] ?? 0) + 1;

    if ($_SESSION['force_pw_otp_attempts'] > 5) {
      $this->clearForcePendingData();
      $_SESSION['error'] = 'Too many incorrect attempts. Please start the password change again.';
      $this->redirect('/force-change-password');
      return;
    }

    // Verify OTP
    if ((string) $enteredOtp !== (string) $_SESSION['force_pw_otp']) {
      $remaining = 5 - $_SESSION['force_pw_otp_attempts'];
      $_SESSION['error'] = "Invalid verification code. You have {$remaining} attempt(s) remaining.";
      $this->redirect('/verify-force-password-otp');
      return;
    }

    // OTP verified! Now actually change the password
    $newPassword = $_SESSION['force_pw_new_password'];
    $hashedPassword = $this->authHelper->hashPassword($newPassword);
    global $mysqli;
    $stmt = $mysqli->prepare("UPDATE users SET password = ?, password_changed = 1, first_login = 0, force_password_change = 0, password_changed_at = NOW(), updatedAt = NOW() WHERE userId = ?");
    $stmt->bind_param("ss", $hashedPassword, $_SESSION['userId']);

    if ($stmt->execute()) {
      // Store in password history
      $this->userModel->addPasswordToHistory($_SESSION['userId'], $hashedPassword);

      // Log the password change
      $this->userModel->logPasswordChange($_SESSION['userId'], 'forced');

      // Send confirmation email notification
      $this->sendPasswordChangeNotification($_SESSION['userId']);

      // Clear pending data
      $this->clearForcePendingData();
      unset($_SESSION['force_password_change']);

      // Destroy session and require re-login for security
      $successMsg = 'Password changed successfully! A confirmation email has been sent. Please log in with your new password.';
      $_SESSION = [];
      if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
      }
      session_destroy();
      session_start();
      session_regenerate_id(true);
      $_SESSION['success'] = $successMsg;

      $this->redirect('/');
    } else {
      $_SESSION['error'] = 'Failed to update password. Please try again.';
      $this->redirect('/force-change-password');
    }
    $stmt->close();
  }

  /**
   * Resend OTP for forced password change
   */
  public function resendForcePasswordOtp()
  {
    if (!isset($_SESSION['userId']) || !isset($_SESSION['force_pw_otp'])) {
      $this->redirect('/force-change-password');
      return;
    }

    $user = $this->userModel->getUserById($_SESSION['userId']);
    if (!$user || empty($user['emailId'])) {
      $_SESSION['error'] = 'Unable to send verification code.';
      $this->redirect('/verify-force-password-otp');
      return;
    }

    // Generate new OTP
    $otp = rand(100000, 999999);
    $_SESSION['force_pw_otp'] = $otp;
    $_SESSION['force_pw_otp_expiry'] = time() + (15 * 60);
    $_SESSION['force_pw_otp_attempts'] = 0;

    $subject = "Password Change Verification - Library System";
    $body = "Hello " . ($user['username'] ?? 'User') . ",\n\n";
    $body .= "Your new verification code is: {$otp}\n\n";
    $body .= "This code is valid for 15 minutes.\n\n";
    $body .= "Best regards,\nLibrary Management System\n";

    $this->authService->sendEmail($user['emailId'], $subject, $body);

    $_SESSION['success'] = "A new verification code has been sent to {$_SESSION['force_pw_masked_email']}.";
    $this->redirect('/verify-force-password-otp');
  }

  /**
   * Clear forced password change pending session data
   */
  private function clearForcePendingData()
  {
    unset(
      $_SESSION['force_pw_otp'],
      $_SESSION['force_pw_otp_expiry'],
      $_SESSION['force_pw_new_password'],
      $_SESSION['force_pw_otp_attempts'],
      $_SESSION['force_pw_masked_email']
    );
  }

  /**
   * Send password change notification email (private helper)
   */
  private function sendPasswordChangeNotification($userId)
  {
    try {
      $user = $this->userModel->getUserById($userId);
      if (!$user || empty($user['emailId']))
        return false;

      $email = $user['emailId'];
      $username = $user['username'] ?? 'User';
      $changeTime = date('F j, Y \a\t g:i A');
      $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

      $subject = "Password Changed Successfully - Library System";
      $body = "Hello {$username},\n\n";
      $body .= "Your password for the Library Management System was changed successfully.\n\n";
      $body .= "Details:\n";
      $body .= "  Date & Time: {$changeTime}\n";
      $body .= "  IP Address: {$ipAddress}\n\n";
      $body .= "If you made this change, no further action is needed.\n\n";
      $body .= "If you did NOT make this change, please:\n";
      $body .= "  1. Contact library support immediately\n";
      $body .= "  2. Use the 'Forgot Password' feature to secure your account\n\n";
      $body .= "Best regards,\nLibrary Management System\n";

      return $this->sendEmailWithPHPMailer($email, $subject, $body);
    } catch (\Exception $e) {
      error_log("Error sending password change notification: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Send email using PHPMailer (PUBLIC for reuse by other controllers)
   */
  public function sendEmailWithPHPMailer($to, $subject, $body)
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
    global $mysqli;

    $status = [
      'system' => 'online',
      'database' => $mysqli ? 'connected' : 'disconnected',
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
      global $mysqli;

      if (!$mysqli) {
        error_log("Wishlist migration error: Database connection not available");
        return false;
      }

      // Use INSERT IGNORE to skip duplicates without errors
      $stmt = $mysqli->prepare("INSERT IGNORE INTO favorites (userId, isbn, notes, createdAt) VALUES (?, ?, ?, NOW())");

      if (!$stmt) {
        error_log("Wishlist migration error: " . $mysqli->error);
        return false;
      }

      $note = 'Migrated from guest wishlist';

      foreach ($wishlist as $isbn) {
        $stmt->bind_param('sss', $userId, $isbn, $note);
        $stmt->execute();
      }

      $stmt->close();
      return true;
    } catch (\Exception $e) {
      error_log("Wishlist migration error: " . $e->getMessage());
      return false;
    }
  }
}
