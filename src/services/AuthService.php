<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthService
{
  /**
   * Send OTP email to user
   * 
   * @param string $email Recipient email
   * @param string $otp OTP code
   * @return bool Success status
   */
  public function sendOTPEmail($email, $otp)
  {
    $subject = "Email Verification - Library System";
    $body = "Your OTP for email verification is: {$otp}\n\n";
    $body .= "This OTP is valid for 15 minutes.\n\n";
    $body .= "If you didn't request this, please ignore this email.\n\n";
    $body .= "Best regards,\nLibrary Management System";

    return $this->sendEmail($email, $subject, $body);
  }

  /**
   * Generic email sending method using PHPMailer
   * 
   * @param string $to Recipient email address
   * @param string $subject Email subject
   * @param string $body Email body (plain text)
   * @return bool Success status
   */
  public function sendEmail($to, $subject, $body)
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
      error_log("Email sent successfully to: {$to}");
      return true;
    } catch (Exception $e) {
      error_log("Email sending failed to {$to}: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Send password reset email
   * 
   * @param string $email Recipient email
   * @param string $resetToken Reset token
   * @return bool Success status
   */
  public function sendPasswordResetEmail($email, $resetToken)
  {
    $resetLink = BASE_URL . "reset-password?token={$resetToken}";
    
    $subject = "Password Reset Request - Library System";
    $body = "You have requested to reset your password.\n\n";
    $body .= "Click the link below to reset your password:\n";
    $body .= "{$resetLink}\n\n";
    $body .= "This link is valid for 1 hour.\n\n";
    $body .= "If you didn't request this, please ignore this email.\n\n";
    $body .= "Best regards,\nLibrary Management System";

    return $this->sendEmail($email, $subject, $body);
  }

  /**
   * Send welcome email to new user
   * 
   * @param string $email Recipient email
   * @param string $username Username
   * @return bool Success status
   */
  public function sendWelcomeEmail($email, $username)
  {
    $subject = "Welcome to Library System";
    $body = "Dear {$username},\n\n";
    $body .= "Welcome to our Library Management System!\n\n";
    $body .= "Your account has been successfully created.\n\n";
    $body .= "You can now login at: " . BASE_URL . "\n\n";
    $body .= "Best regards,\nLibrary Management System";

    return $this->sendEmail($email, $subject, $body);
  }

  /**
   * Send notification email
   * 
   * @param string $email Recipient email
   * @param string $title Notification title
   * @param string $message Notification message
   * @return bool Success status
   */
  public function sendNotificationEmail($email, $title, $message)
  {
    $subject = $title . " - Library System";
    $body = $message . "\n\n";
    $body .= "Best regards,\nLibrary Management System";

    return $this->sendEmail($email, $subject, $body);
  }
}
