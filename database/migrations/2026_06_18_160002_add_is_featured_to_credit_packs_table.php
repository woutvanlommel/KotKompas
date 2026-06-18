<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-gestuurd "uitgelicht"-accent op een bundel (de "Aanbevolen"-badge
     * op de credit-kooppagina). Geen automatische berekening meer.
     */
    public function up(): void
    {
        Schema::table('credit_packs', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('credit_packs', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
};
