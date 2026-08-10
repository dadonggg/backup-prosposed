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
        'host' => 'sql104.infinityfree.com',
        'name' => 'if0_42266462_nutrify',
        'user' => 'if0_42266462',
        'pass' => 'ODlqkgjyHbEbER',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => 'https://nutrify.freehosting.dev',
    ],
    'google_oauth' => [
        'client_id' => (string)($googleOauth['client_id'] ?? ''),
        'client_secret' => (string)($googleOauth['client_secret'] ?? ''),
        'redirect_uri' => (string)($googleOauth['redirect_uri'] ?? 'https://nutrify.freehosting.dev/public/login_auth/oauth2callback.php'),
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
    'usda' => [
        'api_key' => 'VGbJn4p8tPa6b2HQ48SUYKLqj1aZjdHwOYimGqz', // Paste your free private USDA API key here!
    ],
    'gemini' => [
        'api_key' => 'AIzaSyAb8RN6IQclQhmXTan8dKMwkJxyyAHFISoxEDfg5zo-NiZVU__Q',
        'model'   => 'gemini-1.5-flash',
    ],
];
