<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Token invitations for the room score survey, created when a rental ends.
// The email that sends the link depends on "Template mail" (#28);
// until then the landlord (or the platform) shares the link manually.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();

            // Landlord snapshot at rental end — same principle as
            // room_reviews: scores must not shift on transfer.
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
