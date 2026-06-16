<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "Uitgelicht" (featured) rooms surface first on the result page. A room is
// featured while featured_until lies in the future; the timestamp is set from
// the landlord's subscription renewal date, so featured status expires with
// the subscription instead of leaving stale promoted rooms behind.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->timestamp('featured_until')->nullable()->after('status');
            $table->index('featured_until');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['featured_until']);
            $table->dropColumn('featured_until');
        });
    }
};
