<?php

return [
    // General
    'access_blocked' => 'Acceso bloqueado debido a actividad sospechosa.',
    'ok' => 'OK',

    // Alerts
    'alert_subject' => '[Defender] Alerta de Seguridad',
    'alert_suspicious_ip' => 'IP sospechosa detectada',
    'alert_suspicious_user_agent' => 'User-agent sospechoso: :user_agent',
    'alert_suspicious_route' => 'Ruta sospechosa accedida: :route',
    'alert_common_username' => 'Intento de inicio de sesión con nombre de usuario común: :username',
    'alert_too_many_attempts' => 'Demasiadas peticiones desde esta IP',
    'alert_non_allowed_country' => 'Acceso desde país no permitido: :country',
    'alert_denied_country' => 'Acceso desde país denegado: :country',
    'alert_suspicious_pattern' => 'Patrón sospechoso detectado: ":pattern" en la solicitud',

    // IP blocked
    'ip_blocked' => 'Acceso denegado.',

    // Log viewer command
    'logs_header' => 'Registros de IP de Defender',
    'logs_no_results' => 'No se encontraron registros para los filtros dados.',
    'logs_total' => 'Total de registros: :count',
    'logs_date' => 'Fecha',
    'logs_ip' => 'IP',
    'logs_route' => 'Ruta',
    'logs_method' => 'Método',
    'logs_user' => 'Usuario',
    'logs_suspicious' => 'Sospechoso',
    'logs_reason' => 'Razón',

    // Stats command column headers
    'stats_ip' => 'IP',
    'stats_attempts' => 'Intentos',
    'stats_country' => 'País',
    'stats_route' => 'Ruta',

    // Stats command
    'stats_title' => 'Estadísticas de Defender',
    'stats_separator' => '-----------------------------',
    'stats_total_logs' => 'Total de registros: :count',
    'stats_unique_ips' => 'IPs únicas: :count',
    'stats_suspicious' => 'Accesos sospechosos: :count',
    'stats_top_ips' => 'Top 5 IPs:',
    'stats_top_countries' => 'Top 5 países:',
    'stats_top_routes' => 'Top 5 rutas:',

    // Honeypot messages
    'honeypot_bot_detected' => 'Bot detectado',
    'honeypot_too_quick' => 'Formulario enviado demasiado rápido',
    'honeypot_invalid' => 'Honeypot inválido',
    'honeypot_missing' => 'Falta el honeypot',

    // Blocklist commands
    'auto_block_reason' => 'Bloqueado automáticamente tras :count amenazas detectadas',
    'block_ip_invalid' => 'Dirección IP inválida: :ip',
    'block_ip_permanent' => 'La IP :ip ha sido bloqueada permanentemente.',
    'block_ip_until' => 'La IP :ip ha sido bloqueada hasta :until.',
    'unblock_ip_success' => 'La IP :ip ha sido desbloqueada.',
    'unblock_ip_not_found' => 'La IP :ip no estaba en la lista de bloqueo.',
    'block_list_header' => 'IPs bloqueadas',
    'block_list_empty' => 'No hay IPs bloqueadas actualmente.',
    'block_list_ip' => 'IP',
    'block_list_reason' => 'Razón',
    'block_list_until' => 'Bloqueada hasta',
    'block_list_permanent' => 'Permanente',
    'block_list_total' => 'Total de IPs bloqueadas: :count',

    // Audit command
    'audit_running' => 'Ejecutando Auditoría de Seguridad de Laravel Defender...',
    'audit_complete' => 'Auditoría completada.',
    'audit_env_exposed' => 'El archivo .env es accesible públicamente en :url',
    'audit_env_exposed_tip' => 'Bloquea el acceso a .env en la configuración de tu servidor web (Apache/Nginx).',
    'audit_env_not_exposed' => 'El archivo .env no es accesible públicamente.',
    'audit_debug_enabled' => 'APP_DEBUG está habilitado!',
    'audit_debug_tip' => 'Establece APP_DEBUG=false en tu .env para producción.',
    'audit_debug_disabled' => 'APP_DEBUG está deshabilitado.',
    'audit_cors_permissive' => 'CORS es permisivo (allowed_origins = "*")!',
    'audit_cors_tip' => 'Restringe los orígenes de CORS en config/cors.php para mayor seguridad.',
    'audit_cors_ok' => 'La configuración de CORS no es permisiva.',
    'audit_cookies_insecure' => 'Las cookies de sesión no están configuradas como seguras!',
    'audit_cookies_secure_tip' => 'Establece SESSION_SECURE_COOKIE=true en tu .env para HTTPS.',
    'audit_cookies_secure' => 'Las cookies de sesión son seguras.',
    'audit_cookies_http_only' => 'Las cookies de sesión son solo HTTP.',
    'audit_cookies_http_only_missing' => 'Las cookies de sesión no son solo HTTP!',
    'audit_cookies_http_only_tip' => 'Establece SESSION_HTTP_ONLY=true en tu .env.',
    'audit_laravel_version' => 'Versión de Laravel: :version',
    'audit_app_key_insecure' => 'APP_KEY está ausente, es demasiado corta o no es segura!',
    'audit_app_key_tip' => 'Establece un APP_KEY seguro en tu .env (usa `php artisan key:generate`).',
    'audit_app_key_secure' => 'APP_KEY está configurado y parece seguro.',

    // Audit — security headers
    'audit_headers_checking' => 'Comprobando cabeceras de seguridad...',
    'audit_headers_ok' => 'Las cabeceras de seguridad están bien configuradas.',
    'audit_headers_missing' => 'Cabecera de seguridad ausente: :header',
    'audit_headers_unreachable' => 'No se pudo alcanzar la URL de la app para comprobar las cabeceras (omitiendo).',
    'audit_headers_x_frame_options_tip' => 'Añade X-Frame-Options: SAMEORIGIN para prevenir clickjacking.',
    'audit_headers_x_content_type_tip' => 'Añade X-Content-Type-Options: nosniff para prevenir MIME sniffing.',
    'audit_headers_referrer_policy_tip' => 'Añade Referrer-Policy: strict-origin-when-cross-origin.',
    'audit_headers_hsts_tip' => 'Añade la cabecera Strict-Transport-Security para forzar HTTPS.',

    // Database alert messages
    'db_logging_disabled' => 'El registro en base de datos está deshabilitado en config/defender.php.',
    'logs_table_missing' => 'La tabla de registros de IP de Defender no existe. Ejecuta: php artisan vendor:publish --tag=defender-migrations && php artisan migrate',

    // Export command
    'export_logs_csv' => 'Exportados :count registros a :output (CSV)',
    'export_logs_json' => 'Exportados :count registros a :output (JSON)',

    // Prune command
    'prune_deleted' => 'Eliminados :count registros de Defender de más de :days días de la base de datos.',
    'prune_none' => 'No se encontraron registros de Defender de más de :days días.',
    'prune_table_missing' => 'La tabla de registros de Defender (:table) no existe.',
    'prune_laravel_deleted' => 'Eliminados :count archivos de registro de Laravel de más de :days días.',

    // Security tip
    'tip_avoid_common_usernames' => 'Evita usar nombres de usuario comunes como admin, administrator, root, test o user.',
];
