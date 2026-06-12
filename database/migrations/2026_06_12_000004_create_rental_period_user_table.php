<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pivot tabel aanmaken
        Schema::create('rental_period_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['rental_period_id', 'user_id']);
        });

        // 2. Bestaande user_id waarden migreren naar pivot als primaire huurder
        DB::table('rental_periods')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->each(function ($rp) {
                DB::table('rental_period_user')->insert([
                    'rental_period_id' => $rp->id,
                    'user_id' => $rp->user_id,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        // 3. user_id kolom verwijderen uit rental_periods
        Schema::table('rental_periods', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        // Kolom herstellen
        Schema::table('rental_periods', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
        });

        // Data terugmigreren (enkel primaire huurder)
        DB::table('rental_period_user')
            ->where('is_primary', true)
            ->orderBy('rental_period_id')
            ->each(function ($pivot) {
                DB::table('rental_periods')
                    ->where('id', $pivot->rental_period_id)
                    ->update(['user_id' => $pivot->user_id]);
            });

        // Maak kolom NOT NULL (na data restore)
        Schema::table('rental_periods', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::dropIfExists('rental_period_user');
    }
};
