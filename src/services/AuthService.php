<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class AuthService
{
    /**
     * Send OTP email
     */
    public function sendOTPEmail($email, $otp)
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
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification - University Library';
            $mail->Body = $this->getOTPEmailTemplate($otp);
            $mail->AltBody = "Your verification code is: {$otp}. This code will expire in 15 minutes.";
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $resetToken)
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
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset - University Library';
            $mail->Body = $this->getPasswordResetEmailTemplate($resetToken);
            $mail->AltBody = "Click the following link to reset your password: " . BASE_URL . "reset-password?token={$resetToken}";
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($email, $userId)
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
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to University Library';
            $mail->Body = $this->getWelcomeEmailTemplate($userId);
            $mail->AltBody = "Welcome to University Library! Your account has been verified successfully.";
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Generate secure random token
     */
    public function generateSecureToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate OTP
     */
    public function generateOTP($length = 6)
    {
        return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Validate email format
     */
    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check password strength
     */
    public function checkPasswordStrength($password)
    {
        $strength = 0;
        $feedback = [];

        if (strlen($password) >= 8) {
            $strength++;
        } else {
            $feedback[] = 'Password should be at least 8 characters long';
        }

        if (preg_match('/[A-Z]/', $password)) {
            $strength++;
        } else {
            $feedback[] = 'Password should contain at least one uppercase letter';
        }

        if (preg_match('/[a-z]/', $password)) {
            $strength++;
        } else {
            $feedback[] = 'Password should contain at least one lowercase letter';
        }

        if (preg_match('/[0-9]/', $password)) {
            $strength++;
        } else {
            $feedback[] = 'Password should contain at least one number';
        }

        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $strength++;
        } else {
            $feedback[] = 'Password should contain at least one special character';
        }

        return [
            'strength' => $strength,
            'feedback' => $feedback,
            'isStrong' => $strength >= 4
        ];
    }

    /**
     * Get OTP email template
     */
    private function getOTPEmailTemplate($otp)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Email Verification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .otp-code { font-size: 24px; font-weight: bold; color: #007bff; text-align: center; padding: 20px; background-color: white; border: 2px dashed #007bff; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>University Library</h1>
                    <h2>Email Verification</h2>
                </div>
                <div class='content'>
                    <p>Thank you for registering with University Library Management System!</p>
                    <p>Please use the following verification code to complete your registration:</p>
                    <div class='otp-code'>{$otp}</div>
                    <p><strong>Important:</strong> This code will expire in 15 minutes.</p>
                    <p>If you didn't create an account with us, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " University Library. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get password reset email template
     */
    private function getPasswordResetEmailTemplate($resetToken)
    {
        $resetLink = BASE_URL . "reset-password?token={$resetToken}";
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Password Reset</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .reset-button { display: inline-block; padding: 12px 24px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>University Library</h1>
                    <h2>Password Reset Request</h2>
                </div>
                <div class='content'>
                    <p>We received a request to reset your password for your University Library account.</p>
                    <p>Click the button below to reset your password:</p>
                    <a href='{$resetLink}' class='reset-button'>Reset Password</a>
                    <p><strong>Important:</strong> This link will expire in 1 hour.</p>
                    <p>If you didn't request a password reset, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " University Library. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get welcome email template
     */
    private function getWelcomeEmailTemplate($userId)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Welcome to University Library</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>University Library</h1>
                    <h2>Welcome!</h2>
                </div>
                <div class='content'>
                    <p>Dear {$userId},</p>
                    <p>Welcome to the University Library Management System!</p>
                    <p>Your account has been successfully verified and you can now access all library services.</p>
                    <p>You can now:</p>
                    <ul>
                        <li>Browse and search books</li>
                        <li>Borrow books</li>
                        <li>View your borrowing history</li>
                        <li>Manage your fines</li>
                    </ul>
                    <p>Thank you for joining our library community!</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " University Library. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
?>
