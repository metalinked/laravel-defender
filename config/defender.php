<?php

return [
    // 'block'   = detect threats, log them, alert, and block the request.
    // 'monitor' = detect, log and alert, but never block anything.
    // Start with 'monitor' on a live site, check `php artisan defender:ip-logs`
    // for false positives for a few days, then switch to 'block'.
    'mode' => env('DEFENDER_MODE', 'block'),

    // Trusted traffic: never inspected, never blocked.
    'whitelist' => [
        'ips' => [],   // Single IPs or CIDR ranges, e.g. '203.0.113.7', '10.0.0.0/8'
        'paths' => [], // Request::is() patterns, e.g. 'up', 'stripe/webhook', 'api/health*'
    ],

    // Hidden form field + minimum fill time. Add @defenderHoneypot to your forms.
    'honeypot' => [
        'enabled' => true,
        'auto_protect_forms' => true, // validate every POST in the 'web' group that carries the field
        'minimum_time' => 2, // seconds
        'field_prefix' => 'my_full_name_',
    ],

    // Failed logins per IP. Works out of the box with anything that uses
    // Auth::attempt() (Breeze, Fortify, Jetstream, custom controllers).
    'login_protection' => [
        'enabled' => true,
        'max_attempts' => 5,    // failed logins from one IP...
        'decay_minutes' => 15,  // ...within this window, then further attempts get HTTP 429
    ],

    // Request inspection: attack patterns, scanner user-agents, probe routes.
    'advanced_detection' => [
        'enabled' => true,
        // Also inspect POST/PUT bodies. Off by default: rich-text editors, code snippets
        // and pasted images produce false positives. Query strings and the URL are always checked.
        'scan_body' => false,
        'extra_patterns' => [],  // added to the built-in attack patterns (case-insensitive substrings)
        'ignore_patterns' => [], // built-in patterns to skip if they cause false positives
        'suspicious_user_agents' => [
            'sqlmap', 'nikto', 'nmap', 'masscan', 'zgrab', 'nuclei', 'wpscan', 'dirbuster', 'gobuster', 'acunetix', 'fuzzer',
            // Generic HTTP clients are left out on purpose (curl, python-requests, wget...):
            // uptime monitors and integrations use them. Add them here if you want them blocked.
        ],
        'suspicious_routes' => [
            '/wp-admin', '/wp-login', '/wp-content', '/wp-includes', '/xmlrpc.php',
            '/phpmyadmin', '/admin.php', '/.env', '/.git', '/cgi-bin', '/vendor/phpunit',
        ],
        // Logins with these usernames are blocked straight away (checked on POST /login).
        'common_usernames' => [
            'admin', 'administrator', 'root', 'test', 'user',
        ],
    ],

    // Allow or deny by country. Disabled while 'countries' is empty.
    'country_access' => [
        'mode' => 'allow', // 'allow' = only these countries; 'deny' = block these countries
        'countries' => [], // ISO codes, e.g. ['ES', 'FR']
    ],

    // Geolocation provider, used by country_access and to enrich logs.
    // Private and reserved IPs are never looked up.
    'geo' => [
        'provider' => env('DEFENDER_GEO_PROVIDER', 'ip-api'), // 'ip-api', 'ipinfo', 'ipgeolocation'
        'cache_minutes' => 60,
        'ipinfo_token' => env('IPINFO_TOKEN'),
        'ipgeolocation_key' => env('IPGEOLOCATION_KEY'),
    ],

    // AbuseIPDB score lookup. Only queried for requests already flagged as
    // suspicious, so it doesn't burn API quota on legitimate traffic.
    'reputation' => [
        'enabled' => false,
        'provider' => 'abuseipdb',
        'api_key' => env('DEFENDER_ABUSEIPDB_KEY'),
        'cache_minutes' => 60,
        'threshold' => 75,     // score (0-100) at/above which an IP is considered malicious
        'auto_block' => false, // true = add IPs at/above the threshold to the blocklist (for auto_block.hours)
    ],

    // Escalation: an IP that keeps triggering detections goes to the blocklist.
    'auto_block' => [
        'after' => 5,           // suspicious requests from one IP... (null = never auto-block)
        'window_minutes' => 60, // ...within this window
        'hours' => 24,          // how long the block lasts (null = permanent)
    ],

    // IP blocklist (defender:block-ip / defender:unblock-ip / auto_block).
    // Requires the defender-migrations to be published and run.
    'blocklist' => [
        'enabled' => true,
        'cache_ttl' => 300, // seconds to cache each IP's block status
    ],

    'alerts' => [
        'channels' => [
            'log',      // Laravel log
            'database', // defender_ip_logs table (needed for defender:ip-logs, stats and the Pulse card)
            // 'mail',
            // 'slack',
            // 'webhook',
        ],
        // mail/slack/webhook send at most one alert per IP within this window,
        // so an attack burst doesn't flood your inbox. log/database record everything.
        'throttle_minutes' => 10,
        'mail' => [
            'to' => env('DEFENDER_ALERT_MAIL_TO'),
        ],
        'slack' => [
            'webhook_url' => env('DEFENDER_SLACK_WEBHOOK'),
        ],
        'webhook' => [
            'url' => env('DEFENDER_ALERT_WEBHOOK'),
        ],
    ],
];
