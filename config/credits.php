<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kosten per actie (in credits)
    |--------------------------------------------------------------------------
    | Wat een actie aan credits kost. Generiek gehouden zodat we overal
    | CreditService::spend($user, $amount, $reason) kunnen aanroepen.
    */

    // Aantal credits om de kaart van één verhuurder te unlocken
    // (geldt meteen voor al diens gebouwen/kamers).
    'unlock_landlord_cost' => (int) env('CREDITS_UNLOCK_LANDLORD_COST', 2),
];
