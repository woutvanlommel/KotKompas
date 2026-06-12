<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('building_poi_caches', function (Blueprint $table) {
            $table->id();
            $table->foreignID('building_id')->index()->constrained('buildings')->onDelete('cascade');
            $table->string('category');
            $table->string('name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11,8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_poi_caches');
    }
};
