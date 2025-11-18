<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'registration' => [
            'max_attempts' => 3,
            'decay_minutes' => 60,
        ],
        'contact_form' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
        ],
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
        ],
        'payment_submission' => [
            'max_attempts' => 3,
            'decay_minutes' => 30,
        ],
        'file_upload' => [
            'max_attempts' => 10,
            'decay_minutes' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'file_upload' => [
        'max_file_size' => 5242880, // 5MB in bytes
        'allowed_image_types' => ['jpeg', 'jpg', 'png', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'txt'],
        'scan_for_malware' => true,
        'quarantine_suspicious_files' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    */
    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'report_only' => env('CSP_REPORT_ONLY', false),
        'report_uri' => env('CSP_REPORT_URI', '/csp-report'),
        'directives' => [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com",
            'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            'font-src' => "'self' https://fonts.gstatic.com",
            'img-src' => "'self' data: https: blob:",
            'connect-src' => "'self'",
            'media-src' => "'self'",
            'object-src' => "'none'",
            'base-uri' => "'self'",
            'form-action' => "'self'",
            'frame-ancestors' => "'none'",
            'upgrade-insecure-requests' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'timeout_minutes' => 120, // 2 hours
        'regenerate_on_login' => true,
        'secure_cookies' => env('SESSION_SECURE_COOKIES', false),
        'same_site_cookies' => 'lax',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Security
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => false,
        'max_age_days' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Restrictions
    |--------------------------------------------------------------------------
    */
    'ip_restrictions' => [
        'admin_whitelist' => env('ADMIN_IP_WHITELIST', ''),
        'blocked_ips' => [],
        'enable_geo_blocking' => false,
        'allowed_countries' => ['BD', 'US', 'CA'], // ISO country codes
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => true,
        'log_all_requests' => false,
        'log_admin_actions' => true,
        'log_authentication' => true,
        'log_data_changes' => true,
        'retention_days' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'DENY',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'hsts_max_age' => 31536000, // 1 year
        'hsts_include_subdomains' => true,
        'hsts_preload' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'max_input_length' => 10000,
        'allow_html_in_content' => true,
        'allowed_html_tags' => '<p><br><strong><em><ul><ol><li><h3><h4><a>',
        'strip_dangerous_attributes' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Malware Detection
    |--------------------------------------------------------------------------
    */
    'malware' => [
        'enabled' => true,
        'scan_uploads' => true,
        'quarantine_path' => storage_path('quarantine'),
        'signature_patterns' => [
            'php_injection' => '/<\?php|<\?=|eval\s*\(|exec\s*\(|system\s*\(/i',
            'script_injection' => '/<script|javascript:|vbscript:/i',
            'sql_injection' => '/union.*select|select.*from|drop\s+table/i',
        ],
    ],
];