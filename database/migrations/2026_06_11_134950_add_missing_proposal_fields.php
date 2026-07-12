<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            if (!Schema::hasColumn('proposals', 'proposal_event_date')) {
                $table->string('proposal_event_date', 100)->nullable()->after('description');
            }
            if (!Schema::hasColumn('proposals', 'participant_count')) {
                $table->integer('participant_count')->nullable()->after('proposal_event_date');
            }
            if (!Schema::hasColumn('proposals', 'objectives')) {
                $table->text('objectives')->nullable()->after('participant_count');
            }
            if (!Schema::hasColumn('proposals', 'budget_items')) {
                $table->text('budget_items')->nullable()->after('objectives');
            }
            if (!Schema::hasColumn('proposals', 'project_image')) {
                $table->string('project_image', 255)->nullable()->after('completion_proof');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn([
                'proposal_event_date',
                'participant_count',
                'objectives',
                'budget_items',
                'project_image'
            ]);
        });
    }
};
