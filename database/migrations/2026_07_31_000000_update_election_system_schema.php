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
        // 1. Add election/voting state columns to school_years table
        Schema::table('school_years', function (Blueprint $table) {
            if (!Schema::hasColumn('school_years', 'voting_open')) {
                $table->boolean('voting_open')->default(false)->after('candidacy_open');
            }
            if (!Schema::hasColumn('school_years', 'voting_starts_at')) {
                $table->timestamp('voting_starts_at')->nullable()->after('voting_open');
            }
            if (!Schema::hasColumn('school_years', 'voting_ends_at')) {
                $table->timestamp('voting_ends_at')->nullable()->after('voting_starts_at');
            }
            if (!Schema::hasColumn('school_years', 'results_announced')) {
                $table->boolean('results_announced')->default(false)->after('voting_ends_at');
            }
        });

        // 2. Add ip_address and user_agent logging to votes table
        Schema::table('votes', function (Blueprint $table) {
            if (!Schema::hasColumn('votes', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('school_year');
            }
            if (!Schema::hasColumn('votes', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });

        // 3. Create student_ballots table to manage 1-minute time limit and session state securely
        if (!Schema::hasTable('student_ballots')) {
            Schema::create('student_ballots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('position');
                $table->string('school_year');
                $table->timestamp('started_at');
                $table->timestamp('submitted_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['user_id', 'position', 'school_year']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_ballots');

        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent']);
        });

        Schema::table('school_years', function (Blueprint $table) {
            $table->dropColumn(['voting_open', 'voting_starts_at', 'voting_ends_at', 'results_announced']);
        });
    }
};
