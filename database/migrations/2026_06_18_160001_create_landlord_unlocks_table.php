<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entitlement: welke huurder welke VERHUURDER unlocked heeft.
     * Bewust op verhuurder-niveau (niet gebouw/kamer) → één unlock dekt
     * automatisch alle gebouwen van die verhuurder. Los van de credit-ledger:
     * de ledger is de geldstroom, deze tabel is de toegang.
     */
    public function up(): void
    {
        Schema::create('landlord_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
            // Link naar de -N ledgerrij waarmee dit betaald is (null = gratis/afgeleid toegekend).
            $table->foreignId('credit_transaction_id')->nullable()
                ->constrained('credit_transactions')->nullOnDelete();
            $table->timestamp('unlocked_at')->useCurrent();
            $table->timestamps();

            // Voorkomt dubbel unlocken / dubbel afschrijven.
            $table->unique(['tenant_id', 'landlord_id']);
            $table->index('landlord_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_unlocks');
    }
};
