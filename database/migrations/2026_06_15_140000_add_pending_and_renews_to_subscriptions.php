<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Gepland (uitgesteld) plan dat ingaat bij de volgende verlenging.
            $table->string('pending_stripe_price')->nullable()->after('stripe_price');
            // Volgende verleng-/verloopdatum, gesynct via webhook (geen live Stripe-call op runtime).
            $table->timestamp('renews_at')->nullable()->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['pending_stripe_price', 'renews_at']);
        });
    }
};
