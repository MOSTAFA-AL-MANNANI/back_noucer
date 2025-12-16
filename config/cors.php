<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    // المسارات التي تسمح لها بالوصول من الخارج
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // الطرق المسموح بها
    'allowed_methods' => ['*'],

    // أوريجن الخاص بالفرونتند
    'allowed_origins' => ['http://localhost:5173', 'http://localhost:5174'],

    // أنماط أوريجن المسموح بها (فارغ هنا)
    'allowed_origins_patterns' => [],

    // الهيدرز المسموح بها
    'allowed_headers' => ['*'],

    // الهيدرز المكشوفة للفرونتند
    'exposed_headers' => [],

    // مدة الكاش
    'max_age' => 0,

    // السماح بالكويكز مع الطلبات (مهم لـ Sanctum)
    'supports_credentials' => true,

];
