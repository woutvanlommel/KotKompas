<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Top-up seeder (NOT a fresh seed) to test the "uitgelicht/highlight" feature:
 * adds non-featured available rooms so featured ones visibly rank first with a
 * badge on the public listing. Safe to leave the existing data intact.
 */
class HighlightTestSeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::where('email', 'verhuurder@kotkompas.be')->first();

        if (! $landlord) {
            $this->command->warn('Verhuurder niet gevonden — niets toegevoegd.');

            return;
        }

        // Report the state of existing featured rooms (an expired featured_until
        // is the usual reason a "featured" room does not actually highlight).
        $this->command->info('Bestaande uitgelichte koten:');
        Room::whereHas('building', fn ($q) => $q->where('landlord_id', $landlord->id))
            ->where('is_featured', true)
            ->get()
            ->each(fn (Room $r) => $this->command->line(
                "  #{$r->id} {$r->title} — status={$r->status}, until="
                .($r->featured_until?->toDateString() ?? 'NULL')
                .', actief='.($r->featured_until?->isFuture() ? 'JA' : 'NEE')
            ));

        // Idempotency guard — don't stack duplicates on re-run.
        $alreadyAdded = Room::whereHas('building', fn ($q) => $q->where('landlord_id', $landlord->id))
            ->where('room_number', 'like', 'D9%')
            ->exists();

        if ($alreadyAdded) {
            $this->command->warn('Test-koten (D9x) bestaan al — overslaan om duplicaten te vermijden.');

            return;
        }

        $titles = ['Lichte studio', 'Ruime kamer', 'Kot met tuin', 'Studio centrum',
            'Gemeubelde kamer', 'Hoekkamer', 'Zolderstudio', 'Kamer met balkon'];

        $buildings = Building::where('landlord_id', $landlord->id)->get();
        $i = 0;

        foreach ($buildings as $building) {
            foreach (range(1, 2) as $n) {
                $i++;
                Room::factory()->for($building)->create([
                    'status' => 'available',
                    'is_featured' => false,
                    'featured_until' => null,
                    'room_number' => 'D'.(90 + $i),
                    'title' => $titles[($i - 1) % count($titles)],
                    'price_per_month' => 300 + $i * 45,
                ]);
            }
        }

        $this->command->info("Toegevoegd: {$i} niet-uitgelichte beschikbare koten.");
        $this->command->info('Publiek beschikbaar totaal: '.Room::where('status', 'available')->count());
        $this->command->info('Actief uitgelicht: '.Room::where('status', 'available')->where('is_featured', true)->where('featured_until', '>', now())->count());
    }
}
