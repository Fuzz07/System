<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Officers may revise a rejected proposal and send it back for approval.
 * resubmitted_at marks when they last did, so the admin can tell a
 * resubmission from a fresh proposal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('proposals', 'resubmitted_at')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->timestamp('resubmitted_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('proposals', 'resubmitted_at')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->dropColumn('resubmitted_at');
            });
        }
    }
};
