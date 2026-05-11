<?php

declare(strict_types=1);

$googleOauth = [];
$googlePath = __DIR__ . '/google.php';
if (is_file($googlePath)) {
    $loaded = require $googlePath;
    if (is_array($loaded)) {
        $googleOauth = $loaded;
    }
}

return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'webdev',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => 'http://localhost/webdev',
    ],
    'google_oauth' => [
        'client_id' => (string)($googleOauth['client_id'] ?? ''),
        'client_secret' => (string)($googleOauth['client_secret'] ?? ''),
        'redirect_uri' => (string)($googleOauth['redirect_uri'] ?? 'http://localhost/webdev/public/login_auth/oauth2callback.php'),
    ],
    'mail' => [
        'driver' => 'smtp',
        'from_email' => 'dadongalfanta9182@gmail.com',
        'from_name' => 'Nutrify',
        'smtp' => [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => 'dadongalfanta9182@gmail.com',
            'password' => 'yhahfllmoxdvedia',
            'encryption' => 'tls',
            'debug' => 2,
        ],
    ],
];
