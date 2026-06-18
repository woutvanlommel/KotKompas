<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Asset auto-injectie uit
    |--------------------------------------------------------------------------
    | We bundelen Livewire + Alpine zelf via resources/js/app.ts (zie de
    | manual-bundling-aanpak). Daarom mag Livewire zijn eigen scripts NIET
    | auto-injecteren op de publieke pagina's — anders krijg je twee
    | Alpine-instanties ("Detected multiple instances of Alpine") en werken
    | wire:-directives niet binnen Alpine x-data subtrees.
    |
    | Filament laadt zijn eigen assets via @filamentScripts(withCore: true),
    | dus de admin-/dashboard-panels worden hier niet door geraakt.
    */
    'inject_assets' => false,
];
