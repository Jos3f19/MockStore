<?php
/**
 * Environment Variable Loader
 * Simple .env file parser for loading environment variables
 */
class Env
{
    private static bool $loaded = false;

    /**
     * Load environment variables from .env file
     * 
     * @param string $path Path to .env file
     */
    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = $path . '/.env';
        
        if (!file_exists($envFile)) {
            throw new RuntimeException('.env file not found. Please copy .env.example to .env and configure your credentials.');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE format
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                // Set environment variable
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Get an environment variable
     * 
     * @param string $key Variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
