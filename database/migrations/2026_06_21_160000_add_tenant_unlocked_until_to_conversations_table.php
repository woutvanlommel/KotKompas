<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // A temporary reply window granted to a locked tenant when their
            // landlord messages them; null when no window is active.
            $table->timestamp('tenant_unlocked_until')->nullable()->after('notification_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('tenant_unlocked_until');
        });
    }
};
