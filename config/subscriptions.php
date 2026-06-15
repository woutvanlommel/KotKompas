<?php

return [
    'plans' => [
        'starter' => env('STRIPE_PRICE_STARTER'),
        'pro'     => env('STRIPE_PRICE_PRO'),
        'premium' => env('STRIPE_PRICE_PREMIUM'),
    ],
];
