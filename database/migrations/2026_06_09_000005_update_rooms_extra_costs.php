<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('extra_costs');
            $table->decimal('deposit_amount', 8, 2)->nullable()->after('price_per_month');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('deposit_amount');
            $table->json('extra_costs')->nullable();
        });
    }
};
