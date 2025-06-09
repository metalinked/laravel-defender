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
        'alert_channels' => ['log', 'abuseipdb'], // ['log', 'mail', 'slack', 'webhook']
        'abuseipdb_api_key' => env('ABUSEIPDB_API_KEY'),
        'max_attempts' => 5,
        'decay_minutes' => 10,
        'block_suspicious' => true,
    ],
    // Info: SaaS integration will be available in the future via a separate connector package.
];