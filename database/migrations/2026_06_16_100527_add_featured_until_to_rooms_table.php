<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "Uitgelicht" (featured) rooms surface first on the result page.
//   - is_featured:    the landlord's intent to feature this room.
//   - featured_until: hard expiry, set from the subscription period end and
//                     bumped on renewal — a safety net so a missed cancel
//                     webhook can't keep a room featured forever.
// A room counts as featured only while BOTH hold: is_featured && featured_until
// in the future. Splitting intent from expiry lets the renewal webhook extend
// rooms by intent, instead of losing them when the window lapses at renewal.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status');
            $table->timestamp('featured_until')->nullable()->after('is_featured');
            $table->index(['is_featured', 'featured_until']);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['is_featured', 'featured_until']);
            $table->dropColumn(['is_featured', 'featured_until']);
        });
    }
};
