<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\User;
use App\Services\GeocodingService;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    /**
     * 20 gebouwen, ongelijk verdeeld over de 5 verhuurders en gespreid over heel
     * Vlaanderen. Alle adressen bestaan echt (straat + postcode + stad).
     *
     * Coordinaten worden uitsluitend via de GeocodingService (Nominatim)
     * opgehaald tijdens het seeden — er staan bewust GEEN coordinaten hardcoded
     * in deze seeder. Zo zie je meteen of elk adres effectief resolved en kan
     * een verkeerde fallback-locatie nooit op de kaart belanden. De
     * BuildingObserver vuurt hier niet (DatabaseSeeder dempt events met
     * WithoutModelEvents), vandaar de directe service-call. Resolved een adres
     * niet, dan blijven latitude/longitude null en krijg je een waarschuwing.
     */
    public function run(): void
    {
        $geocoder = app(GeocodingService::class);
        $landlordEmails = [
            'verhuurder@kotkompas.be',
            'sofie.vermeulen@kotkompas.be',
            'karel.peeters@kotkompas.be',
            'inge.maes@kotkompas.be',
            'bram.janssens@kotkompas.be',
        ];

        $landlords = User::whereIn('email', $landlordEmails)
            ->get()
            ->keyBy('email');

        // [email, name, description, street, house_number, postal_code, city]
        $buildings = [
            // --- verhuurder@kotkompas.be (6) — Leuven & Limburg ---
            ['verhuurder@kotkompas.be', 'Studentenhuis Tiensestraat', 'Centraal gelegen studentenhuis op wandelafstand van het centrum en de campussen.', 'Tiensestraat', '102', '3000', 'Leuven'],
            ['verhuurder@kotkompas.be', 'Kot Naamsestraat', 'Klassiek Leuvens kot in een herenhuis vlak bij de Oude Markt.', 'Naamsestraat', '63', '3000', 'Leuven'],
            ['verhuurder@kotkompas.be', 'Residentie Parijsstraat', 'Rustig gelegen residentie met gedeelde keuken en fietsenstalling.', 'Parijsstraat', '24', '3000', 'Leuven'],
            ['verhuurder@kotkompas.be', 'Kot Koning Albert', 'Studentenkot in de bruisende winkelstraat van Hasselt.', 'Koning Albertstraat', '45', '3500', 'Hasselt'],
            ['verhuurder@kotkompas.be', 'Residentie Demerstraat', 'Modern gebouw met instapklare studio\'s in hartje Hasselt.', 'Demerstraat', '18', '3500', 'Hasselt'],
            ['verhuurder@kotkompas.be', 'Campuskot Universiteitslaan', 'Vlak bij de campus van UHasselt in Diepenbeek, ideaal voor studenten.', 'Universiteitslaan', '1', '3590', 'Diepenbeek'],

            // --- sofie.vermeulen@kotkompas.be (5) — Gent ---
            ['sofie.vermeulen@kotkompas.be', 'Kot Overpoort', 'Midden in de Gentse studentenbuurt, op een steenworp van het uitgaansleven.', 'Overpoortstraat', '41', '9000', 'Gent'],
            ['sofie.vermeulen@kotkompas.be', 'Residentie Sint-Pieters', 'Nieuwbouwresidentie vlak bij de campussen aan de Sint-Pietersnieuwstraat.', 'Sint-Pietersnieuwstraat', '120', '9000', 'Gent'],
            ['sofie.vermeulen@kotkompas.be', 'Studentenhuis Veldstraat', 'Boven de winkelstraat, centraal en goed verbonden met het openbaar vervoer.', 'Veldstraat', '78', '9000', 'Gent'],
            ['sofie.vermeulen@kotkompas.be', 'Kot Citadellaan', 'Groene omgeving naast het Citadelpark, rustig studeren gegarandeerd.', 'Citadellaan', '9', '9000', 'Gent'],
            ['sofie.vermeulen@kotkompas.be', 'Residentie Kortrijksesteenweg', 'Ruime kamers en studio\'s langs een vlotte verbindingsas.', 'Kortrijksesteenweg', '200', '9000', 'Gent'],

            // --- karel.peeters@kotkompas.be (4) — Antwerpen ---
            ['karel.peeters@kotkompas.be', 'Residentie Lange Leemstraat', 'In het bruisende Zuid, dicht bij UAntwerpen en talrijke horeca.', 'Lange Leemstraat', '187', '2018', 'Antwerpen'],
            ['karel.peeters@kotkompas.be', 'Kot Mechelsesteenweg', 'Stijlvol herenhuis omgebouwd tot studentenkamers nabij het station.', 'Mechelsesteenweg', '55', '2018', 'Antwerpen'],
            ['karel.peeters@kotkompas.be', 'Studentenhuis Nationalestraat', 'In het modekwartier, op wandelafstand van de Antwerpse campussen.', 'Nationalestraat', '96', '2000', 'Antwerpen'],
            ['karel.peeters@kotkompas.be', 'Kot Kammenstraat', 'Trendy buurt met veel winkels en cafés, centraal in de stad.', 'Kammenstraat', '44', '2000', 'Antwerpen'],

            // --- inge.maes@kotkompas.be (3) — West-Vlaanderen ---
            ['inge.maes@kotkompas.be', 'Residentie Langestraat', 'Historisch pand in het centrum van Brugge, charmant en centraal.', 'Langestraat', '102', '8000', 'Brugge'],
            ['inge.maes@kotkompas.be', 'Kot Doorniksestraat', 'Vlak bij de campussen van Howest en KU Leuven Kulak in Kortrijk.', 'Doorniksestraat', '58', '8500', 'Kortrijk'],
            ['inge.maes@kotkompas.be', 'Studentenhuis Ooststraat', 'In de winkelstraat van Roeselare, alle voorzieningen in de buurt.', 'Ooststraat', '34', '8800', 'Roeselare'],

            // --- bram.janssens@kotkompas.be (2) — Mechelen & Kempen ---
            ['bram.janssens@kotkompas.be', 'Kot Bruul', 'In de gezellige winkelstraat van Mechelen, vlot bereikbaar per trein.', 'Bruul', '71', '2800', 'Mechelen'],
            ['bram.janssens@kotkompas.be', 'Residentie Gasthuisstraat', 'Centraal in Turnhout, ideaal voor studenten van Thomas More.', 'Gasthuisstraat', '29', '2300', 'Turnhout'],
        ];

        $resolved = 0;
        $failed = [];

        foreach ($buildings as $index => [$email, $name, $description, $street, $houseNumber, $postalCode, $city]) {
            $building = Building::create([
                'landlord_id' => $landlords[$email]->id,
                'name' => $name,
                'description' => $description,
                'street' => $street,
                'house_number' => $houseNumber,
                'postal_code' => $postalCode,
                'city' => $city,
                'country' => 'BE',
            ]);

            // Respecteer de Nominatim usage policy (max 1 request/seconde).
            if ($index > 0) {
                sleep(1);
            }

            $coordinates = $geocoder->geocodeBuilding($building);

            if ($coordinates) {
                $building->update($coordinates);
                $resolved++;
                $this->command->line("  ✓ {$street} {$houseNumber}, {$postalCode} {$city} → {$coordinates['latitude']}, {$coordinates['longitude']}");
            } else {
                // Geen verkeerde fallback: lat/lng blijven null, adres resolved NIET.
                $failed[] = "{$street} {$houseNumber}, {$postalCode} {$city}";
                $this->command->warn("  ✗ Geen geocoding-resultaat voor {$street} {$houseNumber}, {$postalCode} {$city} — lat/lng blijven leeg.");
            }
        }

        $this->command->info("Geocoding: {$resolved}/".count($buildings).' adressen geresolved.');

        if ($failed !== []) {
            $this->command->warn('Niet geresolved (controleer deze adressen): '.implode(' | ', $failed));
        }
    }
}
