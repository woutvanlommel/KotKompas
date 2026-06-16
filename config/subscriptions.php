<?php

return [
    'plans' => [
        'starter' => env('STRIPE_PRICE_STARTER'),
        'pro' => env('STRIPE_PRICE_PRO'),
        'premium' => env('STRIPE_PRICE_PREMIUM'),
    ],


    'featured_slots' => [
        'starter' => 0,
        'pro' => 1,
        'premium' => 3,
    ],
];
