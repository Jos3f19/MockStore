<?php
/**
 * Error Handler Class
 * Handles errors and exceptions securely without information disclosure
 */
class ErrorHandler
{
    private static bool $isProduction = false;
    private static string $logPath = '';

    /**
     * Initialize error handler
     * 
     * @param array $config Application configuration
     */
    public static function init(array $config): void
    {
        self::$isProduction = ($config['app']['env'] ?? 'development') === 'production';
        self::$logPath = BASE_PATH . '/logs/error.log';

        // Configure error display based on environment
        if (self::$isProduction) {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            error_reporting(0);
        } else {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
        }

        // Set custom error and exception handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP errors
     * 
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile Error file
     * @param int $errline Error line
     * @return bool
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Don't handle suppressed errors (@)
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorType = self::getErrorType($errno);
        
        // Log the error
        self::logError($errorType, $errstr, $errfile, $errline);

        // In production, don't display error details
        if (self::$isProduction) {
            // Only show generic message for fatal errors
            if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
                self::displayGenericError();
                exit(1);
            }
            return true;
        }

        // In development, let PHP display the error
        return false;
    }

    /**
     * Handle uncaught exceptions
     * 
     * @param Throwable $exception
     */
    public static function handleException(Throwable $exception): void
    {
        // Log the exception
        self::logException($exception);

        // Set appropriate HTTP status code
        $statusCode = $exception->getCode() >= 400 && $exception->getCode() < 600 
            ? $exception->getCode() 
            : 500;
        http_response_code($statusCode);

        if (self::$isProduction) {
            // Production: Show generic error page
            self::displayGenericError();
        } else {
            // Development: Show detailed error
            self::displayDetailedError($exception);
        }
        
        exit(1);
    }

    /**
     * Handle fatal errors during shutdown
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::logError(
                self::getErrorType($error['type']),
                $error['message'],
                $error['file'],
                $error['line']
            );

            if (self::$isProduction) {
                self::displayGenericError();
            }
        }
    }

    /**
     * Log error to file
     * 
     * @param string $type Error type
     * @param string $message Error message
     * @param string $file File path
     * @param int $line Line number
     */
    private static function logError(string $type, string $message, string $file, int $line): void
    {
        $logDir = dirname(self::$logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? 'N/A';
        
        $logMessage = sprintf(
            "[%s] %s: %s in %s on line %d | IP: %s | URI: %s\n",
            $timestamp,
            $type,
            $message,
            $file,
            $line,
            $ip,
            $uri
        );

        error_log($logMessage, 3, self::$logPath);
    }

    /**
     * Log exception to file
     * 
     * @param Throwable $exception
     */
    private static function logException(Throwable $exception): void
    {
        $logDir = dirname(self::$logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? 'N/A';
        
        $logMessage = sprintf(
            "[%s] EXCEPTION: %s\nMessage: %s\nFile: %s:%d\nTrace:\n%s\nIP: %s | URI: %s\n\n",
            $timestamp,
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
            $ip,
            $uri
        );

        error_log($logMessage, 3, self::$logPath);
    }

    /**
     * Display generic error page (production)
     */
    private static function displayGenericError(): void
    {
        if (headers_sent()) {
            echo "\n\nAn error occurred. Please try again later.\n";
            return;
        }

        http_response_code(500);
        
        // Check if this is an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'An error occurred. Please try again later.']);
            return;
        }

        // Display HTML error page
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - MockStore</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <h1 class="display-1 text-danger">500</h1>
                        <h2 class="mb-4">Something went wrong</h2>
                        <p class="text-muted mb-4">
                            We're sorry, but something went wrong. Our team has been notified 
                            and we're working to fix the issue.
                        </p>
                        <a href="/" class="btn btn-primary">Return to Home</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Display detailed error page (development)
     * 
     * @param Throwable $exception
     */
    private static function displayDetailedError(Throwable $exception): void
    {
        if (headers_sent()) {
            echo "\n\n" . $exception->getMessage() . "\n";
            echo "in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
            return;
        }

        // Check if this is an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
            return;
        }

        // Display HTML error page with details
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - MockStore (Development)</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                .error-details { background: #f8f9fa; padding: 20px; border-radius: 5px; }
                .trace { font-family: monospace; font-size: 12px; white-space: pre-wrap; }
            </style>
        </head>
        <body>
            <div class="container py-5">
                <div class="alert alert-danger">
                    <h4 class="alert-heading">⚠️ Development Mode Error</h4>
                    <p class="mb-0">This detailed error is only shown in development mode.</p>
                </div>
                
                <div class="error-details">
                    <h2><?= htmlspecialchars(get_class($exception)) ?></h2>
                    <h4 class="text-danger"><?= htmlspecialchars($exception->getMessage()) ?></h4>
                    <p class="mb-2">
                        <strong>File:</strong> <?= htmlspecialchars($exception->getFile()) ?><br>
                        <strong>Line:</strong> <?= $exception->getLine() ?>
                    </p>
                    
                    <hr>
                    
                    <h5>Stack Trace:</h5>
                    <div class="trace"><?= htmlspecialchars($exception->getTraceAsString()) ?></div>
                </div>
                
                <div class="mt-4">
                    <a href="/" class="btn btn-primary">Return to Home</a>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Get error type string
     * 
     * @param int $errno Error number
     * @return string
     */
    private static function getErrorType(int $errno): string
    {
        $errorTypes = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        ];

        return $errorTypes[$errno] ?? 'UNKNOWN';
    }

    /**
     * Safely handle database exceptions
     * 
     * @param PDOException $e
     * @param string $operation Description of the operation
     * @throws Exception Generic exception with safe message
     */
    public static function handleDatabaseException(PDOException $e, string $operation = 'database operation'): void
    {
        // Log the detailed error
        self::logException($e);

        // Throw generic exception
        if (self::$isProduction) {
            throw new Exception("An error occurred during $operation. Please try again later.");
        } else {
            // In development, show more details
            throw new Exception("Database error during $operation: " . $e->getMessage());
        }
    }
}
