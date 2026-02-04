<?php
/**
 * Security Class
 * Handles security features like CSRF protection
 */
class Security
{
    /**
     * Generate CSRF token
     * Creates a new token if one doesn't exist in the session
     * 
     * @return string CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Get CSRF token field HTML
     * Returns a hidden input field with the CSRF token
     * 
     * @return string HTML input field
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate CSRF token
     * Compares the token from POST data with the session token
     * 
     * @return bool True if valid, false otherwise
     */
    public static function validateCsrfToken(): bool
    {
        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Use hash_equals to prevent timing attacks
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }

    /**
     * Require valid CSRF token or terminate
     * Validates the CSRF token and terminates with 403 if invalid
     * 
     * @return void
     */
    public static function requireCsrfToken(): void
    {
        if (!self::validateCsrfToken()) {
            http_response_code(403);
            die('Invalid CSRF token. Please refresh the page and try again.');
        }
    }

    /**
     * Sanitize string input
     * Removes HTML tags and special characters
     * 
     * @param string $input Input string
     * @return string Sanitized string
     */
    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Start a secure session with proper security settings
     * 
     * Configures session to prevent:
     * - Session fixation attacks
     * - Session hijacking
     * - JavaScript access to session cookies
     * - Cross-site session attacks
     * 
     * @return void
     */
    public static function startSecureSession(): void
    {
        // Prevent session start if already started
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Configure secure session settings
        ini_set('session.cookie_httponly', '1');     // Prevent JavaScript access to session cookie
        ini_set('session.use_only_cookies', '1');    // Only use cookies, never URL parameters
        ini_set('session.use_strict_mode', '1');     // Reject uninitialized session IDs
        ini_set('session.cookie_samesite', 'Strict'); // Prevent CSRF via cookies
        
        // Note: session.sid_length and session.sid_bits_per_character are deprecated in PHP 8.1+
        // Modern PHP versions already use secure defaults (128 bits of entropy)
        
        // In production with HTTPS, enable secure flag
        $isProduction = getenv('APP_ENV') === 'production';
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        
        if ($isProduction || $isHttps) {
            ini_set('session.cookie_secure', '1');   // Only send cookie over HTTPS
        }
        
        // Set session name (avoid default PHPSESSID which reveals technology)
        session_name('MOCKSTORE_SESSID');
        
        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => 0,              // Expire when browser closes
            'path' => '/',
            'domain' => '',
            'secure' => $isProduction || $isHttps,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        // Start the session
        session_start();
        
        // Regenerate session ID if this is a new session
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
            $_SESSION['created_at'] = time();
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        }
        
        // Validate session integrity
        self::validateSession();
        
        // Regenerate session ID periodically (every 30 minutes)
        if (isset($_SESSION['last_regeneration'])) {
            if (time() - $_SESSION['last_regeneration'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        } else {
            $_SESSION['last_regeneration'] = time();
        }
    }

    /**
     * Validate session integrity
     * Checks for session hijacking attempts
     * 
     * @return void
     */
    private static function validateSession(): void
    {
        // Check if IP address changed (potential hijacking)
        $currentIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $currentIp) {
            // IP changed - possible session hijacking
            // For now, just log it. In production, you might want to destroy the session
            error_log("Session IP mismatch: Expected {$_SESSION['user_ip']}, got {$currentIp}");
        }
        
        // Check if user agent changed (potential hijacking)
        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $currentUserAgent) {
            error_log("Session User-Agent mismatch");
        }
        
        // Check session age (destroy sessions older than 24 hours)
        if (isset($_SESSION['created_at'])) {
            $sessionAge = time() - $_SESSION['created_at'];
            if ($sessionAge > 86400) { // 24 hours
                session_unset();
                session_destroy();
                session_start();
                $_SESSION['initiated'] = true;
                $_SESSION['created_at'] = time();
                $_SESSION['user_ip'] = $currentIp;
                $_SESSION['user_agent'] = $currentUserAgent;
            }
        }
    }

    /**
     * Regenerate session ID (call after login or privilege escalation)
     * 
     * @return void
     */
    public static function regenerateSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    /**
     * Destroy session securely
     * 
     * @return void
     */
    public static function destroySession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            
            // Delete the session cookie
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
            
            session_destroy();
        }
    }

    /**
     * Set HTTP security headers
     * 
     * Configures security headers to protect against:
     * - Clickjacking (X-Frame-Options)
     * - MIME sniffing attacks (X-Content-Type-Options)
     * - XSS attacks (X-XSS-Protection)
     * - Information leakage (Referrer-Policy)
     * - Various injection attacks (Content-Security-Policy)
     * - Feature abuse (Permissions-Policy)
     * - Man-in-the-middle attacks (Strict-Transport-Security in production)
     * 
     * @return void
     */
    public static function setSecurityHeaders(): void
    {
        // Prevent clickjacking by disallowing the page to be framed
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS filter in browsers (legacy browsers)
        header('X-XSS-Protection: 1; mode=block');
        
        // Control referrer information
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy - Define allowed sources for content
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "img-src 'self' https://images.unsplash.com data: https:",
            "font-src 'self' https://cdn.jsdelivr.net data:",
            "connect-src 'self' https://checkout-test.placetopay.com https://api-test.placetopay.com",
            "frame-src https://checkout-test.placetopay.com",
            "form-action 'self' https://checkout-test.placetopay.com",
            "base-uri 'self'",
            "object-src 'none'",
            "upgrade-insecure-requests"
        ]);
        header("Content-Security-Policy: $csp");
        
        // Permissions Policy - Restrict browser features
        $permissions = implode(', ', [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'accelerometer=()',
            'gyroscope=()'
        ]);
        header("Permissions-Policy: $permissions");
        
        // Check if production and HTTPS
        $isProduction = getenv('APP_ENV') === 'production';
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        
        // Strict Transport Security (HSTS) - Only for HTTPS in production
        if ($isProduction && $isHttps) {
            // Force HTTPS for 1 year, including subdomains
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Additional security headers
        header('X-Powered-By: MockStore'); // Hide PHP version
        header('X-Content-Type-Options: nosniff'); // Prevent MIME confusion attacks
    }
}
