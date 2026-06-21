<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomReview;
use App\Services\KotScoreService;
use Illuminate\Database\Seeder;

class ScoreSeeder extends Seeder
{
    /**
     * Maak reviews aan voor ~de helft van de kamers en herbereken daarna alle
     * scores via KotScoreService::recomputeAll(). Deterministisch via een
     * vaste seed zodat fresh:seed altijd hetzelfde resultaat geeft.
     *
     * Elke geselecteerde kamer krijgt 1–3 reviews (tenant_id = null — MySQL
     * staat meerdere NULL-rijen toe in een unieke index). Reviews ouder dan
     * 2 jaar tellen half in de Bayesiaanse score, wat variatie geeft.
     */
    private const SEED = 2026;

    public function run(): void
    {
        $rooms = Room::with('building.landlord')->get();

        if ($rooms->isEmpty()) {
            $this->command->warn('Geen kamers gevonden — draai eerst RoomSeeder.');

            return;
        }

        // Deterministisch: elke kamer met een even index krijgt reviews.
        $selected = $rooms->filter(fn ($room, $index) => $index % 2 === 0)->values();

        mt_srand(self::SEED);

        $reviewsCreated = 0;

        foreach ($selected as $room) {
            $landlordId = $room->building?->landlord_id;

            if (! $landlordId) {
                continue;
            }

            $count = mt_rand(1, 5);

            for ($i = 0; $i < $count; $i++) {
                // Varieer de datum: sommige reviews ouder dan 2 jaar (tellen half mee).
                $daysAgo = mt_rand(0, 900); // 0–2,5 jaar
                $createdAt = now()->subDays($daysAgo);

                RoomReview::create([
                    'room_id' => $room->id,
                    'landlord_id' => $landlordId,
                    'tenant_id' => null,
                    'score_hygiene' => mt_rand(2, 5),
                    'score_size' => mt_rand(2, 5),
                    'score_value' => mt_rand(1, 5),
                    'score_communication' => mt_rand(2, 5),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $reviewsCreated++;
            }
        }

        mt_srand();

        // Herbereken alle cached scores vanuit de zojuist aangemaakte reviews.
        app(KotScoreService::class)->recomputeAll();

        $this->command->info("ScoreSeeder: {$reviewsCreated} reviews aangemaakt voor {$selected->count()} kamers.");
        $this->command->info('Scores herberekend via KotScoreService.');
    }
}
