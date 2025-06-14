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
    'alert_too_many_attempts' => 'Too many login attempts',
    'alert_non_allowed_country' => 'Access from non-allowed country: :country',
    'alert_denied_country' => 'Access from denied country: :country',
    'alert_suspicious_pattern' => 'Suspicious pattern detected: ":pattern" in request',

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

    // Honeypot messages
    'honeypot_bot_detected' => 'Bot detected',
    'honeypot_too_quick' => 'Form submitted too quickly',
    'honeypot_invalid' => 'Invalid honeypot',
    'honeypot_missing' => 'Missing honeypot',

    // Audit command
    'audit_running' => '🔒 Running Laravel Defender Security Audit...',
    'audit_complete' => '✅ Audit complete.',
    'audit_env_exposed' => '⚠️  .env file is publicly accessible at :url',
    'audit_env_exposed_tip' => '➜ Block access to .env in your web server config (Apache/Nginx).',
    'audit_env_not_exposed' => '✔️  .env file is not publicly accessible.',
    'audit_debug_enabled' => '⚠️  APP_DEBUG is enabled!',
    'audit_debug_tip' => '➜ Set APP_DEBUG=false in your .env for production.',
    'audit_debug_disabled' => '✔️  APP_DEBUG is disabled.',
    'audit_cors_permissive' => '⚠️  CORS is permissive (allowed_origins = "*")!',
    'audit_cors_tip' => '➜ Restrict CORS origins in config/cors.php for better security.',
    'audit_cors_ok' => '✔️  CORS configuration is not permissive.',
    'audit_cookies_insecure' => '⚠️  Session cookies are not set as secure!',
    'audit_cookies_secure_tip' => '➜ Set SESSION_SECURE_COOKIE=true in your .env for HTTPS.',
    'audit_cookies_secure' => '✔️  Session cookies are secure.',
    'audit_cookies_http_only' => '✔️  Session cookies are HTTP only.',
    'audit_cookies_http_only_missing' => '⚠️  Session cookies are not HTTP only!',
    'audit_cookies_http_only_tip' => '➜ Set SESSION_HTTP_ONLY=true in your .env.',
    'audit_laravel_version' => 'ℹ️  Laravel version: :version',

    // Database alert messages
    'db_logging_disabled' => 'Database logging is disabled in config/defender.php.',
    'logs_table_missing' => 'Defender IP logs table does not exist.',

    // Export command
    'export_logs_csv' => 'Exported :count logs to :output (CSV)',
    'export_logs_json' => 'Exported :count logs to :output (JSON)',

    // Prune command
    'prune_deleted' => 'Deleted :count Defender logs older than :days days from database.',
    'prune_none' => 'No Defender logs older than :days days found in database.',
    'prune_table_missing' => 'Defender log table (:table) does not exist.',
    'prune_laravel_deleted' => 'Deleted :count Laravel log files older than :days days.',

    // Security tip (README, optional for UI)
    'tip_avoid_common_usernames' => 'Avoid using common usernames like admin, administrator, root, test, or user for your application accounts. These are frequently targeted by attackers and are flagged as suspicious by Laravel Defender.',
];