<?php

declare(strict_types=1);

/**
 * Google OAuth Credentials — EXAMPLE FILE
 *
 * 1. Copy this file to google.php:
 *       cp app/config/google.example.php app/config/google.php
 * 2. Fill in your real credentials from:
 *       https://console.cloud.google.com/apis/credentials
 * 3. google.php is listed in .gitignore so it will NEVER be committed.
 */
return [
    'client_id'     => 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com',
    'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
    'redirect_uri'  => 'http://localhost/webdev/public/login_auth/oauth2callback.php',
];
