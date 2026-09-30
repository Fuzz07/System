<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The student fee is now called the "Contribution Fee". Budget rows that hold
 * it are found by title (Budget::ENROLLMENT_TITLE_PREFIX), so existing rows are
 * renamed along with the constant: "Enrollment Fees" and
 * "Enrollment Fees - BSIT" become "Contribution Fees" and
 * "Contribution Fees - BSIT".
 */
return new class extends Migration
{
    private const OLD = 'Enrollment Fees';
    private const NEW = 'Contribution Fees';
    private const OLD_NOTE = 'Consolidated enrollment fees collection for all departments.';
    private const NEW_NOTE = 'Consolidated contribution fees collection for all departments.';

    public function up(): void
    {
        $this->rename(self::OLD, self::NEW, self::OLD_NOTE, self::NEW_NOTE);
    }

    public function down(): void
    {
        $this->rename(self::NEW, self::OLD, self::NEW_NOTE, self::OLD_NOTE);
    }

    private function rename(string $from, string $to, string $fromNote, string $toNote): void
    {
        DB::table('budgets')->where('title', $from)->update(['title' => $to]);

        DB::table('budgets')->where('title', 'like', $from . ' - %')->get(['id', 'title'])
            ->each(fn ($budget) => DB::table('budgets')->where('id', $budget->id)
                ->update(['title' => $to . substr($budget->title, strlen($from))]));

        DB::table('budgets')->where('notes', $fromNote)->update(['notes' => $toNote]);
    }
};
