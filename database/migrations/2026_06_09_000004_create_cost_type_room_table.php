<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_type_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 8, 2)->nullable();
            $table->boolean('is_variable')->default(false)->comment('Kost niet inbegrepen maar geen vaste prijs (bv. verbruik op naam huurder)');
            $table->enum('frequency', ['monthly', 'yearly', 'one_time']);
            $table->string('description', 100)->nullable();
            $table->timestamps();

            $table->unique(['cost_type_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_type_room');
    }
};
