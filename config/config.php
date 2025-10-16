<?php
if ($_SERVER['HTTP_HOST']=='localhost') // if the website is hosted on local server, i.e., localhost
{
     define("BASE_URL","http://localhost/Integrated-Library-System/");
     define("DIR_URL",$_SERVER['DOCUMENT_ROOT']."/Integrated-Library-System/");
}
else // if the website is hosted on remote server
{
     define("BASE_URL","");
     define("DIR_URL",$_SERVER['DOCUMENT_ROOT']);
}

define("ADMIN_CODE","hello_world");
// Replace the Admin Registration Code above in place of hello_world to register as an admin in the Library

date_default_timezone_set('Asia/Kolkata');
// Change the default timezone above if you want the app to run in a different time zone

define("DB_PORT","3307");
// Replace the 3306 above to change the connection port if your MySQL is running on a different port

define("DB_HOST", "localhost");
// Replace the localhost above to change the connection host if your MySQL is not running on local server

define("DB_USER", "root");
// Replace the root above to change the MySQL username

define("DB_PASSWORD", "");
// Type the password above inside the empty quotes if there is a password asscoiated with the user

define("DB_NAME", "integrated_library_system");
// Replace the lms above with some other name if you want the database name to be something else

// Outbound Email via PHPMailer (SMTP)
define("SMTP_HOST", "smtp.gmail.com");
define("SMTP_PORT", 587);
define("SMTP_USERNAME", "your full Gmail address"); // e.g., your full Gmail address
define("SMTP_PASSWORD", "Gmail App Password");  // e.g., Gmail App Password
define("SMTP_ENCRYPTION", "tls"); // ssl or tls
define("SMTP_FROM_EMAIL", "your full Gmail address");
define("SMTP_FROM_NAME", "Library managemene system university of Ruhuna");

// SMS Gateway placeholders (used for OTP link to mobile). Leave blank to disable
define("SMS_API_URL", ""); // e.g., https://api.textlocal.in/send/
define("SMS_API_KEY", "");
define("SMS_SENDER_ID", "");
?>

