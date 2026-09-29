<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Year levels now follow the school year: activating a later school year moves
 * every student up a level and archives the 4th years as graduates.
 *
 * - users.archived_at / graduated_school_year: graduates leave the active roster
 *   without being confused with sign-ups still waiting for approval, which are
 *   also "inactive".
 * - school_years.students_promoted_at: marks the school years students have
 *   already been moved into, so switching semesters or re-activating a year
 *   never promotes anyone twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'archived_at')) {
                $table->timestamp('archived_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'graduated_school_year')) {
                $table->string('graduated_school_year', 20)->nullable();
            }
        });

        Schema::table('school_years', function (Blueprint $table) {
            if (!Schema::hasColumn('school_years', 'students_promoted_at')) {
                $table->timestamp('students_promoted_at')->nullable();
            }
        });

        // The year levels on record already describe the term that is active
        // today, so that term is the starting point for future promotions.
        DB::table('school_years')->where('is_active', 1)->update(['students_promoted_at' => now()]);

        // Students already marked as graduates belong in the archive too.
        DB::table('users')
            ->where('role', 'student')
            ->whereNull('archived_at')
            ->whereIn(DB::raw('LOWER(year_level)'), ['graduated', 'alumni'])
            ->update(['archived_at' => now(), 'status' => 'inactive']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['archived_at', 'graduated_school_year'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('school_years', function (Blueprint $table) {
            if (Schema::hasColumn('school_years', 'students_promoted_at')) {
                $table->dropColumn('students_promoted_at');
            }
        });
    }
};
