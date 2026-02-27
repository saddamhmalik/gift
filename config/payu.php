<?php

return [
    'key'  => env('PAYU_KEY',  ''),
    'salt' => env('PAYU_SALT', ''),
    'mode' => env('PAYU_MODE', 'test'), // 'test' | 'production'

    'url' => env('PAYU_MODE', 'test') === 'production'
        ? 'https://secure.payu.in/_payment'
        : 'https://test.payu.in/_payment',

    'frontend_url'  => env('FRONTEND_URL', 'http://localhost:3000'),
    'backend_url'   => env('APP_URL',      'http://localhost:8000'),
];
