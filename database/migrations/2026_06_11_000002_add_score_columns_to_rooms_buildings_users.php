<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cached scores — de bron van waarheid blijft room_reviews; deze kolommen
// worden bijgewerkt door KotScoreService (observer + dagelijkse recompute).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('score', 3, 2)->nullable()->after('status');
            // Bayesiaans gemiddelde: gebruikt voor ranking/filtering ("uitgelicht"),
            // zodat één 5-sterrenreview niet boven twintig 4,6-reviews uitkomt.
            $table->decimal('score_bayesian', 3, 2)->nullable()->after('score');
            $table->unsignedInteger('reviews_count')->default(0)->after('score_bayesian');
        });

        Schema::table('buildings', function (Blueprint $table) {
            $table->decimal('score', 3, 2)->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('landlord_score', 3, 2)->nullable();
            $table->unsignedInteger('landlord_reviews_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', fn (Blueprint $table) => $table->dropColumn(['score', 'score_bayesian', 'reviews_count']));
        Schema::table('buildings', fn (Blueprint $table) => $table->dropColumn(['score', 'reviews_count']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['landlord_score', 'landlord_reviews_count']));
    }
};
