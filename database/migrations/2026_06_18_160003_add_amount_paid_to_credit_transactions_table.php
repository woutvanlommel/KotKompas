<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Het werkelijk betaalde bedrag (in CENTS, EUR) bij een aankoop — een
     * snapshot uit de Stripe-sessie, zodat we het later kunnen tonen zonder
     * live bij Stripe te bevragen en zonder afhankelijk te zijn van een
     * eventueel gewijzigde bundelprijs. Null voor verbruik-rijen.
     */
    public function up(): void
    {
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->unsignedInteger('amount_paid')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
        });
    }
};
