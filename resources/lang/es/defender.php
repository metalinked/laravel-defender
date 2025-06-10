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
    'alert_too_many_attempts' => 'Demasiados intentos de inicio de sesión',
    'alert_non_allowed_country' => 'Acceso desde país no permitido: :country',
    'alert_denied_country' => 'Acceso desde país denegado: :country',

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

    // Honeypot messages
    'honeypot_bot_detected' => 'Bot detectado',
    'honeypot_too_quick' => 'Formulario enviado demasiado rápido',
    'honeypot_invalid' => 'Honeypot inválido',
    'honeypot_missing' => 'Falta el honeypot',

    // Audit command
    'audit_running' => '🔒 Ejecutando Auditoría de Seguridad de Laravel Defender...',
    'audit_complete' => '✅ Auditoría completada.',
    'audit_env_exposed' => '⚠️  El archivo .env es accesible públicamente en :url',
    'audit_env_exposed_tip' => '➜ Bloquea el acceso a .env en la configuración de tu servidor web (Apache/Nginx).',
    'audit_env_not_exposed' => '✔️  El archivo .env no es accesible públicamente.',
    'audit_debug_enabled' => '⚠️  ¡APP_DEBUG está habilitado!',
    'audit_debug_tip' => '➜ Establece APP_DEBUG=false en tu .env para producción.',
    'audit_debug_disabled' => '✔️  APP_DEBUG está deshabilitado.',
    'audit_cors_permissive' => '⚠️  ¡CORS es permisivo (allowed_origins = "*")!',
    'audit_cors_tip' => '➜ Restringe los orígenes de CORS en config/cors.php para mayor seguridad.',
    'audit_cors_ok' => '✔️  La configuración de CORS no es permisiva.',
    'audit_cookies_insecure' => '⚠️  ¡Las cookies de sesión no están configuradas como seguras!',
    'audit_cookies_secure_tip' => '➜ Establece SESSION_SECURE_COOKIE=true en tu .env para HTTPS.',
    'audit_cookies_secure' => '✔️  Las cookies de sesión son seguras.',
    'audit_cookies_http_only' => '✔️  Las cookies de sesión son solo HTTP.',
    'audit_cookies_http_only_missing' => '⚠️  ¡Las cookies de sesión no son solo HTTP!',
    'audit_cookies_http_only_tip' => '➜ Establece SESSION_HTTP_ONLY=true en tu .env.',
    'audit_laravel_version' => 'ℹ️  Versión de Laravel: :version',

    // Export command
    'export_logs_csv' => 'Exportados :count registros a :output (CSV)',
    'export_logs_json' => 'Exportados :count registros a :output (JSON)',

    // Security tip (README, optional for UI)
    'tip_avoid_common_usernames' => 'Evita usar nombres de usuario comunes como admin, administrator, root, test o user para las cuentas de tu aplicación. Estos son frecuentemente atacados y son marcados como sospechosos por Laravel Defender.',
];