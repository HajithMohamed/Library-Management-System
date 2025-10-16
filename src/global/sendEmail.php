<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Composer autoload and config
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../config/config.php';

// Base send helper using PHPMailer SMTP
// Returns an associative array: ['success' => bool, 'error' => string]
function sendEmailBase(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): array
{
    $mail = new PHPMailer(true);
    try {
        // SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);
        $sent = $mail->send();
        if ($sent) {
            return ['success' => true, 'error' => ''];
        }
        $errorMsg = $mail->ErrorInfo ?: 'Unknown error from mail()';
        logMailError($toEmail, $subject, $errorMsg);
        return ['success' => false, 'error' => $errorMsg];
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        logMailError($toEmail, $subject, $errorMsg);
        return ['success' => false, 'error' => $errorMsg];
    }
}

// Send OTP email. If $verifyLink is provided, include it; otherwise plain OTP.
// Returns ['success'=>bool,'error'=>string]
function sendOtpEmail(string $toEmail, string $toName, string $otp, string $verifyLink = ''): array
{
    $subject = 'Your verification code';
    if ($verifyLink !== '') {
        $html = '<p>Hello '.htmlspecialchars($toName).',</p>'
              . '<p>Your verification code is: <b>'.htmlspecialchars($otp).'</b></p>'
              . '<p>You can also verify by clicking this link:</p>'
              . '<p><a href="'.htmlspecialchars($verifyLink).'">Verify your account</a></p>';
        $text = "Hello $toName,\nYour verification code is: $otp\nVerify here: $verifyLink";
    } else {
        $html = '<p>Hello '.htmlspecialchars($toName).',</p>'
              . '<p>Your verification code is: <b>'.htmlspecialchars($otp).'</b></p>';
        $text = "Hello $toName,\nYour verification code is: $otp";
    }
    return sendEmailBase($toEmail, $toName, $subject, $html, $text);
}

// Send a generic notification email
// Returns ['success'=>bool,'error'=>string]
function sendNotificationEmail(string $toEmail, string $toName, string $subject, string $messageHtml, string $messageText = ''): array
{
    return sendEmailBase($toEmail, $toName, $subject, $messageHtml, $messageText);
}

// Write failure details to logs/mail.log for debugging
function logMailError(string $toEmail, string $subject, string $error): void
{
    $logDir = __DIR__.'/../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir.'/mail.log';
    $timestamp = date('Y-m-d H:i:s');
    $line = "[$timestamp] to={$toEmail} subject=\"{$subject}\" error={$error}\n";
    @file_put_contents($logFile, $line, FILE_APPEND);
}