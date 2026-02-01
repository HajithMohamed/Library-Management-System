<?php
/**
 * Credentials Configuration Template
 * 
 * IMPORTANT: This is a template file. DO NOT add real credentials here.
 * 
 * SETUP INSTRUCTIONS:
 * 1. Copy this file to 'credentials.php' in the same directory
 * 2. Fill in your actual credentials in credentials.php
 * 3. Never commit credentials.php to version control (.gitignore will prevent this)
 * 
 * This file provides an alternative to .env for storing sensitive credentials.
 * Use this if you prefer PHP-based configuration over environment variables.
 */

// =========================
// Database Credentials
// =========================
// Primary database connection details
define('DB_HOST', 'db');                           // Database host (use 'db' for Docker, 'localhost' for local)
define('DB_PORT', '3306');                         // MySQL port
define('DB_USER', 'library_user');                 // Database username
define('DB_PASSWORD', 'your_secure_password_here'); // Database password - CHANGE THIS!
define('DB_NAME', 'integrated_library_system');   // Database name

// =========================
// SMTP / Email Configuration
// =========================
// Email server settings for sending notifications, OTP, etc.
define('SMTP_HOST', 'smtp.gmail.com');             // SMTP server host
define('SMTP_PORT', '587');                        // SMTP port (587 for TLS, 465 for SSL)
define('SMTP_USERNAME', 'your_email@gmail.com');   // SMTP username - CHANGE THIS!
define('SMTP_PASSWORD', 'your_app_password_here'); // SMTP password or app password - CHANGE THIS!
define('SMTP_ENCRYPTION', 'tls');                  // Encryption type: 'tls' or 'ssl'
define('SMTP_FROM_EMAIL', 'noreply@library.com');  // From email address
define('SMTP_FROM_NAME', 'Library Management System'); // From name

// Gmail Users: Generate an App Password at https://myaccount.google.com/apppasswords
// Requires 2FA to be enabled on your Google account

// =========================
// Admin Configuration
// =========================
define('ADMIN_CODE', 'your_admin_code_here');      // Admin registration code - CHANGE THIS!

// =========================
// Cloudinary Configuration (Optional)
// =========================
// Used for image upload and management
// Sign up at https://cloudinary.com/users/register/free
define('CLOUDINARY_CLOUD_NAME', 'your_cloud_name');   // CHANGE THIS!
define('CLOUDINARY_API_KEY', 'your_api_key');         // CHANGE THIS!
define('CLOUDINARY_API_SECRET', 'your_api_secret');   // CHANGE THIS!

// =========================
// SMS Gateway Configuration (Optional)
// =========================
// Used for SMS notifications (leave blank to disable)
define('SMS_API_URL', '');                         // SMS provider API endpoint
define('SMS_API_KEY', '');                         // SMS provider API key
define('SMS_SENDER_ID', '');                       // SMS sender ID

// =========================
// Application Settings
// =========================
define('APP_DEBUG', 'false');                      // Set to 'true' only in development
define('OTP_EXPIRY_MINUTES', '15');                // OTP expiration time in minutes
date_default_timezone_set('Asia/Kolkata');         // Application timezone

// =========================
// Security Recommendations
// =========================
/*
 * 1. Use strong, unique passwords (minimum 16 characters with mixed case, numbers, symbols)
 * 2. Never commit credentials.php to version control
 * 3. Set file permissions: chmod 600 credentials.php (owner read/write only)
 * 4. Rotate credentials regularly (every 90 days recommended)
 * 5. Use different credentials for development, staging, and production
 * 6. For production, consider using environment variables (.env) instead
 * 7. Enable 2FA on all external services (email, Cloudinary, SMS provider)
 */
?>