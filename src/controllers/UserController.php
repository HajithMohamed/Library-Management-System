<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\Transaction;
use App\Services\AuthService;
use App\Helpers\ValidationHelper;

class UserController extends BaseController
{
    private $userModel;
    private $bookModel;
    private $borrowModel;
    private $transactionModel;
    private $authService;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->bookModel = new Book();
        $this->borrowModel = new BorrowRecord();
        $this->transactionModel = new Transaction();
        $this->authService = new AuthService();
    }

    /**
     * Display user dashboard
     */
    public function dashboard()
    {
        $this->requireLogin();
        
        // Redirect Faculty users to their own dashboard
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/dashboard');
            return;
        }
        
        $userId = $_SESSION['userId'] ?? null;
        $userType = $_SESSION['userType'] ?? 'Student';
        
        if (!$userId) {
            $_SESSION['error'] = 'User ID not found. Please login again.';
            $this->redirect('login');
            return;
        }
        
        global $mysqli;
        
        try {
            // Get user privileges (role-based limits)
            $privileges = $this->userModel->getUserPrivileges($userId);
            $maxBooks = $privileges['max_borrow_limit'] ?? 3;
            $borrowPeriodDays = $privileges['borrow_period_days'] ?? 14;
            $maxRenewals = $privileges['max_renewals'] ?? 1;
            
            // Get count of currently borrowed books from books_borrowed table
            $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM books_borrowed WHERE userId = ? AND status = 'Active'");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $borrowedCount = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
            $stmt->close();
            
            // Get count of overdue books
            $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM books_borrowed WHERE userId = ? AND status = 'Active' AND dueDate < CURDATE()");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $overdueCount = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
            $stmt->close();
            
            // Calculate total fines from overdue books
            $stmt = $mysqli->prepare("SELECT dueDate FROM books_borrowed WHERE userId = ? AND status = 'Active' AND dueDate < CURDATE()");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $totalFines = 0;
            while ($row = $result->fetch_assoc()) {
                $daysOverdue = max(0, (int)((time() - strtotime($row['dueDate'])) / 86400));
                $totalFines += $daysOverdue * 5; // LKR5 per day
            }
            $stmt->close();
            
            // Get user statistics
            $userStats = [
                'borrowed_books' => $borrowedCount,
                'overdue_books' => $overdueCount,
                'total_fines' => $totalFines,
                'max_books' => $maxBooks,
                'borrow_period_days' => $borrowPeriodDays,
                'max_renewals' => $maxRenewals,
                'remaining_slots' => max(0, $maxBooks - $borrowedCount)
            ];
            
            // Get recent activity (last 5 borrow records)
            $stmt = $mysqli->prepare("
                SELECT 
                    bb.borrowDate as borrow_date,
                    bb.returnDate as return_date,
                    bb.dueDate as due_date,
                    COALESCE(b.bookName, 'Unknown Book') as title,
                    COALESCE(b.authorName, 'Unknown Author') as author
                FROM books_borrowed bb
                LEFT JOIN books b ON bb.isbn = b.isbn
                WHERE bb.userId = ?
                ORDER BY bb.borrowDate DESC
                LIMIT 5
            ");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $recentActivity = [];
            while ($row = $result->fetch_assoc()) {
                $recentActivity[] = $row;
            }
            $stmt->close();
            
        } catch (\Exception $e) {
            error_log("Error loading dashboard data: " . $e->getMessage());
            // Set default values on error
            $userStats = [
                'borrowed_books' => 0,
                'overdue_books' => 0,
                'total_fines' => 0,
                'max_books' => $userType === 'Faculty' ? 10 : 3,
                'borrow_period_days' => $userType === 'Faculty' ? 60 : 14,
                'max_renewals' => $userType === 'Faculty' ? 2 : 1,
                'remaining_slots' => $userType === 'Faculty' ? 10 : 3
            ];
            $recentActivity = [];
        }
        
        // Pass data to view
        $this->data['userStats'] = $userStats;
        $this->data['recentActivity'] = $recentActivity;
        
        $this->view('users/dashboard', $this->data);
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        $this->requireLogin();
        
        // Redirect Faculty users to their own profile page
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/profile');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updateProfile();
        }
        
        $userId = $_SESSION['userId'];
        $user = $this->userModel->findById($userId);
        
        $this->data['user'] = $user;
        $this->view('users/profile', $this->data);
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate using ValidationHelper
            $errors = ValidationHelper::validateProfileUpdate($_POST);
            
            if (!empty($errors)) {
                $_SESSION['validation_errors'] = $errors;
                ValidationHelper::setFormData($_POST);
                $_SESSION['error'] = 'Please fix the validation errors below';
                header('Location: ' . BASE_URL . 'user/profile');
                exit;
            }
            
            ValidationHelper::clearValidation();
            
            $userId = $_SESSION['userId'];
            $data = [
                'emailId' => $_POST['emailId'] ?? '',
                'phoneNumber' => $_POST['phoneNumber'] ?? '',
                'address' => $_POST['address'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'dob' => $_POST['dob'] ?? ''
            ];
            
            // Handle profile image upload
            if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = APP_ROOT . '/public/assets/images/users/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileInfo = pathinfo($_FILES['profileImage']['name']);
                $extension = strtolower($fileInfo['extension']);
                
                // Validate file type
                if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $_SESSION['error'] = 'Only JPG and PNG images are allowed';
                    $this->redirect('user/profile');
                    return;
                }
                
                // Validate file size (2MB max)
                if ($_FILES['profileImage']['size'] > 2 * 1024 * 1024) {
                    $_SESSION['error'] = 'File size must be less than 2 MB';
                    $this->redirect('user/profile');
                    return;
                }
                
                // Remove old profile images for this user
                $oldFiles = glob($uploadDir . $userId . '.*');
                foreach ($oldFiles as $oldFile) {
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                
                // Save new image
                $newFileName = $userId . '.' . $extension;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetPath)) {
                    // Store relative path in database
                    $data['profileImage'] = 'assets/images/users/' . $newFileName;
                } else {
                    $_SESSION['error'] = 'Failed to upload profile image';
                    $this->redirect('user/profile');
                    return;
                }
            }
            
            if ($this->userModel->updateProfile($userId, $data)) {
                $_SESSION['success'] = 'Profile updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update profile';
            }
            
            $this->redirect('user/profile');
        }
    }

    /**
     * Show password change form (GET)
     */
    public function changePasswordForm()
    {
        $this->requireLogin();
        
        $userId = $_SESSION['userId'];
        $user = $this->userModel->findById($userId);
        
        $this->data['user'] = $user;
        $this->data['pageTitle'] = 'Change Password';
        $this->view('users/change-password', $this->data);
    }

    /**
     * Change password (POST) - validates then sends OTP for verification
     */
    public function changePassword()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/change-password');
            return;
        }
        
        $userId = $_SESSION['userId'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Determine redirect path based on source
        $redirectPath = $_POST['redirect_to'] ?? 'user/change-password';
        
        // Check rate limiting (5 failed attempts = 30 min lockout)
        if ($this->userModel->isPasswordChangeLocked($userId)) {
            $remainingMinutes = $this->userModel->getLockoutRemainingMinutes($userId);
            $_SESSION['error'] = "Too many failed attempts. Please try again in {$remainingMinutes} minute(s).";
            $this->redirect($redirectPath);
            return;
        }
        
        // Validate all fields are present
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'All password fields are required.';
            $this->redirect($redirectPath);
            return;
        }
        
        // Confirm passwords match
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New passwords do not match.';
            $this->redirect($redirectPath);
            return;
        }
        
        // Validate password strength
        $strengthErrors = $this->validatePasswordStrength($newPassword, $userId);
        if (!empty($strengthErrors)) {
            $_SESSION['error'] = implode('<br>', $strengthErrors);
            $this->redirect($redirectPath);
            return;
        }
        
        // Verify current password before proceeding
        $user = $this->userModel->findById($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            // Record failed attempt
            $this->userModel->recordFailedPasswordAttempt($userId, 'wrong_current_password');
            $_SESSION['error'] = 'Current password is incorrect.';
            $this->redirect($redirectPath);
            return;
        }
        
        // Check password history
        if ($this->userModel->isPasswordInHistory($userId, $newPassword)) {
            $_SESSION['error'] = 'You cannot reuse any of your last 3 passwords. Please choose a different password.';
            $this->redirect($redirectPath);
            return;
        }
        
        // Check new password is not same as current
        if (password_verify($newPassword, $user['password'])) {
            $_SESSION['error'] = 'New password cannot be the same as your current password.';
            $this->redirect($redirectPath);
            return;
        }
        
        // All validations passed - generate OTP and send to email
        $otp = rand(100000, 999999);
        $otpExpiry = time() + (15 * 60); // 15 minutes
        
        // Store pending password change data in session
        $_SESSION['pw_change_otp'] = $otp;
        $_SESSION['pw_change_otp_expiry'] = $otpExpiry;
        $_SESSION['pw_change_new_password'] = $newPassword;
        $_SESSION['pw_change_redirect'] = $redirectPath;
        $_SESSION['pw_change_user_id'] = $userId;
        $_SESSION['pw_change_otp_attempts'] = 0;
        
        // Send OTP email
        $email = $user['emailId'] ?? '';
        $username = $user['username'] ?? 'User';
        
        if (!empty($email)) {
            $this->sendPasswordOtpEmail($email, $username, $otp);
            
            // Mask email for display
            $maskedEmail = $this->maskEmail($email);
            $_SESSION['pw_change_masked_email'] = $maskedEmail;
            $_SESSION['success'] = "A 6-digit verification code has been sent to {$maskedEmail}. Please enter it below to confirm your password change.";
        } else {
            $_SESSION['error'] = 'No email address found on your account. Please contact support.';
            $this->redirect($redirectPath);
            return;
        }
        
        // Redirect to OTP verification page
        $userType = strtolower($_SESSION['userType'] ?? 'user');
        if ($userType === 'student') $userType = 'user';
        $this->redirect($userType . '/verify-password-otp');
    }

    /**
     * Show OTP verification form for password change (GET)
     */
    public function verifyPasswordOtpForm()
    {
        $this->requireLogin();
        
        // Must have pending OTP
        if (!isset($_SESSION['pw_change_otp'])) {
            $userType = strtolower($_SESSION['userType'] ?? 'user');
            if ($userType === 'student') $userType = 'user';
            $_SESSION['error'] = 'No pending password change. Please start the password change process again.';
            $this->redirect($userType . '/change-password');
            return;
        }
        
        // Check if OTP has expired
        if (time() > $_SESSION['pw_change_otp_expiry']) {
            $this->clearPendingPasswordChange();
            $userType = strtolower($_SESSION['userType'] ?? 'user');
            if ($userType === 'student') $userType = 'user';
            $_SESSION['error'] = 'Verification code has expired. Please start the password change process again.';
            $this->redirect($userType . '/change-password');
            return;
        }
        
        $this->data['pageTitle'] = 'Verify Password Change';
        $this->data['maskedEmail'] = $_SESSION['pw_change_masked_email'] ?? 'your email';
        $this->data['otpExpiry'] = $_SESSION['pw_change_otp_expiry'] ?? 0;
        $this->view('users/verify-password-otp', $this->data);
    }

    /**
     * Verify OTP and complete password change (POST)
     */
    public function verifyPasswordOtp()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->verifyPasswordOtpForm();
            return;
        }
        
        $userType = strtolower($_SESSION['userType'] ?? 'user');
        if ($userType === 'student') $userType = 'user';
        $otpRedirect = $userType . '/verify-password-otp';
        $changeRedirect = $userType . '/change-password';
        
        // Must have pending OTP
        if (!isset($_SESSION['pw_change_otp'])) {
            $_SESSION['error'] = 'No pending password change. Please start the process again.';
            $this->redirect($changeRedirect);
            return;
        }
        
        // Check if OTP has expired
        if (time() > $_SESSION['pw_change_otp_expiry']) {
            $this->clearPendingPasswordChange();
            $_SESSION['error'] = 'Verification code has expired. Please start the password change process again.';
            $this->redirect($changeRedirect);
            return;
        }
        
        $enteredOtp = trim($_POST['otp'] ?? '');
        
        if (empty($enteredOtp)) {
            $_SESSION['error'] = 'Please enter the verification code.';
            $this->redirect($otpRedirect);
            return;
        }
        
        // Track OTP attempts
        $_SESSION['pw_change_otp_attempts'] = ($_SESSION['pw_change_otp_attempts'] ?? 0) + 1;
        
        if ($_SESSION['pw_change_otp_attempts'] > 5) {
            $this->clearPendingPasswordChange();
            $_SESSION['error'] = 'Too many incorrect attempts. Please start the password change process again.';
            $this->redirect($changeRedirect);
            return;
        }
        
        // Verify OTP
        if ((string)$enteredOtp !== (string)$_SESSION['pw_change_otp']) {
            $remaining = 5 - $_SESSION['pw_change_otp_attempts'];
            $_SESSION['error'] = "Invalid verification code. You have {$remaining} attempt(s) remaining.";
            $this->redirect($otpRedirect);
            return;
        }
        
        // OTP verified! Now actually change the password
        $userId = $_SESSION['pw_change_user_id'];
        $newPassword = $_SESSION['pw_change_new_password'];
        $redirectPath = $_SESSION['pw_change_redirect'] ?? $changeRedirect;
        
        // Verify user session still matches
        if ($userId !== $_SESSION['userId']) {
            $this->clearPendingPasswordChange();
            $_SESSION['error'] = 'Session mismatch. Please start the password change process again.';
            $this->redirect($changeRedirect);
            return;
        }
        
        // Hash and update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        global $conn;
        $stmt = $conn->prepare("UPDATE users SET password = ?, password_changed = 1, password_changed_at = NOW(), updatedAt = NOW() WHERE userId = ?");
        $stmt->bind_param("ss", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            // Store in password history
            $this->userModel->addPasswordToHistory($userId, $hashedPassword);
            
            // Log the password change
            $this->userModel->logPasswordChange($userId, 'voluntary');
            
            // Clear any failed attempts
            $this->userModel->clearFailedPasswordAttempts($userId);
            
            // Send password changed confirmation email
            $this->sendPasswordChangeEmail($userId);
            
            // Create in-app notification
            $this->createNotification(
                $userId,
                'Password Changed',
                'Your password was changed successfully on ' . date('M j, Y \a\t g:i A') . '. If you did not make this change, please contact library support immediately.',
                'security'
            );
            
            // Clear pending password change session data
            $this->clearPendingPasswordChange();
            
            // Invalidate session - force re-login with new password
            $_SESSION['success'] = 'Password changed successfully! A confirmation email has been sent. Please log in with your new password.';
            
            // Destroy current session and redirect to login
            $this->destroySessionAndRedirect();
            return;
        } else {
            $this->clearPendingPasswordChange();
            $_SESSION['error'] = 'Failed to update password. Please try again.';
            $this->redirect($redirectPath);
        }
        $stmt->close();
    }

    /**
     * Resend OTP for password change
     */
    public function resendPasswordOtp()
    {
        $this->requireLogin();
        
        $userType = strtolower($_SESSION['userType'] ?? 'user');
        if ($userType === 'student') $userType = 'user';
        $otpRedirect = $userType . '/verify-password-otp';
        $changeRedirect = $userType . '/change-password';
        
        // Must have pending password change
        if (!isset($_SESSION['pw_change_otp'])) {
            $_SESSION['error'] = 'No pending password change. Please start the process again.';
            $this->redirect($changeRedirect);
            return;
        }
        
        $userId = $_SESSION['pw_change_user_id'] ?? $_SESSION['userId'];
        $user = $this->userModel->findById($userId);
        
        if (!$user || empty($user['emailId'])) {
            $_SESSION['error'] = 'Unable to send verification code. No email found.';
            $this->redirect($otpRedirect);
            return;
        }
        
        // Generate new OTP
        $otp = rand(100000, 999999);
        $otpExpiry = time() + (15 * 60); // 15 minutes
        
        $_SESSION['pw_change_otp'] = $otp;
        $_SESSION['pw_change_otp_expiry'] = $otpExpiry;
        $_SESSION['pw_change_otp_attempts'] = 0;
        
        // Send new OTP
        $this->sendPasswordOtpEmail($user['emailId'], $user['username'] ?? 'User', $otp);
        
        $maskedEmail = $this->maskEmail($user['emailId']);
        $_SESSION['pw_change_masked_email'] = $maskedEmail;
        $_SESSION['success'] = "A new verification code has been sent to {$maskedEmail}.";
        $this->redirect($otpRedirect);
    }

    /**
     * Cancel pending password change
     */
    public function cancelPasswordChange()
    {
        $this->requireLogin();
        
        $this->clearPendingPasswordChange();
        
        $userType = strtolower($_SESSION['userType'] ?? 'user');
        if ($userType === 'student') $userType = 'user';
        $_SESSION['info'] = 'Password change has been cancelled.';
        $this->redirect($userType . '/change-password');
    }

    /**
     * Send OTP email for password change verification
     */
    private function sendPasswordOtpEmail($email, $username, $otp)
    {
        try {
            $subject = "Password Change Verification - Library System";
            $body = "Hello {$username},\n\n";
            $body .= "You have requested to change your password for the Library Management System.\n\n";
            $body .= "Your verification code is: {$otp}\n\n";
            $body .= "This code is valid for 15 minutes.\n\n";
            $body .= "If you did NOT request this password change, please ignore this email and your password will remain unchanged.\n\n";
            $body .= "Details:\n";
            $body .= "  Date & Time: " . date('F j, Y \a\t g:i A') . "\n";
            $body .= "  IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n\n";
            $body .= "Best regards,\nLibrary Management System\n";
            
            return $this->authService->sendEmail($email, $subject, $body);
        } catch (\Exception $e) {
            error_log("Error sending password OTP email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mask email address for display (e.g., jo***@example.com)
     */
    private function maskEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
        
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];
        
        if (strlen($name) <= 2) {
            $masked = $name[0] . '***';
        } else {
            $masked = substr($name, 0, 2) . str_repeat('*', max(3, strlen($name) - 2));
        }
        
        return $masked . '@' . $domain;
    }

    /**
     * Clear all pending password change session data
     */
    private function clearPendingPasswordChange()
    {
        unset(
            $_SESSION['pw_change_otp'],
            $_SESSION['pw_change_otp_expiry'],
            $_SESSION['pw_change_new_password'],
            $_SESSION['pw_change_redirect'],
            $_SESSION['pw_change_user_id'],
            $_SESSION['pw_change_otp_attempts'],
            $_SESSION['pw_change_masked_email']
        );
    }

    /**
     * Validate password strength requirements
     */
    private function validatePasswordStrength($password, $userId = null)
    {
        $errors = [];
        
        // Minimum 8 characters
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        
        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        
        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }
        
        // At least one number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }
        
        // At least one special character
        if (!preg_match('/[@$!%*?&#^()_+\-=\[\]{};:\'",.<>\/\\\\|`~]/', $password)) {
            $errors[] = 'Password must contain at least one special character (@, $, !, %, *, ?, &, etc.).';
        }
        
        // Cannot be same as username or email
        if ($userId) {
            $user = $this->userModel->findById($userId);
            if ($user) {
                $lowerPassword = strtolower($password);
                if (!empty($user['username']) && strtolower($user['username']) === $lowerPassword) {
                    $errors[] = 'Password cannot be the same as your username.';
                }
                if (!empty($user['emailId']) && strtolower($user['emailId']) === $lowerPassword) {
                    $errors[] = 'Password cannot be the same as your email address.';
                }
            }
        }
        
        return $errors;
    }

    /**
     * Send password change notification email
     */
    private function sendPasswordChangeEmail($userId)
    {
        try {
            $user = $this->userModel->findById($userId);
            if (!$user || empty($user['emailId'])) {
                return false;
            }
            
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
            $body .= "Best regards,\n";
            $body .= "Library Management System\n";
            
            // Use AuthService to send email
            return $this->authService->sendEmail($email, $subject, $body);
        } catch (\Exception $e) {
            error_log("Error sending password change email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Destroy session and redirect to login (after password change)
     */
    private function destroySessionAndRedirect()
    {
        // Store success message before destroying session
        $successMessage = $_SESSION['success'] ?? 'Password changed successfully! Please log in with your new password.';
        
        // Clear all session data
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy session
        session_destroy();
        
        // Start a new session for the flash message
        session_start();
        session_regenerate_id(true);
        $_SESSION['success'] = $successMessage;
        
        // Redirect to login
        header('Location: ' . BASE_URL);
        exit;
    }

    /**
     * Display user fines (ALL fines - paid and unpaid)
     */
    public function fines()
    {
        $this->requireLogin();
        
        // Redirect Faculty users to their own fines page
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/fines');
            return;
        }
        
        $userId = $_SESSION['userId'];
        
        // Get ALL fines from Transaction model
        $allFines = $this->transactionModel->getFinesByUserId($userId);
        
        // Separate into pending and paid
        $pendingFines = [];
        $paidFines = [];
        
        foreach ($allFines as $fine) {
            $fine['title'] = $fine['bookName'] ?? 'Unknown Book';
            $fine['borrowDate'] = $fine['borrowDate'] ?? date('Y-m-d');
            $fine['fineAmount'] = $fine['fineAmount'] ?? 0;
            $fine['fineStatus'] = $fine['fineStatus'] ?? 'pending';
            
            // Separate by status
            if ($fine['fineStatus'] === 'paid' || $fine['fineStatus'] === 'Paid') {
                $paidFines[] = $fine;
            } else {
                $pendingFines[] = $fine;
            }
        }
        
        // Combine: pending first, then paid
        $this->data['fines'] = array_merge($pendingFines, $paidFines);
        $this->data['pendingFines'] = $pendingFines;
        $this->data['paidFines'] = $paidFines;
        
        $this->view('users/fines', $this->data);
    }

    /**
     * Show payment form for fine payment
     */
    public function showPaymentForm()
    {
        $this->requireLogin();
        
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/fines');
            return;
        }
        
        global $mysqli;
        $userId = $_SESSION['userId'];
        
        // Check if paying all fines or single fine
        $payAll = isset($_GET['pay_all']) && $_GET['pay_all'] === 'true';
        
        if ($payAll) {
            // Get all unpaid fines
            $stmt = $mysqli->prepare("
                SELECT t.*, b.bookName 
                FROM transactions t
                LEFT JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? AND t.fineStatus IN ('pending', 'Unpaid') AND t.fineAmount > 0
            ");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $transactions = [];
            $totalAmount = 0;
            
            while ($row = $result->fetch_assoc()) {
                $transactions[] = $row;
                $totalAmount += (float)$row['fineAmount'];
            }
            $stmt->close();
            
            if (empty($transactions)) {
                $_SESSION['error'] = 'No pending fines to pay';
                $this->redirect('user/fines');
                return;
            }
            
            $this->data['pay_all'] = true;
            $this->data['transactions'] = $transactions;
            $this->data['amount'] = $totalAmount;
        } else {
            // Single transaction payment
            $transactionId = $_GET['tid'] ?? '';
            $amount = $_GET['amount'] ?? 0;
            
            if (!$transactionId || !$amount) {
                $_SESSION['error'] = 'Invalid payment request';
                $this->redirect('user/fines');
                return;
            }
            
            // Verify the transaction belongs to this user
            $stmt = $mysqli->prepare("
                SELECT t.*, b.bookName 
                FROM transactions t
                LEFT JOIN books b ON t.isbn = b.isbn
                WHERE t.tid = ? AND t.userId = ? AND t.fineStatus IN ('pending', 'Unpaid')
            ");
            $stmt->bind_param("ss", $transactionId, $userId);
            $stmt->execute();
            $transaction = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$transaction) {
                $_SESSION['error'] = 'Transaction not found or already paid';
                $this->redirect('user/fines');
                return;
            }
            
            $this->data['pay_all'] = false;
            $this->data['transaction'] = $transaction;
            $this->data['amount'] = $amount;
        }
        
        // Get saved cards
        $stmt = $mysqli->prepare("SELECT * FROM saved_cards WHERE userId = ? ORDER BY isDefault DESC, createdAt DESC");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $savedCards = [];
        while ($row = $result->fetch_assoc()) {
            $savedCards[] = $row;
        }
        $stmt->close();
        
        $this->data['savedCards'] = $savedCards;
        $this->view('users/payment-form', $this->data);
    }

    /**
     * Process fine payment
     */
    public function payFine()
    {
        $this->requireLogin();
        
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/fines');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/fines');
            return;
        }
        
        global $mysqli;
        $userId = $_SESSION['userId'];
        
        // Validate payment details
        $errors = $this->validatePaymentDetails($_POST);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        $payAll = isset($_POST['pay_all']) && $_POST['pay_all'] === 'true';
        $paymentMethod = $_POST['payment_method'] ?? 'credit_card';
        $cardLastFour = isset($_POST['card_number']) ? substr(str_replace(' ', '', $_POST['card_number']), -4) : null;
        $saveCard = isset($_POST['save_card']) && $_POST['save_card'] === '1';
        
        try {
            $mysqli->begin_transaction();
            
            if ($payAll) {
                // Pay all pending fines
                $stmt = $mysqli->prepare("
                    SELECT tid, fineAmount FROM transactions 
                    WHERE userId = ? AND fineStatus IN ('pending', 'Unpaid') AND fineAmount > 0
                ");
                $stmt->bind_param("s", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $totalPaid = 0;
                $transactionIds = [];
                
                while ($row = $result->fetch_assoc()) {
                    $transactionIds[] = $row['tid'];
                    $totalPaid += (float)$row['fineAmount'];
                    
                    // Update transaction
                    $this->transactionModel->payFine($row['tid'], $paymentMethod, $cardLastFour);
                    
                    // Log payment
                    $this->logPayment($userId, $row['tid'], $row['fineAmount'], $paymentMethod, $cardLastFour);
                }
                $stmt->close();
                
                $successMessage = 'Successfully paid ' . count($transactionIds) . ' fines totaling LKR' . number_format($totalPaid, 2);
                
                // Create notification
                $this->createNotification($userId, 'Bulk Fine Payment Successful', $successMessage, 'fine_paid');
                
            } else {
                // Single transaction payment
                $transactionId = $_POST['borrow_id'] ?? $_POST['transaction_id'] ?? '';
                $amount = $_POST['amount'] ?? 0;
                
                // Update transaction
                if ($this->transactionModel->payFine($transactionId, $paymentMethod, $cardLastFour)) {
                    // Log payment
                    $this->logPayment($userId, $transactionId, $amount, $paymentMethod, $cardLastFour);
                    
                    $successMessage = 'Payment of LKR' . number_format($amount, 2) . ' processed successfully!';
                    
                    // Create notification
                    $this->createNotification($userId, 'Fine Payment Successful', $successMessage, 'fine_paid');
                } else {
                    throw new \Exception('Failed to update transaction');
                }
            }
            
            // Save card if requested
            if ($saveCard && isset($_POST['card_number']) && isset($_POST['card_name']) && isset($_POST['expiry_date'])) {
                $this->saveCardDetails($userId, $_POST);
            }
            
            $mysqli->commit();
            $_SESSION['success'] = $successMessage ?? 'Payment processed successfully!';
            
        } catch (\Exception $e) {
            $mysqli->rollback();
            error_log("Error processing payment: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to process payment: ' . $e->getMessage();
        }
        
        $this->redirect('user/fines');
    }
    
    /**
     * Pay all pending fines
     */
    public function payAllFines()
    {
        $_GET['pay_all'] = 'true';
        return $this->showPaymentForm();
    }
    
    /**
     * Validate payment details
     */
    private function validatePaymentDetails($data)
    {
        $errors = [];
        $paymentMethod = $data['payment_method'] ?? 'credit_card';
        
        if ($paymentMethod === 'upi') {
            $upiId = $data['upi_id'] ?? '';
            if (empty($upiId)) {
                $errors[] = 'UPI ID is required';
            } elseif (!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9]+$/', $upiId)) {
                $errors[] = 'Invalid UPI ID format';
            }
        } else {
            // Card validation
            $cardName = $data['card_name'] ?? '';
            $cardNumber = str_replace(' ', '', $data['card_number'] ?? '');
            $expiryDate = $data['expiry_date'] ?? '';
            $cvv = $data['cvv'] ?? '';
            
            if (empty($cardName)) {
                $errors[] = 'Cardholder name is required';
            }
            
            if (empty($cardNumber)) {
                $errors[] = 'Card number is required';
            } elseif (!$this->validateCardNumber($cardNumber)) {
                $errors[] = 'Invalid card number';
            }
            
            if (empty($expiryDate)) {
                $errors[] = 'Expiry date is required';
            } elseif (!$this->validateExpiryDate($expiryDate)) {
                $errors[] = 'Invalid or expired card';
            }
            
            if (empty($cvv)) {
                $errors[] = 'CVV is required';
            } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
                $errors[] = 'Invalid CVV';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate card number using Luhn algorithm
     */
    private function validateCardNumber($number)
    {
        $number = preg_replace('/\s+/', '', $number);
        
        if (!preg_match('/^\d{13,19}$/', $number)) {
            return false;
        }
        
        $sum = 0;
        $length = strlen($number);
        
        for ($i = 0; $i < $length; $i++) {
            $digit = (int)$number[$length - $i - 1];
            
            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }
        
        return ($sum % 10 === 0);
    }
    
    /**
     * Validate expiry date
     */
    private function validateExpiryDate($expiryDate)
    {
        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiryDate, $matches)) {
            return false;
        }
        
        $month = (int)$matches[1];
        $year = (int)$matches[2] + 2000;
        
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        
        if ($year < $currentYear || ($year === $currentYear && $month < $currentMonth)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Log payment
     */
    private function logPayment($userId, $transactionId, $amount, $paymentMethod, $cardLastFour)
    {
        global $mysqli;
        
        $stmt = $mysqli->prepare("
            INSERT INTO payment_logs 
            (userId, transactionId, amount, paymentMethod, cardLastFour, paymentDate, status) 
            VALUES (?, ?, ?, ?, ?, NOW(), 'success')
        ");
        $stmt->bind_param("ssdss", $userId, $transactionId, $amount, $paymentMethod, $cardLastFour);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Create notification
     */
    private function createNotification($userId, $title, $message, $type = 'system')
    {
        global $mysqli;
        
        $stmt = $mysqli->prepare("
            INSERT INTO notifications (userId, title, message, type, priority, createdAt) 
            VALUES (?, ?, ?, ?, 'medium', NOW())
        ");
        $stmt->bind_param("ssss", $userId, $title, $message, $type);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Save card details
     */
    private function saveCardDetails($userId, $data)
    {
        global $mysqli;
        
        $cardNumber = str_replace(' ', '', $data['card_number']);
        $cardLastFour = substr($cardNumber, -4);
        $cardType = $this->detectCardType($cardNumber);
        $cardName = $data['card_name'];
        $expiryDate = $data['expiry_date'];
        $cardNickname = $data['card_nickname'] ?? ($cardType . ' *' . $cardLastFour);
        
        list($expiryMonth, $expiryYear) = explode('/', $expiryDate);
        $expiryYear = '20' . $expiryYear;
        
        // Check if card already exists
        $stmt = $mysqli->prepare("SELECT id FROM saved_cards WHERE userId = ? AND cardLastFour = ?");
        $stmt->bind_param("ss", $userId, $cardLastFour);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return; // Card already saved
        }
        $stmt->close();
        
        $stmt = $mysqli->prepare("
            INSERT INTO saved_cards 
            (userId, cardNickname, cardLastFour, cardType, cardHolderName, expiryMonth, expiryYear) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssss", $userId, $cardNickname, $cardLastFour, $cardType, $cardName, $expiryMonth, $expiryYear);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Detect card type
     */
    private function detectCardType($number)
    {
        $patterns = [
            'visa' => '/^4/',
            'mastercard' => '/^5[1-5]/',
            'rupay' => '/^(60|65|81|82)/',
            'amex' => '/^3[47]/'
        ];
        
        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $number)) {
                return ucfirst($type);
            }
        }
        
        return 'Unknown';
    }

    /**
     * Display user notifications
     */
    public function notifications($params = [])
    {
        error_log("=== NOTIFICATIONS METHOD CALLED ===");
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Request URI: " . $_SERVER['REQUEST_URI']);
        error_log("POST data: " . print_r($_POST, true));
        error_log("Base URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED'));
        
        $this->requireLogin();
        
        // Redirect Faculty users to their own notifications page
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            error_log("Redirecting Faculty user to faculty/notifications");
            $this->redirect('faculty/notifications');
            return;
        }
        
        $userId = $_SESSION['userId'];
        $userType = $_SESSION['userType'] ?? 'Student';
        
        error_log("Processing notifications for user: $userId, type: $userType");
        
        // Handle mark as read BEFORE fetching notifications
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
            $notificationId = $_POST['notification_id'] ?? 0;
            error_log("POST request to mark notification $notificationId as read");
            
            if ($notificationId) {
                global $mysqli;
                
                error_log("Executing UPDATE query for notification $notificationId");
                
                $sql = "UPDATE notifications SET isRead = 1 WHERE id = ? AND userId = ?";
                $stmt = $mysqli->prepare($sql);
                
                if (!$stmt) {
                    error_log("ERROR: Failed to prepare statement: " . $mysqli->error);
                    $_SESSION['error'] = 'Failed to mark notification as read';
                } else {
                    $stmt->bind_param('is', $notificationId, $userId);
                    
                    if ($stmt->execute()) {
                        $affectedRows = $stmt->affected_rows;
                        error_log("✓ UPDATE successful - Affected rows: $affectedRows");
                        
                        if ($affectedRows > 0) {
                            $_SESSION['success'] = 'Notification marked as read';
                            error_log("✓ Notification marked as read successfully");
                        } else {
                            error_log("WARNING: No rows affected - notification may not exist or already read");
                            $_SESSION['info'] = 'Notification already marked as read';
                        }
                    } else {
                        error_log("✗ Failed to execute UPDATE: " . $stmt->error);
                        $_SESSION['error'] = 'Failed to mark notification as read';
                    }
                    $stmt->close();
                }
            } else {
                error_log("ERROR: No notification ID provided in POST");
                $_SESSION['error'] = 'Invalid notification';
            }
            
            error_log("Redirecting back to notifications after marking as read");
            $this->redirect('user/notifications');
            return;
        }
        
        // Get notifications with proper user email and type based on userId
        global $mysqli;
        
        if (!$mysqli) {
            error_log("ERROR: Database connection not available");
            $_SESSION['error'] = 'Database connection error';
            $this->redirect('user/dashboard');
            return;
        }
        
        // Simpler query - get all notifications for this user OR system-wide notifications
        $sql = "SELECT 
                    n.id,
                    n.userId,
                    n.title,
                    n.message,
                    n.type,
                    n.priority,
                    n.isRead,
                    n.relatedId,
                    n.createdAt,
                    u.userType,
                    u.username,
                    u.emailId
                FROM notifications n
                LEFT JOIN users u ON n.userId = u.userId
                WHERE n.userId = ? OR n.userId IS NULL
                ORDER BY n.isRead ASC, n.createdAt DESC";
        
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            error_log("ERROR: Failed to prepare statement: " . $mysqli->error);
            $_SESSION['error'] = 'Failed to load notifications';
            $this->redirect('user/dashboard');
            return;
        }
        
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        $stmt->close();
        
        error_log("Found " . count($notifications) . " notifications for user $userId");
        
        $this->data['notifications'] = $notifications;
        $this->data['userType'] = $userType;
        
        error_log("Loading view: users/notifications");
        
        try {
            $this->view('users/notifications', $this->data);
            error_log("View loaded successfully");
        } catch (\Exception $e) {
            error_log("ERROR loading view: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Mark notification as read - STANDALONE METHOD (not used anymore but kept for compatibility)
     */
    public function markNotificationRead($notificationId = null)
    {
        error_log("=== STANDALONE MARK NOTIFICATION READ CALLED (DEPRECATED) ===");
        error_log("This method is deprecated - use POST to /user/notifications instead");
        
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("ERROR: Not a POST request");
            $this->redirect('user/notifications');
            return;
        }
        
        $notificationId = $_POST['notification_id'] ?? null;
        error_log("Notification ID: " . ($notificationId ?? 'NULL'));
        
        if ($notificationId) {
            global $mysqli;
            $userId = $_SESSION['userId'];
            
            error_log("Marking notification $notificationId as read for user $userId");
            
            $sql = "UPDATE notifications SET isRead = 1 WHERE id = ? AND userId = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('is', $notificationId, $userId);
            
            if ($stmt->execute()) {
                error_log("✓ Notification marked as read successfully");
                $_SESSION['success'] = 'Notification marked as read';
            } else {
                error_log("✗ Failed to mark notification as read: " . $stmt->error);
                $_SESSION['error'] = 'Failed to mark notification as read';
            }
            $stmt->close();
        } else {
            error_log("ERROR: No notification ID provided");
            $_SESSION['error'] = 'Invalid notification';
        }
        
        error_log("Redirecting back to notifications");
        $this->redirect('user/notifications');
    }
    
    /**
     * Reserve a book (send to borrow_requests table)
     */
    public function reserve($params = []) {
        error_log("=== RESERVE METHOD CALLED ===");
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("GET params: " . print_r($_GET, true));
        error_log("POST params: " . print_r($_POST, true));
        error_log("Route params: " . print_r($params, true));
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        
        $this->requireLogin();

        $userId = $_SESSION['userId'];
        error_log("User ID: $userId");
        
        // FIXED: Support both /user/reserve?isbn=... and /user/reserve/{isbn}
        $isbn = $_GET['isbn'] ?? ($params['isbn'] ?? null);
        error_log("ISBN extracted: " . ($isbn ?? 'NULL'));

        if (!$isbn) {
            error_log("ERROR: No ISBN provided");
            $_SESSION['error'] = 'No book specified for reservation';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }

        global $mysqli;
        error_log("Database connection check: " . ($mysqli ? 'OK' : 'FAILED'));

        // Only allow POST for reservation
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("Processing POST reservation request");
            
            // Check borrow limit before allowing reservation
            $privileges = $this->userModel->getUserPrivileges($userId);
            $maxLimit = $privileges['max_borrow_limit'] ?? 3;
            
            // Count active borrows from books_borrowed table
            $borrowCountStmt = $mysqli->prepare("SELECT COUNT(*) as count FROM books_borrowed WHERE userId = ? AND status = 'Active'");
            $borrowCountStmt->bind_param("s", $userId);
            $borrowCountStmt->execute();
            $currentBorrows = (int)($borrowCountStmt->get_result()->fetch_assoc()['count'] ?? 0);
            $borrowCountStmt->close();
            
            // Only count pending requests for books the user does NOT already have borrowed
            $pendingStmt = $mysqli->prepare("SELECT COUNT(*) as count FROM borrow_requests br WHERE br.userId = ? AND br.status = 'Pending' AND NOT EXISTS (SELECT 1 FROM books_borrowed bb WHERE bb.userId = br.userId AND bb.isbn = br.isbn AND bb.status = 'Active')");
            $pendingCount = 0;
            if ($pendingStmt) {
                $pendingStmt->bind_param("s", $userId);
                $pendingStmt->execute();
                $pendingResult = $pendingStmt->get_result()->fetch_assoc();
                $pendingCount = (int)($pendingResult['count'] ?? 0);
                $pendingStmt->close();
            }
            
            if (($currentBorrows + $pendingCount) >= $maxLimit) {
                error_log("User has reached borrow limit: {$currentBorrows} borrowed + {$pendingCount} pending >= {$maxLimit}");
                $_SESSION['error'] = "You have reached your borrowing limit ({$currentBorrows}/{$maxLimit} books borrowed" . ($pendingCount > 0 ? ", {$pendingCount} pending requests" : "") . "). Please return some books before requesting new ones.";
                header('Location: ' . BASE_URL . 'user/books');
                exit;
            }
            
            // Check if already has a pending/approved request for this book
            $stmt = $mysqli->prepare("SELECT * FROM borrow_requests WHERE userId = ? AND isbn = ? AND status IN ('Pending','Approved') AND dueDate >= CURDATE()");
            $stmt->bind_param("ss", $userId, $isbn);
            $stmt->execute();
            $existingCount = $stmt->get_result()->num_rows;
            error_log("Existing requests count: $existingCount");
            
            if ($existingCount > 0) {
                error_log("User already has pending/approved request");
                $_SESSION['error'] = 'You already have a pending or approved request for this book.';
                $stmt->close();
                header('Location: ' . BASE_URL . 'user/books');
                exit;
            }
            $stmt->close();

            // Only allow reservation for 1 day
            $dueDate = date('Y-m-d', strtotime('+1 day'));
            error_log("Due date set to: $dueDate");

            // Insert into borrow_requests
            $stmt = $mysqli->prepare("INSERT INTO borrow_requests (userId, isbn, dueDate, status) VALUES (?, ?, ?, 'Pending')");
            $stmt->bind_param("sss", $userId, $isbn, $dueDate);
            
            if ($stmt->execute()) {
                error_log("✓ Reservation inserted successfully");
                $_SESSION['success'] = 'Reservation request sent! Awaiting admin approval.';
            } else {
                error_log("✗ Failed to insert reservation: " . $stmt->error);
                $_SESSION['error'] = 'Failed to send reservation request.';
            }
            $stmt->close();

            error_log("Redirecting to reserved-books page");
            header('Location: ' . BASE_URL . 'user/reserved-books');
            exit;
        }

        // GET: Show confirmation page
        error_log("Loading reservation confirmation page");
        
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$book) {
            error_log("ERROR: Book not found for ISBN: $isbn");
            $_SESSION['error'] = 'Book not found';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        error_log("Book found: " . print_r($book, true));
        error_log("Loading view: users/reserve");

        $this->data['book'] = $book;
        $this->view('users/reserve', $this->data);
        
        error_log("=== RESERVE METHOD COMPLETED ===");
    }

    /**
     * Show user's reserved books (borrow requests)
     */
    public function reservedBooks() {
        $this->requireLogin();
        global $mysqli;
        $userId = $_SESSION['userId'];

        $sql = "SELECT br.*, b.bookName, b.authorName 
                FROM borrow_requests br
                LEFT JOIN books b ON br.isbn = b.isbn
                WHERE br.userId = ?
                ORDER BY br.requestDate DESC";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();

        $this->data['requests'] = $requests;
        $this->view('users/reserved-books', $this->data);
    }

    /**
     * View book details - FIXED VERSION
     */
    public function viewBook($params = []) {
        error_log("=== VIEW BOOK METHOD CALLED ===");
        error_log("Full URL: " . $_SERVER['REQUEST_URI']);
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("GET params: " . print_r($_GET, true));
        error_log("Route params: " . print_r($params, true));
        
        // SIMPLIFIED AUTHENTICATION - Just check if logged in
        if (!isset($_SESSION['userId'])) {
            error_log("ERROR: User not logged in");
            $_SESSION['error'] = 'Please login to view book details';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $userId = $_SESSION['userId'];
        $userType = $_SESSION['userType'] ?? 'Unknown';
        error_log("User authenticated - ID: $userId, Type: $userType");
        
        // FIXED: Get ISBN from URL parameter or query string
        $isbn = $_GET['isbn'] ?? ($params['isbn'] ?? null);
        error_log("ISBN extracted: " . ($isbn ?? 'NULL'));
        
        if (!$isbn) {
            error_log("ERROR: No ISBN provided");
            $_SESSION['error'] = 'No book specified';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        error_log("Fetching book details for ISBN: $isbn");
        
        global $mysqli;
        error_log("Database connection check: " . ($mysqli ? 'OK' : 'FAILED'));
        
        // Fetch book details
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        $stmt->close();
        
        if (!$book) {
            error_log("ERROR: Book not found for ISBN: $isbn");
            $_SESSION['error'] = 'Book not found';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        error_log("Book found: " . print_r($book, true));
        error_log("Loading view: users/view-book");
        
        // FIXED: Use $this->view() method instead of require_once
        $this->data['book'] = $book;
        $this->view('users/view-book', $this->data);
        
        error_log("=== VIEW BOOK METHOD COMPLETED ===");
    }
    
    /**
     * Show user's borrow history
     */
    public function borrowHistory() {
        $this->requireLogin();
        
        global $mysqli;
        $userId = $_SESSION['userId'];
        
        // Get user's borrow period
        $privileges = $this->userModel->getUserPrivileges($userId);
        $borrowPeriodDays = $privileges['borrow_period_days'] ?? 14;
        
        // Query from books_borrowed table (the primary borrow management table)
        $sql = "SELECT 
                    bb.id,
                    bb.userId as userid,
                    bb.isbn,
                    bb.borrowDate,
                    bb.dueDate,
                    bb.returnDate,
                    bb.status,
                    COALESCE(b.bookName, 'Unknown Book') as bookName,
                    COALESCE(b.authorName, 'Unknown Author') as authorName
                FROM books_borrowed bb
                LEFT JOIN books b ON bb.isbn = b.isbn
                WHERE bb.userId = ?
                ORDER BY bb.borrowDate DESC";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $borrowHistory = [];
        while ($row = $result->fetch_assoc()) {
            $dueDate = $row['dueDate'];
            $status = $row['status'];
            
            // Auto-detect overdue
            if ($status === 'Active' && strtotime($dueDate) < time()) {
                $status = 'Overdue';
            }
            
            // Calculate fine for overdue unreturned books
            $fineAmount = 0;
            $fineStatus = 'Paid';
            
            if (!$row['returnDate'] && strtotime($dueDate) < time()) {
                $daysOverdue = (int)((time() - strtotime($dueDate)) / 86400);
                $fineAmount = $daysOverdue * 5; // LKR5 per day
                $fineStatus = 'Unpaid';
            }
            
            $borrowHistory[] = [
                'id' => $row['id'],
                'isbn' => $row['isbn'],
                'bookName' => $row['bookName'],
                'authorName' => $row['authorName'],
                'borrowDate' => $row['borrowDate'],
                'returnDate' => $row['returnDate'],
                'dueDate' => $dueDate,
                'status' => $status,
                'fineAmount' => $fineAmount,
                'fineStatus' => $fineStatus
            ];
        }
        $stmt->close();
        
        // Add renewal info for active borrows
        foreach ($borrowHistory as &$record) {
            if (!$record['returnDate']) {
                $record['renewalInfo'] = $this->borrowModel->getRenewalInfo($record['id'], $userId);
            }
        }
        
        $this->data['borrowHistory'] = $borrowHistory;
        $this->view('users/borrow-history', $this->data);
    }
    
    /**
     * Submit a book review
     */
    public function submitReview() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/borrow-history');
            return;
        }
        
        global $mysqli;
        $userId = $_SESSION['userId'];
        $isbn = $_POST['isbn'] ?? '';
        $rating = $_POST['rating'] ?? 0;
        $reviewText = $_POST['reviewText'] ?? '';
        
        if (!$isbn || !$rating) {
            $_SESSION['error'] = 'Please provide both ISBN and rating';
            $this->redirect('user/borrow-history');
            return;
        }
        
        // Check if user has borrowed this book from books_borrowed table
        $stmt = $mysqli->prepare("SELECT id FROM books_borrowed WHERE userid = ? AND isbn = ?");
        $stmt->bind_param("ss", $userId, $isbn);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            $_SESSION['error'] = 'You can only review books you have borrowed';
            $stmt->close();
            $this->redirect('user/borrow-history');
            return;
        }
        $stmt->close();
        
        // Check if review already exists
        $stmt = $mysqli->prepare("SELECT id FROM book_reviews WHERE userId = ? AND isbn = ?");
        $stmt->bind_param("ss", $userId, $isbn);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $_SESSION['error'] = 'You have already reviewed this book';
            $stmt->close();
            $this->redirect('user/borrow-history');
            return;
        }
        $stmt->close();
        
        // Insert review
        $stmt = $mysqli->prepare("INSERT INTO book_reviews (userId, isbn, rating, reviewText, createdAt) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssis", $userId, $isbn, $rating, $reviewText);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Thank you for your review!';
        } else {
            $_SESSION['error'] = 'Failed to submit review';
        }
        $stmt->close();
        
        $this->redirect('user/borrow-history');
    }
    
    /**
     * Request renewal of a borrowed book (requires admin approval)
     */
    public function renew()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/borrow-history');
            return;
        }

        global $mysqli;
        $userId = $_SESSION['userId'];
        $tid = trim($_POST['borrow_id'] ?? '');

        if (empty($tid)) {
            $_SESSION['error'] = 'Invalid borrow record.';
            $this->redirect('user/borrow-history');
            return;
        }

        // Ensure renewal_requests table exists
        $mysqli->query("CREATE TABLE IF NOT EXISTS `renewal_requests` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tid` varchar(255) NOT NULL,
            `userId` varchar(255) NOT NULL,
            `isbn` varchar(13) NOT NULL,
            `currentDueDate` date NOT NULL,
            `requestedDueDate` date NOT NULL,
            `reason` text NULL,
            `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
            `adminId` varchar(255) NULL,
            `adminNote` text NULL,
            `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_tid` (`tid`),
            KEY `idx_userId` (`userId`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Get the borrow record from books_borrowed table
        $stmt = $mysqli->prepare("SELECT bb.*, u.borrow_period_days, u.max_renewals 
                                  FROM books_borrowed bb 
                                  JOIN users u ON bb.userId = u.userId 
                                  WHERE bb.id = ? AND bb.userId = ? AND bb.status = 'Active'");
        $stmt->bind_param("is", $tid, $userId);
        $stmt->execute();
        $transaction = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$transaction) {
            $_SESSION['error'] = 'Borrow record not found or book already returned.';
            $this->redirect('user/borrow-history');
            return;
        }

        $borrowPeriod = (int)($transaction['borrow_period_days'] ?? 14);
        $maxRenewals = (int)($transaction['max_renewals'] ?? 1);
        $currentDueDate = $transaction['dueDate'];

        // Check if book is overdue
        if (strtotime($currentDueDate) < time()) {
            $_SESSION['error'] = 'Cannot renew an overdue book. Please return it and pay any fines first.';
            $this->redirect('user/borrow-history');
            return;
        }

        // Check if there's already a pending renewal request for this transaction
        $stmt = $mysqli->prepare("SELECT id FROM renewal_requests WHERE tid = ? AND userId = ? AND status = 'Pending'");
        $stmt->bind_param("ss", $tid, $userId);
        $stmt->execute();
        $existingRequest = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existingRequest) {
            $_SESSION['error'] = 'You already have a pending renewal request for this book. Please wait for admin approval.';
            $this->redirect('user/borrow-history');
            return;
        }

        // Count how many approved renewals this transaction has had
        $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM renewal_requests WHERE tid = ? AND status = 'Approved'");
        $stmt->bind_param("s", $tid);
        $stmt->execute();
        $approvedCount = (int)($stmt->get_result()->fetch_assoc()['count'] ?? 0);
        $stmt->close();

        if ($approvedCount >= $maxRenewals) {
            $_SESSION['error'] = "Maximum renewals reached ({$approvedCount}/{$maxRenewals}). Cannot renew further.";
            $this->redirect('user/borrow-history');
            return;
        }

        // Calculate new requested due date
        $requestedDueDate = date('Y-m-d', strtotime($currentDueDate . " + {$borrowPeriod} days"));
        $reason = trim($_POST['reason'] ?? '');

        // Insert renewal request
        $stmt = $mysqli->prepare("INSERT INTO renewal_requests (tid, userId, isbn, currentDueDate, requestedDueDate, reason, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("ssssss", $tid, $userId, $transaction['isbn'], $currentDueDate, $requestedDueDate, $reason);

        if ($stmt->execute()) {
            // Send notification to admin
            $notifStmt = $mysqli->prepare("INSERT INTO notifications (userId, title, message, type, priority) 
                                           VALUES (NULL, 'Renewal Request', ?, 'renewal', 'medium')");
            $notifMessage = "User {$userId} has requested a renewal for book ISBN: {$transaction['isbn']}. Current due: {$currentDueDate}. Requested new due: {$requestedDueDate}.";
            $notifStmt->bind_param("s", $notifMessage);
            $notifStmt->execute();
            $notifStmt->close();

            $_SESSION['success'] = 'Renewal request submitted successfully! Please wait for admin approval.';
        } else {
            $_SESSION['error'] = 'Failed to submit renewal request. Please try again.';
        }
        $stmt->close();

        $this->redirect('user/borrow-history');
    }

    /**
     * Show all books returned by the user
     */
    public function returns()
    {
        $this->requireLogin();
        
        global $mysqli;
        $userId = $_SESSION['userId'];
        
        $sql = "SELECT 
                    bb.isbn,
                    bb.returnDate,
                    b.bookName,
                    b.authorName
                FROM books_borrowed bb
                LEFT JOIN books b ON bb.isbn = b.isbn
                WHERE bb.userid = ? AND bb.returnDate IS NOT NULL
                ORDER BY bb.returnDate DESC";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $returnedBooks = [];
        while ($row = $result->fetch_assoc()) {
            $returnedBooks[] = $row;
        }
        $stmt->close();
        
        $this->data['returnedBooks'] = $returnedBooks;
        $this->view('users/returns', $this->data);
    }
}
