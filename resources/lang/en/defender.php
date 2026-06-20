<?php

return [
    // General
    'access_blocked' => 'Access blocked due to suspicious activity.',
    'ok' => 'OK',

    // Alerts
    'alert_subject' => '[Defender] Security Alert',
    'alert_suspicious_ip' => 'Suspicious IP detected',
    'alert_suspicious_user_agent' => 'Suspicious user-agent: :user_agent',
    'alert_suspicious_route' => 'Suspicious route accessed: :route',
    'alert_common_username' => 'Login attempt with common username: :username',
    'alert_too_many_attempts' => 'Too many requests from this IP',
    'alert_non_allowed_country' => 'Access from non-allowed country: :country',
    'alert_denied_country' => 'Access from denied country: :country',
    'alert_suspicious_pattern' => 'Suspicious pattern detected: ":pattern" in request',

    // IP blocked
    'ip_blocked' => 'Access denied.',

    // Log viewer command
    'logs_header' => 'Defender IP Logs',
    'logs_no_results' => 'No logs found for the given filters.',
    'logs_total' => 'Total logs: :count',
    'logs_date' => 'Date',
    'logs_ip' => 'IP',
    'logs_route' => 'Route',
    'logs_method' => 'Method',
    'logs_user' => 'User',
    'logs_suspicious' => 'Suspicious',
    'logs_reason' => 'Reason',

    // Stats command column headers (previously missing)
    'stats_ip' => 'IP',
    'stats_attempts' => 'Attempts',
    'stats_country' => 'Country',
    'stats_route' => 'Route',

    // Stats command
    'stats_title' => 'Defender Stats',
    'stats_separator' => '-----------------------------',
    'stats_total_logs' => 'Total logs: :count',
    'stats_unique_ips' => 'Unique IPs: :count',
    'stats_suspicious' => 'Suspicious accesses: :count',
    'stats_top_ips' => 'Top 5 IPs:',
    'stats_top_countries' => 'Top 5 Countries:',
    'stats_top_routes' => 'Top 5 Routes:',

    // Honeypot messages
    'honeypot_bot_detected' => 'Bot detected',
    'honeypot_too_quick' => 'Form submitted too quickly',
    'honeypot_invalid' => 'Invalid honeypot',
    'honeypot_missing' => 'Missing honeypot',

    // Blocklist commands
    'auto_block_reason' => 'Automatically blocked after :count detected threats',
    'block_ip_invalid' => 'Invalid IP address: :ip',
    'block_ip_permanent' => 'IP :ip has been permanently blocked.',
    'block_ip_until' => 'IP :ip has been blocked until :until.',
    'unblock_ip_success' => 'IP :ip has been unblocked.',
    'unblock_ip_not_found' => 'IP :ip was not found in the block list.',
    'block_list_header' => 'Blocked IPs',
    'block_list_empty' => 'No IPs are currently blocked.',
    'block_list_ip' => 'IP',
    'block_list_reason' => 'Reason',
    'block_list_until' => 'Blocked Until',
    'block_list_permanent' => 'Permanent',
    'block_list_total' => 'Total blocked IPs: :count',

    // Audit command
    'audit_running' => 'Running Laravel Defender Security Audit...',
    'audit_complete' => 'Audit complete.',
    'audit_env_exposed' => '.env file is publicly accessible at :url',
    'audit_env_exposed_tip' => 'Block access to .env in your web server config (Apache/Nginx).',
    'audit_env_not_exposed' => '.env file is not publicly accessible.',
    'audit_debug_enabled' => 'APP_DEBUG is enabled!',
    'audit_debug_tip' => 'Set APP_DEBUG=false in your .env for production.',
    'audit_debug_disabled' => 'APP_DEBUG is disabled.',
    'audit_cors_permissive' => 'CORS is permissive (allowed_origins = "*")!',
    'audit_cors_tip' => 'Restrict CORS origins in config/cors.php for better security.',
    'audit_cors_ok' => 'CORS configuration is not permissive.',
    'audit_cookies_insecure' => 'Session cookies are not set as secure!',
    'audit_cookies_secure_tip' => 'Set SESSION_SECURE_COOKIE=true in your .env for HTTPS.',
    'audit_cookies_secure' => 'Session cookies are secure.',
    'audit_cookies_http_only' => 'Session cookies are HTTP only.',
    'audit_cookies_http_only_missing' => 'Session cookies are not HTTP only!',
    'audit_cookies_http_only_tip' => 'Set SESSION_HTTP_ONLY=true in your .env.',
    'audit_laravel_version' => 'Laravel version: :version',
    'audit_app_key_insecure' => 'APP_KEY is missing, too short, or insecure!',
    'audit_app_key_tip' => 'Set a secure APP_KEY in your .env file (run `php artisan key:generate`).',
    'audit_app_key_secure' => 'APP_KEY is set and looks secure.',

    // Audit — security headers
    'audit_headers_checking' => 'Checking security headers...',
    'audit_headers_ok' => 'Security headers look good.',
    'audit_headers_missing' => 'Missing security header: :header',
    'audit_headers_unreachable' => 'Could not reach app URL to check headers (skipping).',
    'audit_headers_x_frame_options_tip' => 'Add X-Frame-Options: SAMEORIGIN to prevent clickjacking.',
    'audit_headers_x_content_type_tip' => 'Add X-Content-Type-Options: nosniff to prevent MIME sniffing.',
    'audit_headers_referrer_policy_tip' => 'Add Referrer-Policy: strict-origin-when-cross-origin.',
    'audit_headers_hsts_tip' => 'Add Strict-Transport-Security header to enforce HTTPS.',

    // Database alert messages
    'db_logging_disabled' => 'Database logging is disabled in config/defender.php.',
    'logs_table_missing' => 'Defender IP logs table does not exist. Run: php artisan vendor:publish --tag=defender-migrations && php artisan migrate',

    // Export command
    'export_logs_csv' => 'Exported :count logs to :output (CSV)',
    'export_logs_json' => 'Exported :count logs to :output (JSON)',

    // Prune command
    'prune_deleted' => 'Deleted :count Defender logs older than :days days from database.',
    'prune_none' => 'No Defender logs older than :days days found in database.',
    'prune_table_missing' => 'Defender log table (:table) does not exist.',
    'prune_laravel_deleted' => 'Deleted :count Laravel log files older than :days days.',

    // Security tip
    'tip_avoid_common_usernames' => 'Avoid using common usernames like admin, administrator, root, test, or user.',
];
