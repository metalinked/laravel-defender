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
    'alert_too_many_attempts' => 'Massa peticions des d\'aquesta IP',
    'alert_non_allowed_country' => 'Accés des d\'un país no permès: :country',
    'alert_denied_country' => 'Accés des d\'un país denegat: :country',
    'alert_suspicious_pattern' => 'S\'ha detectat un patró sospitós: ":pattern" a la sol·licitud',

    // IP blocked
    'ip_blocked' => 'Accés denegat.',

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

    // Stats command column headers
    'stats_ip' => 'IP',
    'stats_attempts' => 'Intents',
    'stats_country' => 'País',
    'stats_route' => 'Ruta',

    // Stats command
    'stats_title' => 'Estadístiques de Defender',
    'stats_separator' => '-----------------------------',
    'stats_total_logs' => 'Total de registres: :count',
    'stats_unique_ips' => 'IPs úniques: :count',
    'stats_suspicious' => 'Accessos sospitosos: :count',
    'stats_top_ips' => 'Top 5 IPs:',
    'stats_top_countries' => 'Top 5 països:',
    'stats_top_routes' => 'Top 5 rutes:',

    // Honeypot messages
    'honeypot_bot_detected' => 'Bot detectat',
    'honeypot_too_quick' => 'El formulari s\'ha enviat massa ràpidament',
    'honeypot_invalid' => 'Honeypot invàlid',
    'honeypot_missing' => 'Falta el honeypot',

    // Blocklist commands
    'auto_block_reason' => 'Bloquejat automàticament després de :count amenaces detectades',
    'block_ip_invalid' => 'Adreça IP invàlida: :ip',
    'block_ip_permanent' => 'La IP :ip ha estat bloquejada permanentment.',
    'block_ip_until' => 'La IP :ip ha estat bloquejada fins :until.',
    'unblock_ip_success' => 'La IP :ip ha estat desbloquejada.',
    'unblock_ip_not_found' => 'La IP :ip no es trobava a la llista de bloqueig.',
    'block_list_header' => 'IPs bloquejades',
    'block_list_empty' => 'No hi ha cap IP bloquejada actualment.',
    'block_list_ip' => 'IP',
    'block_list_reason' => 'Motiu',
    'block_list_until' => 'Bloquejada fins',
    'block_list_permanent' => 'Permanent',
    'block_list_total' => 'Total d\'IPs bloquejades: :count',

    // Audit command
    'audit_running' => 'S\'està executant l\'auditoria de seguretat de Laravel Defender...',
    'audit_complete' => 'Auditoria completada.',
    'audit_env_exposed' => 'L\'arxiu .env és accessible públicament a :url',
    'audit_env_exposed_tip' => 'Bloca l\'accés a .env a la configuració del teu servidor web (Apache/Nginx).',
    'audit_env_not_exposed' => 'L\'arxiu .env no és accessible públicament.',
    'audit_debug_enabled' => 'APP_DEBUG està activat!',
    'audit_debug_tip' => 'Estableix APP_DEBUG=false al teu .env per a producció.',
    'audit_debug_disabled' => 'APP_DEBUG està desactivat.',
    'audit_cors_permissive' => 'CORS és massa permissiu (allowed_origins = "*")!',
    'audit_cors_tip' => 'Restringeix els orígens CORS a config/cors.php per a més seguretat.',
    'audit_cors_ok' => 'La configuració de CORS no és permissiva.',
    'audit_cookies_insecure' => 'Les galetes de sessió no són segures!',
    'audit_cookies_secure_tip' => 'Estableix SESSION_SECURE_COOKIE=true al teu .env per a HTTPS.',
    'audit_cookies_secure' => 'Les galetes de sessió són segures.',
    'audit_cookies_http_only' => 'Les galetes de sessió són només HTTP.',
    'audit_cookies_http_only_missing' => 'Les galetes de sessió no són només HTTP!',
    'audit_cookies_http_only_tip' => 'Estableix SESSION_HTTP_ONLY=true al teu .env.',
    'audit_laravel_version' => 'Versió de Laravel: :version',
    'audit_app_key_insecure' => 'APP_KEY falta, és massa curt o insegur!',
    'audit_app_key_tip' => 'Estableix un APP_KEY segur al teu .env (utilitza `php artisan key:generate`).',
    'audit_app_key_secure' => 'APP_KEY està establert i sembla segur.',

    // Audit — security headers
    'audit_headers_checking' => 'Comprovant capçaleres de seguretat...',
    'audit_headers_ok' => 'Les capçaleres de seguretat estan ben configurades.',
    'audit_headers_missing' => 'Capçalera de seguretat absent: :header',
    'audit_headers_unreachable' => 'No s\'ha pogut accedir a la URL de l\'app per comprovar les capçaleres (ometent).',
    'audit_headers_x_frame_options_tip' => 'Afegeix X-Frame-Options: SAMEORIGIN per prevenir el clickjacking.',
    'audit_headers_x_content_type_tip' => 'Afegeix X-Content-Type-Options: nosniff per prevenir el MIME sniffing.',
    'audit_headers_referrer_policy_tip' => 'Afegeix Referrer-Policy: strict-origin-when-cross-origin.',
    'audit_headers_hsts_tip' => 'Afegeix la capçalera Strict-Transport-Security per forçar HTTPS.',

    // Database alert messages
    'db_logging_disabled' => 'El registre a la base de dades està desactivat a config/defender.php.',
    'logs_table_missing' => 'La taula de registres d\'IP de Defender no existeix. Executa: php artisan vendor:publish --tag=defender-migrations && php artisan migrate',

    // Export command
    'export_logs_csv' => 'S\'han exportat :count registres a :output (CSV)',
    'export_logs_json' => 'S\'han exportat :count registres a :output (JSON)',

    // Prune command
    'prune_deleted' => 'S\'han eliminat :count registres de Defender de més de :days dies de la base de dades.',
    'prune_none' => 'No s\'han trobat registres de Defender de més de :days dies a la base de dades.',
    'prune_table_missing' => 'La taula de registres de Defender (:table) no existeix.',
    'prune_laravel_deleted' => 'S\'han eliminat :count arxius de registre de Laravel de més de :days dies.',

    // Security tip
    'tip_avoid_common_usernames' => 'Evita utilitzar noms d\'usuari comuns com admin, administrator, root, test o user.',
];
