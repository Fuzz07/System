<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetRelease;
use App\Models\CashBookEntry;
use App\Models\EnrollmentPayment;
use App\Models\Expense;
use App\Models\Liquidation;
use App\Models\Proposal;
use App\Models\SchoolYear;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Everything the SSC reports to students for one semester: what came in, what
 * was allocated, what was spent, and which projects the money went to.
 *
 * Two kinds of records make up a term. Budgets and contribution fee payments
 * store the academic term they belong to, so those are matched on the term
 * string (including the legacy label-only keys older rows used). Proposals,
 * releases, expenses, liquidations and cash book entries are only dated, so
 * those are matched on the term's calendar window, which an admin sets in
 * Settings and which otherwise falls back to the usual academic calendar.
 */
class SemesterReport
{
    public SchoolYear $term;
    /** Start of the term's calendar window; null when the label is malformed and no dates were set. */
    public ?Carbon $start;
    /** Last day of the term's calendar window, inclusive. */
    public ?Carbon $end;

    /** Approved funds allocated for the term. */
    public Collection $funds;
    public float $totalAllocated = 0.0;
    /** Approved officer expenses charged to those funds. */
    public float $totalSpent = 0.0;
    public float $totalRemaining = 0.0;

    /** Contribution fees students actually paid for the term. */
    public float $contributionsCollected = 0.0;
    public int $contributionPayers = 0;

    /** Projects proposed during the term, newest first, each with its released total. */
    public Collection $projects;
    public int $projectsProposed = 0;
    public int $projectsApproved = 0;
    public int $projectsCompleted = 0;
    public float $projectsRequested = 0.0;
    public float $projectsApprovedBudget = 0.0;
    /** Cash the treasurer handed out for projects during the term. */
    public float $totalReleased = 0.0;
    public int $releaseCount = 0;

    /** Approved expense lines charged to the term's funds, largest first. */
    public Collection $expenses;
    /** Expense category => amount, from the cash book, in print order. */
    public array $cashCategoryTotals = [];
    public float $cashCollections = 0.0;
    public float $cashExpenses = 0.0;

    public int $liquidationsFiled = 0;
    public int $liquidationsApproved = 0;

    public static function forTerm(SchoolYear $term): self
    {
        $report = new self;
        $report->term = $term;
        $report->start = $term->termStart();
        $report->end = $term->termEnd();
        $report->funds = collect();
        $report->projects = collect();
        $report->expenses = collect();

        $report->loadFunds($term->enrollmentTermKeys());
        $report->loadContributions($term->enrollmentTermKeys());
        $report->loadDatedRecords();

        return $report;
    }

    /** Funds and the expenses charged against them, matched on the academic term string. */
    private function loadFunds(array $termKeys): void
    {
        $this->funds = Budget::with('creator')
            ->whereIn('school_year', $termKeys)
            ->where('status', 'Approved')
            ->orderByDesc('allocated_amount')
            ->get();

        $this->totalAllocated = round((float) $this->funds->sum('allocated_amount'), 2);

        if ($this->funds->isEmpty()) {
            return;
        }

        // Matches the dashboards: only approved expense lines count as spent.
        $this->expenses = Expense::with(['budget', 'officer'])
            ->whereIn('budget_id', $this->funds->pluck('id'))
            ->where('status', 'Approved')
            ->orderByDesc('amount')
            ->get();

        $this->totalSpent = round((float) $this->expenses->sum('amount'), 2);
        $this->totalRemaining = round($this->totalAllocated - $this->totalSpent, 2);
    }

    private function loadContributions(array $termKeys): void
    {
        $paid = EnrollmentPayment::whereIn('semester', $termKeys)->where('status', 'paid');

        $this->contributionsCollected = round((float) $paid->clone()->sum('amount'), 2);
        $this->contributionPayers = (int) $paid->clone()->distinct('user_id')->count('user_id');
    }

