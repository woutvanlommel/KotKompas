<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('visibility')->default('private')->after('is_public');
            $table->foreignId('building_id')->nullable()->after('rental_period_id')->constrained()->nullOnDelete();
            $table->foreignId('shared_with_user_id')->nullable()->after('building_id')->constrained('users')->nullOnDelete();
        });

        // Preserve existing huurder shares: public non-contract docs become 'landlord'.
        DB::table('documents')
            ->where('is_public', true)
            ->where('type', '!=', 'contract')
            ->update(['visibility' => 'landlord']);
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('building_id');
            $table->dropConstrainedForeignId('shared_with_user_id');
            $table->dropColumn('visibility');
        });
    }
};
