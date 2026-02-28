<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Loyalty Earn Rate
    |--------------------------------------------------------------------------
    | Fraction of order amount awarded as points.
    | 0.01 = 1% (i.e., ₹100 spend → 1 point credited; 1 point = ₹1)
    | Products can override this via their loyalty_rate column.
    */
    'default_rate' => env('LOYALTY_DEFAULT_RATE', 0.01),

    /*
    |--------------------------------------------------------------------------
    | Points Validity (days)
    |--------------------------------------------------------------------------
    | Number of days after credit before points expire.
    */
    'validity_days' => env('LOYALTY_VALIDITY_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Minimum Redeemable Points
    |--------------------------------------------------------------------------
    | Minimum points a user must have to redeem any on a transaction.
    */
    'min_redeem' => env('LOYALTY_MIN_REDEEM', 1),
];
