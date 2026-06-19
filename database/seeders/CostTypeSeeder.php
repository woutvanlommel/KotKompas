<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CostTypeSeeder extends Seeder
{
    /**
     * Logische, relevante kostentypes voor studentenhuisvesting. Bewust
     * afgeslankt: enkel kosten die in een kot/studio/appartement realistisch
     * los van de huur worden aangerekend.
     */
    public function run(): void
    {
        $costTypes = [
            // Nutsvoorzieningen
            ['name' => 'Elektriciteit', 'category' => 'Nutsvoorzieningen'],
            ['name' => 'Water', 'category' => 'Nutsvoorzieningen'],
            ['name' => 'Gas', 'category' => 'Nutsvoorzieningen'],
            ['name' => 'Verwarming', 'category' => 'Nutsvoorzieningen'],

            // Internet & Media
            ['name' => 'Internet', 'category' => 'Internet & Media'],
            ['name' => 'Kabel-TV', 'category' => 'Internet & Media'],

            // Gemeenschappelijke kosten
            ['name' => 'Gemeenschappelijke kosten', 'category' => 'Gemeenschappelijke kosten'],
            ['name' => 'Poetsdienst gemeenschappelijke ruimtes', 'category' => 'Gemeenschappelijke kosten'],
            ['name' => 'Onderhoud gemeenschappelijke ruimtes', 'category' => 'Gemeenschappelijke kosten'],

            // Verzekeringen
            ['name' => 'Brandverzekering', 'category' => 'Verzekeringen'],

            // Belastingen & Taksen
            ['name' => 'Huisvuil / Vuilnisophaling', 'category' => 'Belastingen & Taksen'],

            // Parking & Mobiliteit
            ['name' => 'Parkeerplaats', 'category' => 'Parking & Mobiliteit'],
            ['name' => 'Fietsenstalling', 'category' => 'Parking & Mobiliteit'],

            // Diversen
            ['name' => 'Sleutelwaarborg', 'category' => 'Diversen'],
            ['name' => 'Gebruik wasmachine / droogkast', 'category' => 'Diversen'],
        ];

        DB::table('cost_types')->insert($costTypes);
    }
}
