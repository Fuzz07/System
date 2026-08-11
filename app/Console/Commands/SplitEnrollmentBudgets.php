<?php

namespace App\Console\Commands;

use App\Helpers\SscHelper;
use App\Models\Budget;
use App\Models\EnrollmentPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SplitEnrollmentBudgets extends Command
{
    protected $signature = 'ssc:split-enrollment-budgets
                            {--school-year= : School year label to rebuild (defaults to the active one)}
                            {--apply : Persist the changes; without this flag the command only reports}';

    protected $description = 'Rebuild the per-department "Enrollment Fees" budgets from paid enrollment payments.';

    public function handle(): int
    {
        $schoolYear = $this->option('school-year') ?: SscHelper::getActiveSchoolYear();
        $apply = (bool) $this->option('apply');

        if ($schoolYear === 'N/A') {
            $this->error('No active school year. Pass one explicitly with --school-year="2025-2026".');
            return self::FAILURE;
        }

        $this->info("School year: {$schoolYear}");
        $this->line($apply ? 'Mode: APPLY (changes will be written)' : 'Mode: dry run (re-run with --apply to write)');
        $this->newLine();

        $collected = $this->collectedByDepartment($schoolYear);

        if ($collected->isEmpty()) {
            $this->warn('No paid enrollment payments found for this school year. Nothing to do.');
            return self::SUCCESS;
        }

        $plan = [];
        $rows = [];

        foreach ($collected as $department => $total) {
            $budget = Budget::where('school_year', $schoolYear)
                ->where('title', Budget::enrollmentTitleFor($department))
                ->where('department', $department)
                ->first();

            // Preserve whatever the department has already spent so the
            // rebuild only corrects the allocated side of the ledger.
            $spent = $budget ? max(0, (float) $budget->allocated_amount - (float) $budget->remaining_balance) : 0.0;
            $remaining = max(0, $total - $spent);

            $plan[] = compact('department', 'total', 'remaining', 'budget');

            $rows[] = [
                $department,
                SscHelper::formatCurrency($budget ? (float) $budget->allocated_amount : 0.0),
                SscHelper::formatCurrency($total),
                SscHelper::formatCurrency($spent),
                SscHelper::formatCurrency($remaining),
                $budget ? 'update' : 'create',
            ];
        }

        if ($apply) {
            DB::transaction(function () use ($plan, $schoolYear) {
                foreach ($plan as $item) {
                    if ($item['budget']) {
                        $item['budget']->update([
                            'allocated_amount'  => $item['total'],
                            'remaining_balance' => $item['remaining'],
                        ]);
                        continue;
                    }

                    Budget::create([
                        'title'             => Budget::enrollmentTitleFor($item['department']),
                        'department'        => $item['department'],
                        'allocated_amount'  => $item['total'],
                        'remaining_balance' => $item['remaining'],
                        'school_year'       => $schoolYear,
                        'status'            => 'Pending',
                        'notes'             => 'Rebuilt from paid enrollment payments by ssc:split-enrollment-budgets.',
                    ]);
                }
            });
        }

        $this->table(['Department', 'Was Allocated', 'Now Allocated', 'Already Spent', 'New Remaining', 'Action'], $rows);

        $this->reportLegacyBudget($schoolYear);

        if ($apply) {
            SscHelper::logActivity(null, 'BUDGET_ENROLLMENT_SPLIT', "Rebuilt per-department enrollment budgets for {$schoolYear}");
            $this->info('Done. Per-department enrollment budgets now match the paid payments.');
        }

        return self::SUCCESS;
    }

    /** @return \Illuminate\Support\Collection<string, float> */
    protected function collectedByDepartment(string $schoolYear)
    {
        $totals = [];

        $rows = EnrollmentPayment::query()
            ->join('users', 'users.id', '=', 'enrollment_payments.user_id')
            ->where('enrollment_payments.semester', $schoolYear)
            ->where('enrollment_payments.status', 'paid')
            ->groupBy('users.department')
            ->selectRaw('users.department as department, SUM(enrollment_payments.amount) as amount')
            ->get();

        foreach ($rows as $row) {
            $department = Budget::normalizeDepartment($row->department);
            $totals[$department] = ($totals[$department] ?? 0.0) + (float) $row->amount;
        }

        ksort($totals);

        return collect($totals);
    }

    /**
     * The old flow pooled every fee into a single "Enrollment Fees" row. Once
     * the departments are rebuilt that row double-counts the same money, so
     * flag it instead of silently deleting a budget that may hold expenses.
     */
    protected function reportLegacyBudget(string $schoolYear): void
    {
        $legacy = Budget::where('school_year', $schoolYear)
            ->where('title', Budget::ENROLLMENT_TITLE_PREFIX)
            ->first();

        if (! $legacy) {
            return;
        }

        $this->newLine();
        $this->warn("Legacy pooled budget found: #{$legacy->id} \"{$legacy->title}\" ({$legacy->department}), "
            . 'allocated ' . SscHelper::formatCurrency((float) $legacy->allocated_amount) . '.');
        $this->warn('Its balance is now also counted in the per-department budgets above.');

        if ($legacy->expenses()->exists()) {
            $this->warn('It has expenses attached, so it cannot be deleted. Reconcile those expenses against '
                . 'the department budgets first, then zero it out manually.');
            return;
        }

        $this->warn('It has no expenses. Delete it from Budget Management to avoid double-counting.');
    }
}
