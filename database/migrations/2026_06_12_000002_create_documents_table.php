<?php

use App\Models\RentalPeriod;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(RentalPeriod::class)->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['school', 'identity', 'contract', 'other'])->default('other');
            $table->boolean('is_public')->default(false);
            $table->json('blocks')->nullable();             // enkel voor type=contract
            $table->string('status')->nullable();           // enkel voor type=contract: draft/signed/archived
            $table->longText('ocr_text')->nullable();       // voor later
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
