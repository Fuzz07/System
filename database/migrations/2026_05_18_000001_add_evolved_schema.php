<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds columns/tables that were introduced after the initial setup.sql
     * to ensure parity with the fully evolved native system.
     */
    public function up(): void
    {
        // Add project_status and completion_proof to proposals (added in later sessions)
        if (!Schema::hasColumn('proposals', 'project_status')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->enum('project_status', ['Ongoing', 'Completed'])->default('Ongoing')->after('admin_notes');
                $table->string('completion_proof', 255)->nullable()->after('project_status');
            });
        }

        // Add project_id to announcements (added for linking completed project announcements)
        if (!Schema::hasColumn('announcements', 'project_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->unsignedInteger('project_id')->nullable()->after('created_by');
            });
        }

        // Create proposal_comments table if it doesn't exist
        if (!Schema::hasTable('proposal_comments')) {
            Schema::create('proposal_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('proposal_id');
                $table->unsignedInteger('user_id');
                $table->text('comment');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_comments');

        if (Schema::hasColumn('proposals', 'project_status')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->dropColumn(['project_status', 'completion_proof']);
            });
        }

        if (Schema::hasColumn('announcements', 'project_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn('project_id');
            });
        }
    }
};
