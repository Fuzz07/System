<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a comment reply to another one. Threads are one level deep, and
     * deleting a comment takes its replies with it.
     */
    public function up(): void
    {
        foreach (['announcement_comments', 'proposal_comments'] as $table) {
            if (!Schema::hasColumn($table, 'parent_id')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    $blueprint->foreignId('parent_id')->nullable()->after('user_id')
                        ->constrained($table)->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['announcement_comments', 'proposal_comments'] as $table) {
            if (Schema::hasColumn($table, 'parent_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropConstrainedForeignId('parent_id');
                });
            }
        }
    }
};
