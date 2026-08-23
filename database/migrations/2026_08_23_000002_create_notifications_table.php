<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laravel's standard database-notifications table.
 *
 * The app has been calling $user->notify() (election opened, enrolment paid) and
 * reading $user->unreadNotifications() since those features were written, but the
 * table itself was never migrated -- every one of those calls was hitting a
 * missing table. The failures stayed invisible because the call sites either
 * swallowed the exception or were polled by a client that ignored errors.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
