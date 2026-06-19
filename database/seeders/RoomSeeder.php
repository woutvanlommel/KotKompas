<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoomSeeder extends Seeder
{
    /**
     * 60 kamers, ongelijk verdeeld over de 20 gebouwen (2-5 per gebouw), met
     * gevarieerde types (kamer / studio / appartement).
     *
     * Per kamer worden foto's gekoppeld als rijen in de `media`-tabel, maar met
     * een externe URL (kolom `external_url`) i.p.v. een lokaal bestand. Er wordt
     * dus NIETS gedownload: enkel de link wordt opgeslagen, en het custom
     * App\Models\Media-model serveert die URL. De links zijn echte
     * interieurfoto's (Unsplash) passend bij het kamertype. Cover = 1 foto,
     * gallery = 2 foto's. Zet SEED_ROOM_IMAGES=false in .env om foto's over te
     * slaan.
     */
    private const TOTAL_ROOMS = 60;

    /**
     * Echte, publieke interieurfoto's (Unsplash CDN — stabiel hotlinkbaar),
     * gegroepeerd per kamertype zodat de beelden bij het type passen. Vervang
     * gerust door je eigen links.
     *
     * @var array<string, list<string>>
     */
    private const PHOTO_POOL = [
        // Studentenkamers / slaapkamers
        'kamer' => [
            'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1617104678098-de229db51175?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1505691723518-36a5ac3be353?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1616627561950-9f746e330187?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1616137466211-f939a420be84?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1631679706909-1844bbd07221?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1615873968403-89e068629265?auto=format&fit=crop&w=1024&q=80',
        ],
        // Studio's / kleine ingerichte ruimtes
        'studio' => [
            'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1567767292278-a4f21aa2d36e?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1484101403633-562f891dc89a?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1551516594-56cb78394645?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1502005229762-cf1b2da7c5d6?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1560448075-bb485b067938?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1505692433770-36f19f51681d?auto=format&fit=crop&w=1024&q=80',
        ],
        // Appartementen / leefruimtes
        'appartement' => [
            'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=1024&q=80',
            'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1024&q=80',
        ],
    ];

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
     * Koppel een cover + 2 galerijfoto's als media-rijen met een externe URL.
     * Niets wordt gedownload — enkel de link komt in de `media`-tabel
     * (kolom external_url). De foto's zijn echte interieurbeelden (Unsplash)
     * passend bij het kamertype, deterministisch gekozen per kamer.
     */
    private function attachImages(Room $room): int
    {
        $pool = self::PHOTO_POOL[$room->type] ?? self::PHOTO_POOL['kamer'];
        $count = count($pool);

        $this->createMediaRow($room, 'cover', $pool[$room->id % $count], 'Cover', 1);

        $attached = 1;
        for ($g = 1; $g <= 4; $g++) {
            $this->createMediaRow($room, 'gallery', $pool[($room->id + $g) % $count], "Foto {$g}", $g);
            $attached++;
        }

        return $attached;
    }

    /**
     * Maak een media-rij die naar een externe afbeelding wijst. De verplichte
     * Spatie-kolommen krijgen veilige defaults; `external_url` zorgt dat het
     * custom Media-model deze link serveert i.p.v. een disk-pad.
     */
    private function createMediaRow(Room $room, string $collection, string $url, string $name, int $order): void
    {
        $room->media()->create([
            'uuid' => (string) Str::uuid(),
            'collection_name' => $collection,
            'name' => $name,
            'file_name' => Str::slug($name).'.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'external_url' => $url,
            'size' => 0,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'order_column' => $order,
        ]);
    }
}
