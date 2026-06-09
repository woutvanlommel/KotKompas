<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('description', 100)->nullable();
            $table->timestamps();

            $table->unique(['facility_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_room');
    }
};
