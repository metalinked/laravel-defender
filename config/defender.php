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
        'enabled' => true,  // Enable/disable database logging entirely
        'log_all' => false, // WARNING: If true, writes ALL requests to the Laravel log.
                            // Only recommended for temporary debugging. Not for production!
    ],

    // Brute force protection
    // Counts suspicious requests from the same IP within the decay window.
    // Uses a 30-second cache layer to reduce DB load.
    'brute_force' => [
        'max_attempts' => 5,
        'decay_minutes' => 10,
    ],

    'advanced_detection' => [
        'enabled' => true,
        'geo_provider' => env('DEFENDER_GEO_PROVIDER', 'ip-api'), // 'ip-api', 'ipinfo', 'ipgeolocation'
        'geo_cache_minutes' => 10,
        'ipinfo_token' => env('IPINFO_TOKEN'),
        'ipgeolocation_key' => env('IPGEOLOCATION_KEY'),
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
            'whitelist_ips' => [], // Always allowed regardless of country or mode
        ],
    ],

    // Blocklist: IPs blocked dynamically via defender:block-ip command.
    // Uses a DB table + cache layer. Requires the defender-migrations to be published and run.
    'blocklist' => [
        'enabled' => true,
        'cache_ttl' => 300,         // seconds to cache a block check (5 minutes)
        'auto_block_after' => null, // null = disabled. Integer = auto-block after N IpBlocked events from the same IP.
        'auto_block_hours' => null, // null = permanent auto-block. Integer = hours to block for.
        'auto_block_window_hours' => 24, // rolling window in hours for counting IpBlocked events.
    ],

    // Alert channels configuration.
    // Enable or disable specific channels by commenting/uncommenting them.
    'alerts' => [
        'channels' => [
            'log',      // Save the alert to the Laravel log
            'database', // Save the alert to the database (requires migration)
            // 'mail',
            // 'slack',
            // 'webhook',
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
