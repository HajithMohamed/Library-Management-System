<?php

namespace App\Middleware;

class SecurityMiddleware
{
    /**
     * Set security headers
     */
    public static function setSecurityHeaders()
    {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        // Enable XSS protection filter (deprecated in modern browsers but good for older)
        header('X-XSS-Protection: 1; mode=block');
        // HSTS (HTTP Strict Transport Security) - 1 year
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        // Referrer policy
        header('Referrer-Policy: no-referrer-when-downgrade');
        // Content Security Policy (Basic starter policy, adjust as needed)
        // strict-dynamic requires a nonce, we will use a loose policy for now to not break inline scripts/styles immediately
        // Ideally we should use nonces.
        header("Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'; img-src 'self' data: https:;");
    }

    /**
     * Sanitize input
     */
    public static function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
