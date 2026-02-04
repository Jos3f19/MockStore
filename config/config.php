<?php
/**
 * Configuration file for MockStore
 * Loads settings from environment variables
 * 
 * Credentials are stored in .env file (not in version control)
 */

return [
    // PlacetoPay API Credentials (loaded from .env)
    'placetopay' => [
        'login' => Env::get('PLACETOPAY_LOGIN'),
        'secretKey' => Env::get('PLACETOPAY_SECRET_KEY'),
        'webcheckout_url' => Env::get('PLACETOPAY_WEBCHECKOUT_URL'),
        'gateway_url' => Env::get('PLACETOPAY_GATEWAY_URL'),
    ],
    
    // Application settings (loaded from .env)
    'app' => [
        'name' => Env::get('APP_NAME'),
        'url' => Env::get('APP_URL'),
        'currency' => Env::get('APP_CURRENCY'),
        'locale' => Env::get('APP_LOCALE'),
    ],
    
    // Database settings (using SQLite for simplicity)
    'database' => [
        'driver' => 'sqlite',
        'path' => __DIR__ . '/../database/store.db',
    ],
];
