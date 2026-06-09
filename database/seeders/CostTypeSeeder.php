<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CostTypeSeeder extends Seeder
{
    public function run(): void
    {
        $costTypes = [
            // Nutsvoorzieningen
            ['name' => 'Water / Gas / Elektriciteit', 'category' => 'Nutsvoorzieningen'],
            ['name' => 'Water', 'category' => 'Nutsvoorzieningen'],
            ['name' => 'Elektriciteit', 'category' => 'Nutsvoorzieningen'],
            ['name' => 'Aardgas', 'category' => 'Nutsvoorzieningen'],
            ['name' => 'Stookolie', 'category' => 'Nutsvoorzieningen'],
            ['name' => 'Warmtelevering (stadsverwarming)', 'category' => 'Nutsvoorzieningen'],

            // Internet & Media
            ['name' => 'Internet', 'category' => 'Internet & Media'],
            ['name' => 'Kabel-TV', 'category' => 'Internet & Media'],
            ['name' => 'Telefoonabonnement', 'category' => 'Internet & Media'],

            // Gemeenschappelijke kosten
            ['name' => 'Gemeenschappelijke kosten', 'category' => 'Gemeenschappelijke kosten'],
            ['name' => 'Syndic', 'category' => 'Gemeenschappelijke kosten'],
            ['name' => 'Reinigingskosten', 'category' => 'Gemeenschappelijke kosten'],
            ['name' => 'Onderhoud gemeenschappelijke ruimtes', 'category' => 'Gemeenschappelijke kosten'],
            ['name' => 'Lift onderhoud', 'category' => 'Gemeenschappelijke kosten'],
            ['name' => 'Tuinonderhoud', 'category' => 'Gemeenschappelijke kosten'],

            // Verzekeringen
            ['name' => 'Brandverzekering', 'category' => 'Verzekeringen'],
            ['name' => 'Huurdersaansprakelijkheid', 'category' => 'Verzekeringen'],

            // Belastingen & Taksen
            ['name' => 'Onroerende voorheffing (aandeel)', 'category' => 'Belastingen & Taksen'],
            ['name' => 'Vuilnisophaling', 'category' => 'Belastingen & Taksen'],
            ['name' => 'Gemeentebelasting', 'category' => 'Belastingen & Taksen'],

            // Parking & Mobiliteit
            ['name' => 'Parkingplaats', 'category' => 'Parking & Mobiliteit'],
            ['name' => 'Garagebox', 'category' => 'Parking & Mobiliteit'],
            ['name' => 'Fietsenstalling (afgesloten)', 'category' => 'Parking & Mobiliteit'],

            // Diversen
            ['name' => 'Sleutelwaarborg', 'category' => 'Diversen'],
            ['name' => 'Badge / toegangskaart', 'category' => 'Diversen'],
            ['name' => 'Was- en droogkosten (gedeelde machines)', 'category' => 'Diversen'],
            ['name' => 'Berging / kelderruimte', 'category' => 'Diversen'],
            ['name' => 'Huisregels administratiekost', 'category' => 'Diversen'],
        ];

        DB::table('cost_types')->insert($costTypes);
    }
}
