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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings');
            $table->string('room_number', 20);
            $table->enum('type', ['studio', 'one_bedroom', 'two_bedroom', 'three_bedroom', 'four_bedroom', 'five_plus_bedroom']);
            $table->string('title', 150)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price_per_month', 8, 2);
            $table->boolean('costs_included');
            $table->json('extra_costs')->nullable();
            $table->smallInteger('surface_m2')->nullable();
            $table->boolean('is_furnished');
            $table->date('available_from')->nullable();
            $table->enum('status', ['available', 'rented', 'maintenance', 'archived']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
