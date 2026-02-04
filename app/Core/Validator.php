<?php
/**
 * Validator Class
 * Provides comprehensive input validation methods
 */
class Validator
{
    /**
     * Validate email address
     * 
     * @param string $email Email to validate
     * @param int $maxLength Maximum length allowed
     * @return bool True if valid
     */
    public static function email(string $email, int $maxLength = 255): bool
    {
        if (strlen($email) > $maxLength) {
            return false;
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate name (allows letters, spaces, hyphens, apostrophes)
     * 
     * @param string $name Name to validate
     * @param int $minLength Minimum length
     * @param int $maxLength Maximum length
     * @return bool True if valid
     */
    public static function name(string $name, int $minLength = 2, int $maxLength = 100): bool
    {
        $length = strlen($name);
        if ($length < $minLength || $length > $maxLength) {
            return false;
        }
        // Allow letters (including accented), spaces, hyphens, apostrophes
        return preg_match("/^[a-zA-ZÀ-ÿ\s\-']+$/u", $name) === 1;
    }

    /**
     * Validate phone number
     * Allows international format with +, digits, spaces, hyphens, parentheses
     * 
     * @param string $phone Phone number to validate
     * @return bool True if valid
     */
    public static function phone(string $phone): bool
    {
        // Allow +, digits, spaces, hyphens, parentheses (7-20 characters)
        return preg_match('/^[+]?[0-9\s\-()]{7,20}$/', $phone) === 1;
    }

    /**
     * Validate document ID (passport, national ID, etc.)
     * 
     * @param string $document Document to validate
     * @return bool True if valid
     */
    public static function document(string $document): bool
    {
        // Allow alphanumeric and hyphens (5-20 characters)
        return preg_match('/^[A-Z0-9\-]{5,20}$/i', $document) === 1;
    }

    /**
     * Validate integer within a specific range
     * 
     * @param int $value Value to validate
     * @param int $min Minimum value (inclusive)
     * @param int $max Maximum value (inclusive)
     * @return bool True if valid
     */
    public static function integerInRange(int $value, int $min, int $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Validate positive integer
     * 
     * @param mixed $value Value to validate
     * @return bool True if valid positive integer
     */
    public static function positiveInteger($value): bool
    {
        return is_numeric($value) && (int)$value > 0 && (int)$value == $value;
    }

    /**
     * Validate quantity (for cart items)
     * 
     * @param int $quantity Quantity to validate
     * @param int $max Maximum quantity allowed
     * @return bool True if valid
     */
    public static function quantity(int $quantity, int $max = 99): bool
    {
        return $quantity > 0 && $quantity <= $max;
    }

    /**
     * Sanitize string for safe output
     * 
     * @param string $input Input string
     * @return string Sanitized string
     */
    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate string length
     * 
     * @param string $string String to validate
     * @param int $minLength Minimum length
     * @param int $maxLength Maximum length
     * @return bool True if valid
     */
    public static function stringLength(string $string, int $minLength = 0, int $maxLength = PHP_INT_MAX): bool
    {
        $length = strlen($string);
        return $length >= $minLength && $length <= $maxLength;
    }

    /**
     * Validate URL
     * 
     * @param string $url URL to validate
     * @return bool True if valid
     */
    public static function url(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate alphanumeric string
     * 
     * @param string $string String to validate
     * @return bool True if valid
     */
    public static function alphanumeric(string $string): bool
    {
        return preg_match('/^[a-zA-Z0-9]+$/', $string) === 1;
    }

    /**
     * Check if value is in allowed list
     * 
     * @param mixed $value Value to check
     * @param array $allowedValues List of allowed values
     * @return bool True if value is in list
     */
    public static function inList($value, array $allowedValues): bool
    {
        return in_array($value, $allowedValues, true);
    }
}
