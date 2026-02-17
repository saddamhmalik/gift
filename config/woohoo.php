<?php

return [

    'base_url' => env('WOOHOO_BASE_URL', 'https://sandbox.woohoo.in'),

    'oauth' => [
        'client_id' => env('WOOHOO_CLIENT_ID'),
        'client_secret' => env('WOOHOO_CLIENT_SECRET'),
        'username' => env('WOOHOO_USERNAME'),
        'password' => env('WOOHOO_PASSWORD'),
    ],

    'endpoints' => [
        'verify' => '/oauth2/verify',
        'token' => '/oauth2/token',
        'categories' => '/rest/v3/catalog/categories',
        'category_products' => '/rest/v3/catalog/categories',
        'product' => '/rest/v3/catalog/products',
    ],

    'token_cache_ttl' => env('WOOHOO_TOKEN_CACHE_TTL', 604800),

    'user_agent' => env('WOOHOO_USER_AGENT', 'GiftBox/1.0 (QwikGift API Client)'),

];
