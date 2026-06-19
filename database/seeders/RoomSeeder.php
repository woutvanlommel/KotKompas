<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * 60 kamers, ongelijk verdeeld over de 20 gebouwen (2-5 per gebouw), met
     * gevarieerde types (kamer / studio / appartement). Per kamer wordt een
     * cover + enkele galerijfoto's gekoppeld via publieke URL's (Lorem Picsum)
     * met addMediaFromUrl — dus niet vanuit de lokale map. Het ophalen is
     * resilient: faalt een URL, dan slaat hij die foto over zonder de seed te
     * breken. Zet SEED_ROOM_IMAGES=false in .env om foto's over te slaan.
     */
    private const TOTAL_ROOMS = 60;

    public function run(): void
    {
        $withImages = filter_var(env('SEED_ROOM_IMAGES', true), FILTER_VALIDATE_BOOL);

        $buildings = Building::all();

        if ($buildings->isEmpty()) {
            $this->command->warn('Geen gebouwen gevonden — draai eerst BuildingSeeder.');

            return;
        }

        $counts = $this->distributeRooms($buildings->count(), self::TOTAL_ROOMS);

        $created = 0;
        $imagesAttached = 0;

        foreach ($buildings as $i => $building) {
            for ($n = 1; $n <= $counts[$i]; $n++) {
                $type = $this->pickType();

                $room = Room::create(array_merge(
                    ['building_id' => $building->id, 'room_number' => $this->roomNumber($type, $n)],
                    $this->attributesForType($type),
                ));

                $created++;

                if ($withImages) {
                    $imagesAttached += $this->attachImages($room);
                }
            }
        }

        $this->command->info("RoomSeeder: {$created} kamers aangemaakt.");

        if ($withImages) {
            $this->command->info("Foto's gekoppeld: {$imagesAttached}.");
        } else {
            $this->command->warn("Foto's overgeslagen (SEED_ROOM_IMAGES=false).");
        }
    }

    /**
     * Verdeel $total kamers over $buildingsCount gebouwen: elk minstens 2,
     * max 5, som exact $total. Deterministisch via een vaste seed.
     *
     * @return list<int>
     */
    private function distributeRooms(int $buildingsCount, int $total): array
    {
        $counts = array_fill(0, $buildingsCount, 2);
        $remaining = $total - array_sum($counts);

        mt_srand(2026);
        while ($remaining > 0) {
            $i = mt_rand(0, $buildingsCount - 1);
            if ($counts[$i] < 5) {
                $counts[$i]++;
                $remaining--;
            }
        }
        mt_srand();

        return $counts;
    }

    private function pickType(): string
    {
        // Gewogen: kamers komen het vaakst voor in studentenhuisvesting.
        $roll = mt_rand(1, 100);

        return match (true) {
            $roll <= 50 => 'kamer',
            $roll <= 85 => 'studio',
            default => 'appartement',
        };
    }

    private function roomNumber(string $type, int $n): string
    {
        $prefix = match ($type) {
            'kamer' => 'K',
            'studio' => 'S',
            'appartement' => 'A',
        };

        return $prefix.$n;
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesForType(string $type): array
    {
        [$priceMin, $priceMax, $surfMin, $surfMax, $titles] = match ($type) {
            'kamer' => [300, 520, 10, 22, ['Gezellige kamer', 'Lichte kamer', 'Ruime kamer', 'Hoekkamer', 'Kamer met wastafel']],
            'studio' => [450, 780, 22, 42, ['Instapklare studio', 'Lichte studio', 'Moderne studio', 'Zolderstudio', 'Studio met balkon']],
            'appartement' => [680, 1150, 45, 95, ['Ruim appartement', 'Gerenoveerd appartement', 'Duplex-appartement', 'Appartement met terras']],
        };

        // Statusverdeling: meeste beschikbaar, enkele verhuurd, paar in onderhoud.
        $statusRoll = mt_rand(1, 100);
        $status = match (true) {
            $statusRoll <= 70 => 'available',
            $statusRoll <= 90 => 'rented',
            default => 'maintenance',
        };

        $price = mt_rand($priceMin, $priceMax);

        return [
            'type' => $type,
            'title' => $titles[array_rand($titles)],
            'description' => $this->description($type),
            'price_per_month' => $price,
            'deposit_amount' => mt_rand(0, 1) ? $price * 2 : null,
            'costs_included' => (bool) mt_rand(0, 1),
            'surface_m2' => mt_rand($surfMin, $surfMax),
            'is_furnished' => $type === 'kamer' ? (mt_rand(1, 100) <= 80) : (bool) mt_rand(0, 1),
            'available_from' => mt_rand(0, 1) ? now()->addDays(mt_rand(7, 120))->toDateString() : null,
            'status' => $status,
        ];
    }

    private function description(string $type): string
    {
        $base = match ($type) {
            'kamer' => 'Een nette studentenkamer met gedeelde keuken en sanitair. Vlot bereikbaar met fiets en openbaar vervoer.',
            'studio' => 'Een volledig ingerichte studio met eigen keuken en badkamer. Ideaal voor wie graag zijn eigen plek heeft.',
            'appartement' => 'Een ruim appartement met aparte slaapkamer(s) en leefruimte. Perfect om te delen of voor een koppel.',
        };

        return $base.' Inclusief internetaansluiting en toegang tot een afgesloten fietsenstalling.';
    }

    /**
     * Koppel een cover + galerijfoto's via publieke URL's. Resilient: een
     * mislukte download stopt de seed niet.
     */
    private function attachImages(Room $room): int
    {
        $attached = 0;

        $cover = "https://picsum.photos/seed/kotkompas-room-{$room->id}-cover/1024/768";
        try {
            $room->addMediaFromUrl($cover)->toMediaCollection('cover');
            $attached++;
        } catch (\Throwable $e) {
            $this->command->warn("  ✗ Coverfoto mislukt voor kamer #{$room->id}: {$e->getMessage()}");
        }

        for ($g = 1; $g <= 2; $g++) {
            $url = "https://picsum.photos/seed/kotkompas-room-{$room->id}-{$g}/1024/768";
            try {
                $room->addMediaFromUrl($url)->toMediaCollection('gallery');
                $attached++;
            } catch (\Throwable $e) {
                $this->command->warn("  ✗ Galerijfoto {$g} mislukt voor kamer #{$room->id}: {$e->getMessage()}");
            }
        }

        return $attached;
    }
}
