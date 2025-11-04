<?php
session_start();
include '../../../config/config.php';
include DIR_URL.'config/dbConnection.php';

// Check if user is logged in
if (empty($_SESSION['userId'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$message = '';
$userId = $_SESSION['userId'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = '<div class="alert alert-danger">Please fill all fields</div>';
    } elseif ($newPassword !== $confirmPassword) {
        $message = '<div class="alert alert-danger">New passwords do not match</div>';
    } elseif (strlen($newPassword) < 6) {
        $message = '<div class="alert alert-danger">Password must be at least 6 characters</div>';
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE userId = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($currentPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE userId = ?");
            $stmt->bind_param("ss", $hashedPassword, $userId);
            if ($stmt->execute()) {
                $stmt->close();
                // Log the password change
                $action_log = "Password changed";
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                $conn->query("INSERT INTO audit_logs(userId, action, ipAddress, userAgent) VALUES('$userId', '$action_log', '$ip', '$userAgent')");
                $message = '<div class="alert alert-success">Password changed successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error changing password. Please try again.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Current password is incorrect</div>';
        }
    }
}

$stmt = $conn->prepare("SELECT name FROM users WHERE userId = ?");
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Library System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.15);
            max-width: 450px;
            width: 100%;
            padding: 40px;
            margin: 20px;
        }
        h2 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 28px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .user-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .user-info p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .user-info strong {
            color: #667eea;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: box-shadow 0.3s, transform 0.2s;
            margin-top: 10px;
        }
        .btn:hover {
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            box-shadow: 0 4px 16px rgba(108, 117, 125, 0.4);
        }
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-group .btn {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fa-solid fa-key"></i> Change Password</h2>
        <p class="subtitle">Update your account password</p>

        <div class="user-info">
            <p><strong><?php echo htmlspecialchars($userData['username'] ?? 'User'); ?></strong></p>
            <p>User ID: <strong><?php echo htmlspecialchars($userId); ?></strong></p>
        </div>

        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label for="currentPassword"><i class="fa-solid fa-lock"></i> Current Password</label>
                <input type="password" id="currentPassword" name="currentPassword" placeholder="Enter your current password" required>
            </div>

            <div class="form-group">
                <label for="newPassword"><i class="fa-solid fa-lock"></i> New Password</label>
                <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
            </div>

            <div class="form-group">
                <label for="confirmPassword"><i class="fa-solid fa-lock"></i> Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your new password" required>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Change Password</button>
                <a href="<?php echo BASE_URL;?>src/admin/adminDashboard.php" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">
                    <button type="button" class="btn btn-secondary" style="width: 100%; margin: 0;"><i class="fa-solid fa-times"></i> Cancel</button>
                </a>
            </div>
        </form>
    </div>

    <script src="<?php echo BASE_URL;?>assets/js/form-validation.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const currentPassword = document.getElementById('currentPassword');
        const newPassword = document.getElementById('newPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        
        // Validate passwords
        newPassword.addEventListener('blur', function() {
            if (this.value && !validatePassword(this.value)) {
                showError(this, 'Password must be at least 6 characters');
            } else {
                clearError(this);
            }
        });
        
        confirmPassword.addEventListener('blur', function() {
            if (this.value && this.value !== newPassword.value) {
                showError(this, 'Passwords do not match');
            } else {
                clearError(this);
            }
        });
        
        // Form submit validation
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear all errors
            this.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
                el.style.borderColor = '';
            });
            this.querySelectorAll('.error-message').forEach(el => el.remove());
            
            if (!validateNotEmpty(currentPassword.value)) {
                showError(currentPassword, 'Current password is required');
                isValid = false;
            }
            
            if (!validatePassword(newPassword.value)) {
                showError(newPassword, 'New password must be at least 6 characters');
                isValid = false;
            }
            
            if (newPassword.value !== confirmPassword.value) {
                showError(confirmPassword, 'Passwords do not match');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                
                const firstError = this.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    });
    </script>
</body>
</html>
