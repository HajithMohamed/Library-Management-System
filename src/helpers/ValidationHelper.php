<?php
// filepath: c:\xampp\htdocs\Integrated-Library-System\src\helpers\ValidationHelper.php

namespace App\Helpers;

class ValidationHelper
{
    /**
     * Validate email address
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (9-12 digits, Sri Lankan format)
     */
    public static function validatePhone($phone)
    {
        $phone = preg_replace('/[\s\-]/', '', $phone);
        return preg_match('/^(?:\+94|0)?[1-9]\d{8,11}$/', $phone);
    }
    
    /**
     * Validate password (minimum 6 characters) - basic check
     */
    public static function validatePassword($password)
    {
        return strlen($password) >= 6;
    }

    /**
     * Validate password strength (strong password policy)
     * Returns array of error messages, empty array if valid
     */
    public static function validatePasswordStrength($password, $username = null, $email = null)
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }
        if (!preg_match('/[@$!%*?&#^()_+\-=\[\]{};:\'",.<>\/\\\\|`~]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }
        if ($username && strtolower($password) === strtolower($username)) {
            $errors[] = 'Password cannot be the same as your username.';
        }
        if ($email && strtolower($password) === strtolower($email)) {
            $errors[] = 'Password cannot be the same as your email address.';
        }
        
        return $errors;
    }

    /**
     * Calculate password strength score (0-100)
     */
    public static function getPasswordStrengthScore($password)
    {
        $score = 0;
        $length = strlen($password);
        
        // Length scoring
        if ($length >= 8) $score += 20;
        if ($length >= 12) $score += 10;
        if ($length >= 16) $score += 10;
        
        // Character type scoring
        if (preg_match('/[a-z]/', $password)) $score += 10;
        if (preg_match('/[A-Z]/', $password)) $score += 15;
        if (preg_match('/[0-9]/', $password)) $score += 15;
        if (preg_match('/[@$!%*?&#^()_+\-=\[\]{};:\'",.<>\/\\\\|`~]/', $password)) $score += 20;
        
        return min(100, $score);
    }
    
    /**
     * Validate username (3-20 characters, letters, numbers, underscore)
     */
    public static function validateUsername($username)
    {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
    }
    
    /**
     * Validate date of birth (must be at least 13 years old)
     */
    public static function validateDOB($dob)
    {
        if (empty($dob)) return false;
        
        try {
            $birthDate = new \DateTime($dob);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;
            
            return $age >= 13 && $age <= 120;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Validate not empty
     */
    public static function validateNotEmpty($value)
    {
        return !empty(trim($value));
    }
    
    /**
     * Validate address (minimum 10 characters)
     */
    public static function validateAddress($address)
    {
        return strlen(trim($address)) >= 10;
    }
    
    /**
     * Validate OTP (exactly 6 digits)
     */
    public static function validateOTP($otp)
    {
        return preg_match('/^\d{6}$/', $otp);
    }
    
    /**
     * Validate credit card number (Luhn algorithm)
     */
    public static function validateCreditCard($cardNumber)
    {
        $cardNumber = preg_replace('/\s+/', '', $cardNumber);
        
        if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
            return false;
        }
        
        $sum = 0;
        $isEven = false;
        
        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $digit = intval($cardNumber[$i]);
            
            if ($isEven) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $isEven = !$isEven;
        }
        
        return ($sum % 10) === 0;
    }
    
    /**
     * Validate CVV (3-4 digits)
     */
    public static function validateCVV($cvv)
    {
        return preg_match('/^\d{3,4}$/', $cvv);
    }
    
    /**
     * Validate expiry date (MM/YY format, not expired)
     */
    public static function validateExpiryDate($expiryDate)
    {
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiryDate)) {
            return false;
        }
        
        list($month, $year) = explode('/', $expiryDate);
        
        try {
            $expiry = new \DateTime('20' . $year . '-' . $month . '-01');
            $now = new \DateTime();
            
            return $expiry >= $now;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Sanitize input
     */
    public static function sanitize($input)
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Set validation error in session
     */
    public static function setError($field, $message)
    {
        if (!isset($_SESSION['validation_errors'])) {
            $_SESSION['validation_errors'] = [];
        }
        $_SESSION['validation_errors'][$field] = $message;
    }
    
    /**
     * Set form data in session for repopulation
     */
    public static function setFormData($data)
    {
        // Filter out sensitive fields
        $safeData = $data;
        unset($safeData['password']);
        unset($safeData['confirmPassword']);
        unset($safeData['currentPassword']);
        unset($safeData['current_password']);
        unset($safeData['new_password']);
        unset($safeData['confirm_password']);
        unset($safeData['cvv']);
        unset($safeData['card_number']);
        
        $_SESSION['form_data'] = $safeData;
    }
    
    /**
     * Clear validation errors and form data
     */
    public static function clearValidation()
    {
        unset($_SESSION['validation_errors']);
        unset($_SESSION['form_data']);
    }
    
    /**
     * Validate all signup fields
     */
    public static function validateSignup($data)
    {
        $errors = [];
        
        // Username
        if (!self::validateNotEmpty($data['username'] ?? '')) {
            $errors['username'] = 'Username is required';
        } elseif (!self::validateUsername($data['username'])) {
            $errors['username'] = 'Username must be 3-20 characters (letters, numbers, underscore only)';
        }
        
        // Password
        if (!self::validateNotEmpty($data['password'] ?? '')) {
            $errors['password'] = 'Password is required';
        } elseif (!self::validatePassword($data['password'])) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        // Email
        if (!self::validateNotEmpty($data['emailId'] ?? '')) {
            $errors['emailId'] = 'Email address is required';
        } elseif (!self::validateEmail($data['emailId'])) {
            $errors['emailId'] = 'Please enter a valid email address';
        }
        
        // Phone
        if (!self::validateNotEmpty($data['phoneNumber'] ?? '')) {
            $errors['phoneNumber'] = 'Phone number is required';
        } elseif (!self::validatePhone($data['phoneNumber'])) {
            $errors['phoneNumber'] = 'Please enter a valid phone number';
        }
        
        // Gender
        if (!self::validateNotEmpty($data['gender'] ?? '')) {
            $errors['gender'] = 'Please select your gender';
        }
        
        // DOB
        if (!self::validateNotEmpty($data['dob'] ?? '')) {
            $errors['dob'] = 'Date of birth is required';
        } elseif (!self::validateDOB($data['dob'])) {
            $errors['dob'] = 'You must be at least 13 years old';
        }
        
        // Address
        if (!self::validateNotEmpty($data['address'] ?? '')) {
            $errors['address'] = 'Address is required';
        } elseif (!self::validateAddress($data['address'])) {
            $errors['address'] = 'Address must be at least 10 characters';
        }
        
        return $errors;
    }
    
    /**
     * Validate login fields
     */
    public static function validateLogin($data)
    {
        $errors = [];
        
        if (!self::validateNotEmpty($data['username'] ?? '')) {
            $errors['username'] = 'Username is required';
        }
        
        if (!self::validateNotEmpty($data['password'] ?? '')) {
            $errors['password'] = 'Password is required';
        }
        
        return $errors;
    }
    
    /**
     * Validate profile update fields
     */
    public static function validateProfileUpdate($data)
    {
        $errors = [];
        
        // Email - check both 'email' and 'emailId' for compatibility
        $email = $data['email'] ?? $data['emailId'] ?? '';
        if (!self::validateEmail($email)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        // Phone
        if (!self::validatePhone($data['phoneNumber'] ?? '')) {
            $errors['phoneNumber'] = 'Please enter a valid phone number';
        }
        
        // DOB
        if (!empty($data['dob']) && !self::validateDOB($data['dob'])) {
            $errors['dob'] = 'You must be at least 13 years old';
        }
        
        // Address
        if (!self::validateAddress($data['address'] ?? '')) {
            $errors['address'] = 'Address must be at least 10 characters';
        }
        
        // Gender
        if (!self::validateNotEmpty($data['gender'] ?? '')) {
            $errors['gender'] = 'Please select your gender';
        }
        
        // Name - add validation for name field
        if (!self::validateNotEmpty($data['name'] ?? '')) {
            $errors['name'] = 'Name is required';
        } elseif (strlen(trim($data['name'])) < 3) {
            $errors['name'] = 'Name must be at least 3 characters';
        }
        
        return $errors;
    }
    
    /**
     * Validate password change
     */
    public static function validatePasswordChange($data)
    {
        $errors = [];
        
        if (!self::validateNotEmpty($data['current_password'] ?? '')) {
            $errors['current_password'] = 'Current password is required';
        }
        
        if (!self::validateNotEmpty($data['new_password'] ?? '')) {
            $errors['new_password'] = 'New password is required';
        } elseif (!self::validatePassword($data['new_password'])) {
            $errors['new_password'] = 'New password must be at least 6 characters';
        }
        
        if (!self::validateNotEmpty($data['confirm_password'] ?? '')) {
            $errors['confirm_password'] = 'Please confirm your new password';
        } elseif ($data['new_password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        return $errors;
    }
    
    /**
     * Validate payment details
     */
    public static function validatePayment($data)
    {
        $errors = [];
        $paymentMethod = $data['payment_method'] ?? 'credit_card';
        
        if ($paymentMethod === 'upi') {
            // UPI validation
            if (!self::validateNotEmpty($data['upi_id'] ?? '')) {
                $errors['upi_id'] = 'UPI ID is required';
            } elseif (!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9]+$/', $data['upi_id'])) {
                $errors['upi_id'] = 'Invalid UPI ID format (e.g., username@upi)';
            }
        } else {
            // Card validation
            if (!self::validateNotEmpty($data['card_name'] ?? '')) {
                $errors['card_name'] = 'Cardholder name is required';
            } elseif (strlen(trim($data['card_name'])) < 3) {
                $errors['card_name'] = 'Cardholder name must be at least 3 characters';
            }
            
            $cardNumber = str_replace(' ', '', $data['card_number'] ?? '');
            if (!self::validateNotEmpty($cardNumber)) {
                $errors['card_number'] = 'Card number is required';
            } elseif (!self::validateCreditCard($cardNumber)) {
                $errors['card_number'] = 'Invalid card number (fails Luhn check)';
            }
            
            if (!self::validateNotEmpty($data['expiry_date'] ?? '')) {
                $errors['expiry_date'] = 'Expiry date is required';
            } elseif (!self::validateExpiryDate($data['expiry_date'])) {
                $errors['expiry_date'] = 'Card has expired or invalid date format';
            }
            
            if (!self::validateNotEmpty($data['cvv'] ?? '')) {
                $errors['cvv'] = 'CVV is required';
            } elseif (!self::validateCVV($data['cvv'])) {
                $errors['cvv'] = 'CVV must be 3-4 digits';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate forgot password email
     */
    public static function validateForgotPassword($data)
    {
        $errors = [];
        
        if (!self::validateNotEmpty($data['email'] ?? '')) {
            $errors['email'] = 'Email address is required';
        } elseif (!self::validateEmail($data['email'])) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        return $errors;
    }
    
    /**
     * Validate OTP verification
     */
    public static function validateOTPVerification($data)
    {
        $errors = [];
        
        if (!self::validateNotEmpty($data['otp'] ?? '')) {
            $errors['otp'] = 'OTP is required';
        } elseif (!self::validateOTP($data['otp'])) {
            $errors['otp'] = 'OTP must be exactly 6 digits';
        }
        
        return $errors;
    }
    
    /**
     * Validate password reset
     */
    public static function validatePasswordReset($data)
    {
        $errors = [];
        
        if (!self::validateNotEmpty($data['password'] ?? '')) {
            $errors['password'] = 'Password is required';
        } elseif (!self::validatePassword($data['password'])) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if (!self::validateNotEmpty($data['confirmPassword'] ?? '')) {
            $errors['confirmPassword'] = 'Please confirm your password';
        } elseif ($data['password'] !== $data['confirmPassword']) {
            $errors['confirmPassword'] = 'Passwords do not match';
        }
        
        return $errors;
    }
}