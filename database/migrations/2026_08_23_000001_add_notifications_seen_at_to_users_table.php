<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'notifications_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                // When this student last opened the notification bell.
                //
                // Announcements are one row shared by the whole school, so there
                // is no per-student read flag to set on them the way there is for
                // a database notification. This timestamp is the read marker
                // instead: anything posted after it is still unread for them.
                $table->timestamp('notifications_seen_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'notifications_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('notifications_seen_at');
            });
        }
    }
};
