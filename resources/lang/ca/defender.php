<?php

return [
    // General
    'access_blocked' => 'Accés bloquejat per activitat sospitosa.',
    'ok' => 'D\'acord',

    // Alerts
    'alert_subject' => '[Defender] Alerta de Seguretat',
    'alert_suspicious_ip' => 'IP sospitosa detectada',
    'alert_suspicious_user_agent' => 'Agent d\'usuari sospitós: :user_agent',
    'alert_suspicious_route' => 'Ruta sospitosa accedida: :route',
    'alert_common_username' => 'Intent d\'inici de sessió amb nom d\'usuari comú: :username',
    'alert_too_many_attempts' => 'Massa intents d\'inici de sessió',
    'alert_non_allowed_country' => 'Accés des d\'un país no permès: :country',
    'alert_denied_country' => 'Accés des d\'un país denegat: :country',
    'alert_suspicious_pattern' => 'S\'ha detectat un patró sospitós: ":pattern" a la sol·licitud',

    // Log viewer command
    'logs_header' => 'Registres d\'IP de Defender',
    'logs_no_results' => 'No s\'han trobat registres amb els filtres donats.',
    'logs_total' => 'Total de registres: :count',
    'logs_date' => 'Data',
    'logs_ip' => 'IP',
    'logs_route' => 'Ruta',
    'logs_method' => 'Mètode',
    'logs_user' => 'Usuari',
    'logs_suspicious' => 'Sospitós',
    'logs_reason' => 'Motiu',

    // Honeypot messages
    'honeypot_bot_detected' => 'Bot detectat',
    'honeypot_too_quick' => 'El formulari s\'ha enviat massa ràpidament',
    'honeypot_invalid' => 'Honeypot invàlid',
    'honeypot_missing' => 'Falta el honeypot',

    // Audit command
    'audit_running' => '🔒 S\'està executant l\'auditoria de seguretat de Laravel Defender...',
    'audit_complete' => '✅ Auditoria completada.',
    'audit_env_exposed' => '⚠️  L\'arxiu .env és accessible públicament a :url',
    'audit_env_exposed_tip' => '➜ Bloca l\'accés a .env a la configuració del teu servidor web (Apache/Nginx).',
    'audit_env_not_exposed' => '✔️  L\'arxiu .env no és accessible públicament.',
    'audit_debug_enabled' => '⚠️  APP_DEBUG està activat!',
    'audit_debug_tip' => '➜ Estableix APP_DEBUG=false al teu .env per a producció.',
    'audit_debug_disabled' => '✔️  APP_DEBUG està desactivat.',
    'audit_cors_permissive' => '⚠️  CORS és massa permissiu (allowed_origins = "*")!',
    'audit_cors_tip' => '➜ Restringeix els orígens CORS a config/cors.php per a més seguretat.',
    'audit_cors_ok' => '✔️  La configuració de CORS no és permissiva.',
    'audit_cookies_insecure' => '⚠️  Les galetes de sessió no són segures!',
    'audit_cookies_secure_tip' => '➜ Estableix SESSION_SECURE_COOKIE=true al teu .env per a HTTPS.',
    'audit_cookies_secure' => '✔️  Les galetes de sessió són segures.',
    'audit_cookies_http_only' => '✔️  Les galetes de sessió són només HTTP.',
    'audit_cookies_http_only_missing' => '⚠️  Les galetes de sessió no són només HTTP!',
    'audit_cookies_http_only_tip' => '➜ Estableix SESSION_HTTP_ONLY=true al teu .env.',
    'audit_laravel_version' => 'ℹ️  Versió de Laravel: :version','audit_cors_permissive' => 'CORS configuration is too permissive (wildcards detected).',
    'audit_app_key_insecure' => '⚠️  APP_KEY falta, és massa curt o insegur!',
    'audit_app_key_tip' => 'ℹ️  Estableix un APP_KEY segur i aleatori al teu arxiu .env (utilitza `php artisan key:generate`).',
    'audit_app_key_secure' => '✔️  APP_KEY està establert i sembla segur.',

    // Database alert messages
    'db_logging_disabled' => 'El registre a la base de dades està desactivat a config/defender.php.',
    'logs_table_missing' => 'La taula de registres d\'IP de Defender no existeix.',

    // Export command
    'export_logs_csv' => 'S\'han exportat :count registres a :output (CSV)',
    'export_logs_json' => 'S\'han exportat :count registres a :output (JSON)',

    // Prune command
    'prune_deleted' => 'S\'han eliminat :count registres de Defender de més de :days dies de la base de dades.',
    'prune_none' => 'No s\'han trobat registres de Defender de més de :days dies a la base de dades.',
    'prune_table_missing' => 'La taula de registres de Defender (:table) no existeix.',
    'prune_laravel_deleted' => 'S\'han eliminat :count arxius de registre de Laravel de més de :days dies.',

    // Stats command    
    'stats_title' => '📊 Estadístiques de Defender',
    'stats_separator' => '-----------------------------',
    'stats_total_logs' => 'Total de registres: :count',
    'stats_unique_ips' => 'IPs úniques: :count',
    'stats_suspicious' => 'Accesos sospitosos: :count',
    'stats_top_ips' => 'Top 5 IPs:',
    'stats_top_countries' => 'Top 5 països:',
    'stats_top_routes' => 'Top 5 rutes:',

    // Security tip (README, optional for UI)
    'tip_avoid_common_usernames' => 'Evita utilitzar noms d\'usuari comuns com admin, administrator, root, test o user per als comptes de la teva aplicació. Aquests són freqüentment objectiu d\'atacs i són marcats com a sospitosos per Laravel Defender.',
];