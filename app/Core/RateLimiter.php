<?php
/**
 * Rate Limiter Class
 * Prevents brute force attacks, API abuse, and DDoS
 */
class RateLimiter
{
    private static string $logPath = '';

    /**
     * Initialize rate limiter
     * 
     * @param array $config Application configuration
     */
    public static function init(array $config): void
    {
        self::$logPath = BASE_PATH . '/logs/rate_limits/';
        
        // Create rate limits directory if it doesn't exist
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
    }

    /**
     * Check if request is within rate limit
     * 
     * @param string $key Identifier for rate limit (e.g., IP address, user ID)
     * @param int $limit Maximum number of requests allowed
     * @param int $seconds Time window in seconds
     * @param string $action Optional action identifier for separate limits per action
     * @return bool True if within limit, false if exceeded
     */
    public static function check(string $key, int $limit, int $seconds, string $action = 'default'): bool
    {
        // Ensure directory exists
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }

        // Sanitize key and action for filename
        $safeKey = md5($key);
        $safeAction = preg_replace('/[^a-z0-9_-]/i', '', $action);
        $file = self::$logPath . "{$safeAction}_{$safeKey}.json";
        
        $now = time();
        $windowStart = $now - $seconds;
        
        // Read existing requests
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if (!is_array($data)) {
                $data = [];
            }
            
            // Filter out requests outside the time window
            $data = array_filter($data, fn($timestamp) => $timestamp > $windowStart);
        } else {
            $data = [];
        }
        
        // Check if limit exceeded
        if (count($data) >= $limit) {
            // Log rate limit exceeded
            self::logRateLimitExceeded($key, $action, $limit, $seconds);
            return false;
        }
        
        // Add current request
        $data[] = $now;
        
        // Save updated data
        file_put_contents($file, json_encode($data));
        
        return true;
    }

    /**
     * Check rate limit and respond with 429 if exceeded
     * 
     * @param string $key Identifier for rate limit
     * @param int $limit Maximum number of requests
     * @param int $seconds Time window in seconds
     * @param string $action Action identifier
     * @param string|null $message Optional custom message
     */
    public static function enforce(string $key, int $limit, int $seconds, string $action = 'default', ?string $message = null): void
    {
        if (!self::check($key, $limit, $seconds, $action)) {
            http_response_code(429);
            header('Retry-After: ' . $seconds);
            header('Content-Type: application/json');
            
            $defaultMessage = "Rate limit exceeded. Maximum $limit requests per $seconds seconds. Please try again later.";
            $response = [
                'error' => $message ?? $defaultMessage,
                'retry_after' => $seconds
            ];
            
            echo json_encode($response);
            exit;
        }
    }

    /**
     * Get client identifier (IP address)
     * 
     * @return string Client IP address
     */
    public static function getClientIdentifier(): string
    {
        // Check for proxy headers
        $headers = [
            'HTTP_CF_CONNECTING_IP',  // Cloudflare
            'HTTP_X_FORWARDED_FOR',   // Standard proxy header
            'HTTP_X_REAL_IP',         // Nginx proxy
            'REMOTE_ADDR'             // Direct connection
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle comma-separated IPs (X-Forwarded-For can have multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0'; // Fallback
    }

    /**
     * Clear rate limit for a specific key
     * 
     * @param string $key Identifier to clear
     * @param string $action Action identifier
     */
    public static function clear(string $key, string $action = 'default'): void
    {
        $safeKey = md5($key);
        $safeAction = preg_replace('/[^a-z0-9_-]/i', '', $action);
        $file = self::$logPath . "{$safeAction}_{$safeKey}.json";
        
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Clean up old rate limit files
     * Call this periodically (e.g., daily cron job)
     * 
     * @param int $olderThanSeconds Remove files older than this many seconds
     */
    public static function cleanup(int $olderThanSeconds = 86400): void
    {
        if (!is_dir(self::$logPath)) {
            return;
        }
        
        $now = time();
        $files = glob(self::$logPath . '*.json');
        
        foreach ($files as $file) {
            if (is_file($file) && (filemtime($file) < $now - $olderThanSeconds)) {
                unlink($file);
            }
        }
    }

    /**
     * Log rate limit exceeded event
     * 
     * @param string $key Client identifier
     * @param string $action Action that was rate limited
     * @param int $limit Limit that was exceeded
     * @param int $seconds Time window
     */
    private static function logRateLimitExceeded(string $key, string $action, int $limit, int $seconds): void
    {
        $logFile = BASE_PATH . '/logs/rate_limit_exceeded.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $uri = $_SERVER['REQUEST_URI'] ?? 'N/A';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
        
        $logMessage = sprintf(
            "[%s] RATE_LIMIT_EXCEEDED | Key: %s | Action: %s | Limit: %d/%ds | URI: %s | User-Agent: %s\n",
            $timestamp,
            $key,
            $action,
            $limit,
            $seconds,
            $uri,
            substr($userAgent, 0, 100)
        );
        
        error_log($logMessage, 3, $logFile);
    }

    /**
     * Get remaining requests for a key
     * 
     * @param string $key Identifier
     * @param int $limit Maximum requests
     * @param int $seconds Time window
     * @param string $action Action identifier
     * @return int Number of remaining requests
     */
    public static function remaining(string $key, int $limit, int $seconds, string $action = 'default'): int
    {
        $safeKey = md5($key);
        $safeAction = preg_replace('/[^a-z0-9_-]/i', '', $action);
        $file = self::$logPath . "{$safeAction}_{$safeKey}.json";
        
        if (!file_exists($file)) {
            return $limit;
        }
        
        $now = time();
        $windowStart = $now - $seconds;
        
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) {
            return $limit;
        }
        
        // Filter to current window
        $data = array_filter($data, fn($timestamp) => $timestamp > $windowStart);
        
        return max(0, $limit - count($data));
    }
}
