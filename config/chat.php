<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Messaging Window (grace period)
    |--------------------------------------------------------------------------
    |
    | After a tenant's rental period ends, they keep this many days to still
    | message their landlord. Once the window expires the conversation becomes
    | read-only for the tenant. The landlord is never restricted.
    |
    */

    'tenant_messaging_window_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Chat Message Blacklist
    |--------------------------------------------------------------------------
    |
    | Words listed here will block a message from being sent. The check is
    | case-insensitive and matches whole words only.
    |
    */

    'blacklist' => [
        // Dutch
        'kut',
        'klootzak',
        'lul',
        'eikel',
        'hoer',
        'slet',
        'godverdomme',
        'godver',
        'kanker',
        'tyfus',
        'tering',
        'pest',
        'mongool',
        'debiel',
        'idioot',
        'sukkel',
        'kankerlul',
        'kankerhond',

        // English
        'fuck',
        'shit',
        'bitch',
        'asshole',
        'bastard',
        'cunt',
        'dick',
        'prick',
        'whore',
        'slut',
        'motherfucker',
        'nigger',
    ],

];
