<?php

return [
    // Configuració bàsica per defecte
    'dashboard_route' => 'defender',
    'enable_logging' => true,
    'honeypot' => [
        'enabled' => true,
        'auto_protect_forms' => true,
        'minimum_time' => 2, // segons
        'field_prefix' => 'my_full_name_',
    ],
];