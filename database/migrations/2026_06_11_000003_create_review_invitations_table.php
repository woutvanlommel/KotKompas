<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Token-uitnodigingen voor de kotscore-enquête, aangemaakt bij het stopzetten
// van een huur. De mail die de link verstuurt hangt aan "Template mail" (#28);
// tot die er is deelt de verhuurder (of het platform) de link handmatig.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();

            // Snapshot van de verhuurder bij het stopzetten — zelfde principe
            // als op room_reviews: de score mag niet verschuiven bij overdracht.
            $table->foreignId('landlord_id')->constrained('users');

            $table->foreignId('tenant_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_invitations');
    }
};
