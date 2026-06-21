<?php

namespace Database\Seeders;

use App\Models\CostType;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomCostSeeder extends Seeder
{
    /**
     * Koppel een realistisch pakket kostenposten aan elke kamer.
     *
     * Vaste kern (alle types):
     *   - Internet             maandelijks, vast
     *   - Gemeenschappelijke kosten  maandelijks, vast
     *   - Fietsenstalling      maandelijks, vast
     *   - Brandverzekering     jaarlijks, vast
     *   - Huisvuil             jaarlijks, vast
     *   - Sleutelwaarborg      eenmalig, vast
     *
     * Extra voor kamers:
     *   - Poetsdienst gemeenschappelijke ruimtes   maandelijks
     *   - Gebruik wasmachine / droogkast            maandelijks
     *
     * Extra voor studio / appartement:
     *   - Elektriciteit   maandelijks (vast of variabel op naam huurder)
     *   - Water           maandelijks (vast of variabel)
     *   - Verwarming      maandelijks (vast) of Gas (variabel), keuze per kamer
     *
     * Willekeurig (deterministisch):
     *   - Parkeerplaats   25 % van de kamers   maandelijks
     *   - Kabel-TV        30 % van de kamers   maandelijks
     */
    private const SEED = 2026;

    public function run(): void
    {
        $rooms = Room::all();

        if ($rooms->isEmpty()) {
            $this->command->warn('Geen kamers gevonden — draai eerst RoomSeeder.');

            return;
        }

        // Index alle kostentypen op naam zodat we ID's opzoeken zonder N+1.
        $types = CostType::all()->keyBy('name');

        if ($types->isEmpty()) {
            $this->command->warn('Geen kostentypes gevonden — draai eerst CostTypeSeeder.');

            return;
        }

        mt_srand(self::SEED);

        $attached = 0;

        foreach ($rooms as $room) {
            $costs = [];

            // ── Vaste kern ────────────────────────────────────────────────

            $costs[$types['Internet']->id] = [
                'amount' => mt_rand(25, 35),
                'is_variable' => false,
                'frequency' => 'monthly',
                'description' => null,
            ];

            $costs[$types['Gemeenschappelijke kosten']->id] = [
                'amount' => mt_rand(15, 40),
                'is_variable' => false,
                'frequency' => 'monthly',
                'description' => null,
            ];

            $costs[$types['Fietsenstalling']->id] = [
                'amount' => mt_rand(5, 10),
                'is_variable' => false,
                'frequency' => 'monthly',
                'description' => null,
            ];

            $costs[$types['Brandverzekering']->id] = [
                'amount' => mt_rand(100, 200),
                'is_variable' => false,
                'frequency' => 'yearly',
                'description' => null,
            ];

            $costs[$types['Huisvuil / Vuilnisophaling']->id] = [
                'amount' => mt_rand(60, 120),
                'is_variable' => false,
                'frequency' => 'yearly',
                'description' => null,
            ];

            $costs[$types['Sleutelwaarborg']->id] = [
                'amount' => mt_rand(50, 100),
                'is_variable' => false,
                'frequency' => 'one_time',
                'description' => null,
            ];

            // ── Extra voor kamers ─────────────────────────────────────────

            if ($room->type === 'kamer') {
                $costs[$types['Poetsdienst gemeenschappelijke ruimtes']->id] = [
                    'amount' => mt_rand(10, 20),
                    'is_variable' => false,
                    'frequency' => 'monthly',
                    'description' => null,
                ];

                $costs[$types['Gebruik wasmachine / droogkast']->id] = [
                    'amount' => mt_rand(8, 18),
                    'is_variable' => false,
                    'frequency' => 'monthly',
                    'description' => null,
                ];
            }

            // ── Extra voor studio / appartement ───────────────────────────

            if (in_array($room->type, ['studio', 'appartement'])) {
                // Elektriciteit: 60 % vast, 40 % op naam huurder (variabel).
                $elecVariable = mt_rand(1, 100) <= 40;
                $costs[$types['Elektriciteit']->id] = [
                    'amount' => $elecVariable ? null : mt_rand(40, 80),
                    'is_variable' => $elecVariable,
                    'frequency' => 'monthly',
                    'description' => $elecVariable ? 'Op naam huurder' : null,
                ];

                // Water: 50 % vast, 50 % variabel.
                $waterVariable = mt_rand(1, 100) <= 50;
                $costs[$types['Water']->id] = [
                    'amount' => $waterVariable ? null : mt_rand(10, 20),
                    'is_variable' => $waterVariable,
                    'frequency' => 'monthly',
                    'description' => $waterVariable ? 'Op naam huurder' : null,
                ];

                // Verwarming of gas — kies één van beide.
                if (mt_rand(1, 100) <= 60) {
                    // Centrale verwarming: vast maandelijks bedrag.
                    $costs[$types['Verwarming']->id] = [
                        'amount' => mt_rand(30, 65),
                        'is_variable' => false,
                        'frequency' => 'monthly',
                        'description' => null,
                    ];
                } else {
                    // Individuele gasaansluiting: variabel op naam huurder.
                    $costs[$types['Gas']->id] = [
                        'amount' => null,
                        'is_variable' => true,
                        'frequency' => 'monthly',
                        'description' => 'Op naam huurder',
                    ];
                }
            }

            // ── Willekeurige extras ───────────────────────────────────────

            if (mt_rand(1, 100) <= 25) {
                $costs[$types['Parkeerplaats']->id] = [
                    'amount' => mt_rand(30, 60),
                    'is_variable' => false,
                    'frequency' => 'monthly',
                    'description' => null,
                ];
            }

            if (mt_rand(1, 100) <= 30) {
                $costs[$types['Kabel-TV']->id] = [
                    'amount' => mt_rand(10, 20),
                    'is_variable' => false,
                    'frequency' => 'monthly',
                    'description' => null,
                ];
            }

            // sync() vervangt eventuele bestaande koppelingen voor een
            // idempotente seeder (fresh:seed-proof).
            $room->costTypes()->sync($costs);

            $attached += count($costs);
        }

        mt_srand();

        $this->command->info("RoomCostSeeder: {$attached} kostenposten gekoppeld aan {$rooms->count()} kamers.");
    }
}
