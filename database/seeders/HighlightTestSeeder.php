<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Top-up seeder (NOT a fresh seed) to test the "uitgelicht/highlight" feature:
 * features a few of the landlord's rooms (within their plan's featured slots)
 * and adds non-featured available rooms so the featured ones visibly rank first
 * with a badge on the public listing. Safe to leave existing data intact.
 *
 * Respecteert het plan: er worden nooit meer kamers uitgelicht dan
 * $landlord->featuredSlots() (premium = 3, pro = 1, starter/geen = 0).
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

        $slots = $landlord->featuredSlots();
        $this->command->info('Plan: '.($landlord->currentPlan()?->label() ?? 'geen').", featured slots: {$slots}.");

        if ($slots === 0) {
            $this->command->warn(
                'Geen featured slots beschikbaar — controleer of het abonnement resolved '
                .'(STRIPE_PRICE_* in .env). Er worden geen kamers uitgelicht.'
            );
        }

        // Feature available rooms tot het plan-maximum, indien nog niet actief.
        $alreadyFeatured = $landlord->featuredSlotsUsed();
        $toFeature = max(0, $slots - $alreadyFeatured);

        if ($toFeature > 0) {
            $candidates = Room::whereHas('building', fn ($q) => $q->where('landlord_id', $landlord->id))
                ->where('status', 'available')
                ->where('is_featured', false)
                ->orderByDesc('price_per_month')
                ->limit($toFeature)
                ->get();

            foreach ($candidates as $room) {
                $room->update([
                    'is_featured' => true,
                    'featured_until' => now()->addMonth(),
                ]);
                $this->command->line("  ★ Uitgelicht: #{$room->id} {$room->title} (€{$room->price_per_month})");
            }
        }

        // Report the state of existing featured rooms.
        $this->command->info('Uitgelichte koten na update:');
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
