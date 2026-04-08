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
        'orders'          => '/rest/v3/orders',
        'order_status'    => '/rest/v3/orders',
        'activated_cards' => '/rest/v3/order', // append /{orderId}/cards/
    ],

    'org_short_code' => env('WOOHOO_ORG_SHORT_CODE', 'ONEZERO'),

    'token_cache_ttl' => env('WOOHOO_TOKEN_CACHE_TTL', 604800),

    'user_agent' => env('WOOHOO_USER_AGENT', 'GiftBox/1.0 (QwikGift API Client)'),

    /** HTTP timeouts (seconds). Order POST default 10s per QC UAT guidance. */
    'http_timeout' => [
        'oauth' => (int) env('WOOHOO_OAUTH_TIMEOUT', 15),
        'get'   => (int) env('WOOHOO_GET_TIMEOUT', 30),
        'post'  => (int) env('WOOHOO_ORDER_POST_TIMEOUT', 10),
    ],

    /** After Order POST client timeout, delay before first Status API recovery attempt */
    'order_timeout_status_delay_sec' => (int) env('WOOHOO_ORDER_TIMEOUT_STATUS_DELAY_SEC', 40),

    /**
     * Async orders: max GET /rest/v3/orders/{id} checks; delays after 1st check 5m, after 2nd 10m (exponential).
     */
    'status_poll' => [
        'max_checks'       => (int) env('WOOHOO_STATUS_POLL_MAX_CHECKS', 3),
        'first_delay_sec'  => (int) env('WOOHOO_STATUS_POLL_FIRST_DELAY_SEC', 120),
        'second_delay_sec' => (int) env('WOOHOO_STATUS_POLL_SECOND_DELAY_SEC', 300),
        'third_delay_sec'  => (int) env('WOOHOO_STATUS_POLL_THIRD_DELAY_SEC', 600),
    ],

];
