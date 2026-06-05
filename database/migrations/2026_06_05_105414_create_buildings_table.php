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
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->constrained('users');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('street');
            $table->bigInteger('house_number');
            $table->bigInteger('postal_code');
            $table->string('box')->nullable();
            $table->string('city');
            $table->string('city');
            $table->string('country');
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