    /** Projects, releases, liquidations and cash book entries falling inside the term window. */
    private function loadDatedRecords(): void
    {
        if (!$this->start || !$this->end) {
            return;
        }

        // Exclusive upper bound so DATE and DATETIME columns both read the whole last day.
        $from = $this->start->toDateString();
        $until = $this->end->copy()->addDay()->toDateString();

        $this->projects = Proposal::with('officer')
            ->withSum('releases', 'amount_released')
            ->where('created_at', '>=', $from)
            ->where('created_at', '<', $until)
            ->orderByDesc('created_at')
            ->get();

        $this->projectsProposed = $this->projects->count();
        $this->projectsApproved = $this->projects->where('status', 'Approved')->count();
        $this->projectsCompleted = $this->projects->where('project_status', 'Completed')->count();
        $this->projectsRequested = round((float) $this->projects->sum('requested_budget'), 2);
        $this->projectsApprovedBudget = round((float) $this->projects
            ->where('status', 'Approved')->sum('approved_budget'), 2);

        $releases = BudgetRelease::whereIn('release_status', ['Released', 'Partial'])
            ->where('created_at', '>=', $from)
            ->where('created_at', '<', $until);
        $this->totalReleased = round((float) $releases->clone()->sum('amount_released'), 2);
        $this->releaseCount = (int) $releases->clone()->count();

        $liquidations = Liquidation::where('created_at', '>=', $from)->where('created_at', '<', $until);
        $this->liquidationsFiled = (int) $liquidations->clone()->count();
        $this->liquidationsApproved = (int) $liquidations->clone()->where('status', 'Approved')->count();

        $this->loadCashBook($from, $until);
    }

    private function loadCashBook(string $from, string $until): void
    {
        $entries = CashBookEntry::whereIn('type', [CashBookEntry::TYPE_COLLECTION, CashBookEntry::TYPE_EXPENSE])
            ->where('entry_date', '>=', $from)
            ->where('entry_date', '<', $until)
            ->get();

        $this->cashCollections = round((float) $entries->where('type', CashBookEntry::TYPE_COLLECTION)->sum('amount'), 2);

        $cashExpenses = $entries->where('type', CashBookEntry::TYPE_EXPENSE);
        $this->cashExpenses = round((float) $cashExpenses->sum('amount'), 2);

        foreach (CashBookEntry::CATEGORIES as $category) {
            $items = $cashExpenses->where('category', $category);
            if ($items->isNotEmpty()) {
                $this->cashCategoryTotals[$category] = round((float) $items->sum('amount'), 2);
            }
        }
    }

    /** Share of the term's allocation that has been spent, for the progress bars. */
    public function spentPercent(): int
    {
        if ($this->totalAllocated <= 0) {
            return 0;
        }

        return min(100, (int) round($this->totalSpent / $this->totalAllocated * 100));
    }

    /** Share of approved project budgets the treasurer has already released. */
    public function releasedPercent(): int
    {
        if ($this->projectsApprovedBudget <= 0) {
            return 0;
        }

        return min(100, (int) round($this->totalReleased / $this->projectsApprovedBudget * 100));
    }

    /** True when the term has nothing to show yet, so the views can say so plainly. */
    public function isEmpty(): bool
    {
        return $this->totalAllocated <= 0
            && $this->contributionsCollected <= 0
            && $this->projects->isEmpty()
            && $this->cashCollections <= 0
            && $this->cashExpenses <= 0;
    }

    /**
     * Every term a report can be drawn for, most recent first.
     *
     * @return Collection<int, SchoolYear>
     */
    public static function reportableTerms(): Collection
    {
        return SchoolYear::newestTermFirst()->get();
    }

    /** The term to report on: the requested one, else the active one, else the most recent. */
    public static function resolveTerm(?string $termId): ?SchoolYear
    {
        $terms = self::reportableTerms();

        if ($termId !== null && $termId !== '') {
            $requested = $terms->firstWhere('id', (int) $termId);
            if ($requested) {
                return $requested;
            }
        }

        return $terms->firstWhere('is_active', true) ?? $terms->first();
    }
}
