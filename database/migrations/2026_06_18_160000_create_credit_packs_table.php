<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Credit-bundels: admin-beheerd. Je kan geen losse credits kopen,
     * enkel per bundel. Prijs wordt ad-hoc aan Stripe Checkout meegegeven
     * (inline price_data) — geen Stripe-product nodig.
     */
    public function up(): void
    {
        Schema::create('credit_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // weergavenaam, bv. "100 credits"
            $table->unsignedInteger('credits');           // aantal credits in de bundel
            $table->unsignedInteger('price');             // prijs in CENTS (EUR)
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_packs');
    }
};
