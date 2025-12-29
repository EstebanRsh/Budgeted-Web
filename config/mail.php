<?php

return [
    'enabled' => envBool('MAIL_ENABLED', true),
    
    'smtp' => [
        'host'       => env('SMTP_HOST', 'smtp.hostinger.com'),
        'port'       => envInt('SMTP_PORT', 587),
        'username'   => env('SMTP_USER', ''),
        'password'   => env('SMTP_PASSWORD', ''),
        'encryption' => env('SMTP_ENCRYPTION', 'tls'),
    ],
    
    'from' => [
        'email' => env('SMTP_FROM_EMAIL', 'noreply@tudominio.com'),
        'name'  => env('SMTP_FROM_NAME', 'Presupuestador'),
    ],
    
    'charset' => 'UTF-8',
    'timeout' => 10,
];
