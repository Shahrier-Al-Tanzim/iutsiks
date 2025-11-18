<?php

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Sanitize user input to prevent XSS attacks.
     */
    public static function sanitizeInput(string $input, bool $allowHtml = false): string
    {
        if ($allowHtml) {
            // Allow only safe HTML tags
            $allowedTags = '<p><br><strong><em><ul><ol><li><h3><h4><a>';
            return strip_tags($input, $allowedTags);
        }

        return strip_tags($input);
    }

    /**
     * Sanitize HTML content for display.
     */
    public static function sanitizeHtml(string $html): string
    {
        // Remove dangerous attributes and scripts
        $html = preg_replace('/on\w+="[^"]*"/i', '', $html);
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi', '', $html);
        
        return $html;
    }

    /**
     * Generate secure random token.
     */
    public static function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Validate and sanitize email address.
     */
    public static function sanitizeEmail(string $email): string
    {
        return filter_var(trim(strtolower($email)), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Validate phone number format.
     */
    public static function validatePhoneNumber(string $phone): bool
    {
        $pattern = '/^[\+]?[0-9\-\(\)\s]+$/';
        return preg_match($pattern, $phone) === 1;
    }

    /**
     * Sanitize filename for safe storage.
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove path traversal attempts
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
        
        // Limit length
        if (strlen($filename) > 100) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $name = substr(pathinfo($filename, PATHINFO_FILENAME), 0, 90);
            $filename = $name . '.' . $extension;
        }

        return $filename;
    }

    /**
     * Check if string contains suspicious patterns.
     */
    public static function containsSuspiciousPatterns(string $input): bool
    {
        $patterns = [
            '/(<script|<\/script>)/i',
            '/(javascript:|vbscript:|onload=|onerror=)/i',
            '/(eval\(|exec\(|system\()/i',
            '/(\.\.\/)/',
            '/(union.*select|select.*from)/i',
            '/(drop\s+table|delete\s+from)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mask sensitive data for logging.
     */
    public static function maskSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'token', 'secret',
            'api_key', 'private_key', 'credit_card', 'ssn'
        ];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $data[$key] = '***MASKED***';
            } elseif (is_array($value)) {
                $data[$key] = self::maskSensitiveData($value);
            }
        }

        return $data;
    }

    /**
     * Generate Content Security Policy nonce.
     */
    public static function generateCSPNonce(): string
    {
        return base64_encode(random_bytes(16));
    }

    /**
     * Validate CSRF token manually if needed.
     */
    public static function validateCSRFToken(string $token): bool
    {
        return hash_equals(session()->token(), $token);
    }

    /**
     * Check if IP address is in allowed range.
     */
    public static function isIpAllowed(string $ip, array $allowedRanges = []): bool
    {
        if (empty($allowedRanges)) {
            return true; // No restrictions
        }

        foreach ($allowedRanges as $range) {
            if (self::ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range.
     */
    private static function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        
        return ($ip & $mask) === $subnet;
    }

    /**
     * Rate limit key generator.
     */
    public static function generateRateLimitKey(string $action, ?string $identifier = null): string
    {
        $identifier = $identifier ?? request()->ip();
        return "rate_limit:{$action}:{$identifier}";
    }

    /**
     * Log security event.
     */
    public static function logSecurityEvent(string $event, array $context = []): void
    {
        \Log::channel('security')->warning($event, array_merge([
            'ip' => request()->ip(),
            'user_id' => auth()->id(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'timestamp' => now()->toISOString()
        ], $context));
    }
}