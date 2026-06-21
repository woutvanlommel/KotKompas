<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Enkel ter weergave (admin + abonnement-kaarten). Stripe blijft de
            // bron voor wat werkelijk afgerekend wordt; dit bedrag moet je daar
            // dus zelf gelijk houden.
            $table->decimal('monthly_price', 8, 2)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('monthly_price');
        });
    }
};
