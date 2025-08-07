<?php

return [
    // Honeypot configuration
    'honeypot' => [
        'enabled' => true,
        'auto_protect_forms' => true,
        'minimum_time' => 2, // seconds
        'field_prefix' => 'my_full_name_',
    ],

    // IP logging configuration
    'ip_logging' => [
        'log_all' => false, // WARNING: If true, logs ALL requests (not just suspicious ones).
                            // Only recommended for testing or temporary auditing.
                            // Not suitable for production environments!
    ],

    // Brute force protection configuration
    'brute_force' => [
        'max_attempts' => 5,
        'decay_minutes' => 10,
    ],

    'advanced_detection' => [
        'enabled' => true,
        'geo_provider' => env('DEFENDER_GEO_PROVIDER', 'ip-api'), // 'ip-api', 'ipinfo', 'ipgeolocation'
        'geo_cache_minutes' => 10, // Cache country codes for 10 minutes
        'ipinfo_token' => env('IPINFO_TOKEN'), // API token for ipinfo.io
        'ipgeolocation_key' => env('IPGEOLOCATION_KEY'), // API key for ipgeolocation.io
        'suspicious_user_agents' => [
            'curl', 'python', 'sqlmap', 'nmap', 'nikto', 'fuzzer', 'scanner', 'masscan', 'libwww-perl', 'wget', 'httpclient',
        ],
        'suspicious_routes' => [
            '/wp-admin', '/wp-login', '/phpmyadmin', '/admin.php', '/xmlrpc.php',
        ],
        'common_usernames' => [
            'admin', 'administrator', 'root', 'test', 'user',
        ],
        'country_access' => [
            'mode' => 'allow', // 'allow' (only allow these countries) or 'deny' (block these countries)
            'countries' => ['ES'],
            'whitelist_ips' => ['1.2.3.4'], // These IPs always have access, regardless of country or mode
        ],
    ],

    // Alert configuration
    // Note: Enable or disable specific channels by commenting/uncommenting them.
    'alerts' => [
        'channels' => [
            'log',      // Save the alert to the Laravel log
            'database', // Save the alert to the database (requires migration)
            // 'mail',   // Send an email
            // 'slack',  // Send to Slack
            // 'webhook' // Send to an external URL
        ],
        'mail' => [
            'to' => env('DEFENDER_ALERT_MAIL_TO', null),
        ],
        'slack' => [
            'webhook_url' => env('DEFENDER_SLACK_WEBHOOK', null),
        ],
        'webhook' => [
            'url' => env('DEFENDER_ALERT_WEBHOOK', null),
        ],
    ],
];
