<?php

return [
    'dashboard_route' => 'defender',
    'enable_logging' => true,
    // SaaS configuration
    // Set 'enabled' to true to enable SaaS integration
    'saas' => [
        'enabled' => false,
        'endpoint' => env('DEFENDER_SAAS_ENDPOINT', null),
        'api_token' => env('DEFENDER_SAAS_TOKEN', null),
    ],
    // Dashboard configuration
    // Set 'enabled' to false to disable the dashboard
    'dashboard' => [
        'enabled' => true,
        'access_token' => env('DEFENDER_DASHBOARD_TOKEN', null),
    ],
    // Honeypot configuration
    // Set 'enabled' to false to disable honeypot protection
    'honeypot' => [
        'enabled' => true,
        'auto_protect_forms' => true,
        'minimum_time' => 2, // seconds
        'field_prefix' => 'my_full_name_',
    ],
    // IP logging configuration
    // Set 'enabled' to false to disable IP logging
    'ip_logging' => [
        'enabled' => true,
        'log_all' => false, // true: all IPs, false: only suspicious ones
        'alert_channels' => ['log'], // ['log', 'mail', 'slack', 'webhook']
    ],
];