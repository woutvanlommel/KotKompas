<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            // Badkamer & Sanitair
            ['name' => 'Privébadkamer', 'category' => 'Badkamer & Sanitair'],
            ['name' => 'Gedeelde badkamer', 'category' => 'Badkamer & Sanitair'],
            ['name' => 'Douche', 'category' => 'Badkamer & Sanitair'],
            ['name' => 'Bad', 'category' => 'Badkamer & Sanitair'],
            ['name' => 'Toilet (privé)', 'category' => 'Badkamer & Sanitair'],
            ['name' => 'Toilet (gedeeld)', 'category' => 'Badkamer & Sanitair'],
            ['name' => 'Wastafel op kamer', 'category' => 'Badkamer & Sanitair'],

            // Keuken
            ['name' => 'Privékeuken', 'category' => 'Keuken'],
            ['name' => 'Gedeelde keuken', 'category' => 'Keuken'],
            ['name' => 'Kookplaat', 'category' => 'Keuken'],
            ['name' => 'Oven', 'category' => 'Keuken'],
            ['name' => 'Microgolfoven', 'category' => 'Keuken'],
            ['name' => 'Koelkast', 'category' => 'Keuken'],
            ['name' => 'Diepvriezer', 'category' => 'Keuken'],
            ['name' => 'Vaatwasser', 'category' => 'Keuken'],
            ['name' => 'Waterkoker', 'category' => 'Keuken'],
            ['name' => 'Koffiemachine', 'category' => 'Keuken'],

            // Internet & TV
            ['name' => 'Wifi', 'category' => 'Internet & TV'],
            ['name' => 'Glasvezel internet', 'category' => 'Internet & TV'],
            ['name' => 'Bekabeld internet (LAN)', 'category' => 'Internet & TV'],
            ['name' => 'Kabel-TV', 'category' => 'Internet & TV'],
            ['name' => 'TV op kamer', 'category' => 'Internet & TV'],

            // Meubilering
            ['name' => 'Gemeubileerd', 'category' => 'Meubilering'],
            ['name' => 'Bureau', 'category' => 'Meubilering'],
            ['name' => 'Bureaustoel', 'category' => 'Meubilering'],
            ['name' => 'Bed (eenpersoons)', 'category' => 'Meubilering'],
            ['name' => 'Bed (tweepersoons)', 'category' => 'Meubilering'],
            ['name' => 'Kast / kleerkast', 'category' => 'Meubilering'],
            ['name' => 'Nachttafel', 'category' => 'Meubilering'],
            ['name' => 'Zetel / fauteuil', 'category' => 'Meubilering'],
            ['name' => 'Boekenrek', 'category' => 'Meubilering'],

            // Klimaat & Comfort
            ['name' => 'Centrale verwarming', 'category' => 'Klimaat & Comfort'],
            ['name' => 'Elektrische verwarming', 'category' => 'Klimaat & Comfort'],
            ['name' => 'Airconditioning', 'category' => 'Klimaat & Comfort'],
            ['name' => 'Vloerverwarming', 'category' => 'Klimaat & Comfort'],
            ['name' => 'Ventilator', 'category' => 'Klimaat & Comfort'],
            ['name' => 'Dubbele beglazing', 'category' => 'Klimaat & Comfort'],
            ['name' => 'Zonnewering / gordijnen', 'category' => 'Klimaat & Comfort'],

            // Wasserij
            ['name' => 'Wasmachine (privé)', 'category' => 'Wasserij'],
            ['name' => 'Wasmachine (gedeeld)', 'category' => 'Wasserij'],
            ['name' => 'Droogkast', 'category' => 'Wasserij'],
            ['name' => 'Droogrek beschikbaar', 'category' => 'Wasserij'],
            ['name' => 'Strijkijzer & strijkplank', 'category' => 'Wasserij'],

            // Opslag & Berging
            ['name' => 'Fietsenstalling', 'category' => 'Opslag & Berging'],
            ['name' => 'Afgesloten fietsenstalling', 'category' => 'Opslag & Berging'],
            ['name' => 'Kelderberging', 'category' => 'Opslag & Berging'],
            ['name' => 'Zolderstockage', 'category' => 'Opslag & Berging'],
            ['name' => 'Parking (privé)', 'category' => 'Opslag & Berging'],
            ['name' => 'Parking (gedeeld)', 'category' => 'Opslag & Berging'],
            ['name' => 'Scooter/motorstalling', 'category' => 'Opslag & Berging'],

            // Gemeenschappelijke ruimtes
            ['name' => 'Gemeenschappelijke woonkamer', 'category' => 'Gemeenschappelijke ruimtes'],
            ['name' => 'Gemeenschappelijke eetkamer', 'category' => 'Gemeenschappelijke ruimtes'],
            ['name' => 'Studieruimte', 'category' => 'Gemeenschappelijke ruimtes'],
            ['name' => 'Tuin', 'category' => 'Gemeenschappelijke ruimtes'],
            ['name' => 'Terras / balkon (kamer)', 'category' => 'Gemeenschappelijke ruimtes'],
            ['name' => 'Gemeenschappelijk terras', 'category' => 'Gemeenschappelijke ruimtes'],
            ['name' => 'Barbecue', 'category' => 'Gemeenschappelijke ruimtes'],

            // Veiligheid
            ['name' => 'Rookmelder', 'category' => 'Veiligheid'],
            ['name' => 'CO-melder', 'category' => 'Veiligheid'],
            ['name' => 'Brandblusser', 'category' => 'Veiligheid'],
            ['name' => 'Videofoon / intercom', 'category' => 'Veiligheid'],
            ['name' => 'Beveiligde toegang (badge/code)', 'category' => 'Veiligheid'],
            ['name' => 'Camerabewaking (gemeenschappelijk)', 'category' => 'Veiligheid'],
            ['name' => 'Kluisje op kamer', 'category' => 'Veiligheid'],

            // Huisdieren & Diversen
            ['name' => 'Huisdieren toegestaan', 'category' => 'Diversen'],
            ['name' => 'Rookverbod', 'category' => 'Diversen'],
            ['name' => 'Roken toegestaan (buiten)', 'category' => 'Diversen'],
            ['name' => 'Lift aanwezig', 'category' => 'Diversen'],
            ['name' => 'Rolstoeltoegankelijk', 'category' => 'Diversen'],
            ['name' => 'Huisbewaarder / conciërge', 'category' => 'Diversen'],
        ];

        DB::table('facilities')->insert($facilities);
    }
}
