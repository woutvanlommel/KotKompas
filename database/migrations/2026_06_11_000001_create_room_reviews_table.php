<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();

            // Landlord snapshot at review time: the landlord score must
            // not shift when a building changes ownership.
            $table->foreignId('landlord_id')->constrained('users');
            // Internal, for dedup and fraud control — never displayed.
            $table->foreignId('tenant_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedTinyInteger('score_hygiene');
            $table->unsignedTinyInteger('score_size');
            $table->unsignedTinyInteger('score_value');
            $table->unsignedTinyInteger('score_communication');

            $table->timestamps();

            // One review per tenant per room until a proper
            // rental-period model exists (Sprint 2) — then per period.
            $table->unique(['room_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_reviews');
    }
};
