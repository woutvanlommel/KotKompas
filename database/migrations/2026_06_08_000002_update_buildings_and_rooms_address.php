<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('house_number')->change();
            $table->string('postal_code')->change();
            $table->renameColumn('box', 'bus');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->string('bus', 20)->nullable()->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->bigInteger('house_number')->change();
            $table->bigInteger('postal_code')->change();
            $table->renameColumn('bus', 'box');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('bus');
        });
    }
};
