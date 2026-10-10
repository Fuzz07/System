<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Semester reports need to know when a term ran. Budgets and contribution fee
 * payments already carry the academic term they belong to, but proposals,
 * releases, expenses and cash book entries are only dated, so the report scopes
 * those by the term's calendar window.
 *
 * Existing rows are backfilled from the usual local academic calendar (first
 * semester August-December, second semester January-July). Admins can correct
 * either date in Settings when a term actually ran longer or shorter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_years', function (Blueprint $table) {
            if (!Schema::hasColumn('school_years', 'starts_on')) {
                $table->date('starts_on')->nullable()->after('semester');
            }
            if (!Schema::hasColumn('school_years', 'ends_on')) {
                $table->date('ends_on')->nullable()->after('starts_on');
            }
        });

        foreach (DB::table('school_years')->get(['id', 'label', 'semester']) as $row) {
            if (!preg_match('/^(\d{4})-\d{4}$/', (string) $row->label, $matches)) {
                continue;
            }

            $startYear = (int) $matches[1];
            $isFirst = ($row->semester ?: 'first') === 'first';

            DB::table('school_years')->where('id', $row->id)->update([
                'starts_on' => $isFirst ? "{$startYear}-08-01" : ($startYear + 1) . '-01-01',
                'ends_on'   => $isFirst ? "{$startYear}-12-31" : ($startYear + 1) . '-07-31',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('school_years', function (Blueprint $table) {
            foreach (['starts_on', 'ends_on'] as $column) {
                if (Schema::hasColumn('school_years', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
