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

            // Snapshot van de verhuurder op het moment van beoordelen: de
            // verhuurderscore mag niet verschuiven als een gebouw van eigenaar wisselt.
            $table->foreignId('landlord_id')->constrained('users');

            // Intern, voor dedup en fraudecontrole — wordt nooit getoond.
            $table->foreignId('tenant_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedTinyInteger('score_hygiene');
            $table->unsignedTinyInteger('score_size');
            $table->unsignedTinyInteger('score_value');
            $table->unsignedTinyInteger('score_communication');

            $table->timestamps();

            // Eén beoordeling per huurder per kot tot er een echt
            // huurperiode-model bestaat (Sprint 2) — dan kan dit per periode.
            $table->unique(['room_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_reviews');
    }
};
