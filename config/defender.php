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
        'enabled' => true,
        'log_all' => false, // true: all IPs, false: only suspicious ones
        'abuseipdb_api_key' => env('ABUSEIPDB_API_KEY'),
        'max_attempts' => 5,
        'decay_minutes' => 10,
        'block_suspicious' => true,
    ],
    'advanced_detection' => [
        'enabled' => true,
        'suspicious_user_agents' => [
            'curl', 'python', 'sqlmap', 'nmap', 'nikto', 'fuzzer', 'scanner', 'masscan', 'libwww-perl', 'wget', 'httpclient'
        ],
        'suspicious_routes' => [
            '/wp-admin', '/wp-login', '/phpmyadmin', '/admin.php', '/xmlrpc.php'
        ],
        'common_usernames' => [
            'admin', 'administrator', 'root', 'test', 'user'
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
            // 'mail',   // Send an email
            // 'database', // Save the alert to the database (requires migration)
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
    // Info: SaaS integration will be available in the future via a separate connector package.
];