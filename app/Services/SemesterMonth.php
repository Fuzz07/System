<?php

namespace App\Services;

use App\Models\CashBookEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * One month of a semester's cash book, as the "Records of Expenses" sheet prints
 * it: the entries dated in that month, its own totals per expense category, and
 * the cash balance carried in from the month before.
 *
 * A month is clipped to the part of it the term actually covers, so the month
 * subtotals always add up to the semester totals even when an admin sets a term
 * to start or end mid-month.
 */
class SemesterMonth
{
    /** First day of the calendar month, used for the month's name. */
    public Carbon $monthStart;
    /** First day of the month the term covers (the month's own start unless the term begins mid-month). */
    public Carbon $coverageStart;
    /** Last day of the month the term covers, inclusive. */
    public Carbon $coverageEnd;
    /** Collections and expenses dated in the covered range, oldest first. */
    public Collection $entries;
    /** Cash on hand entering the month, carried forward from the previous one. */
    public float $beginningBalance = 0.0;
    public float $totalCollections = 0.0;
    public float $totalExpenses = 0.0;
    /** Expense category => amount for this month only. */
    public array $categoryTotals = [];

    public function __construct(Carbon $monthStart, Carbon $coverageStart, Carbon $coverageEnd, Collection $entries)
    {
        $this->monthStart = $monthStart;
        $this->coverageStart = $coverageStart;
        $this->coverageEnd = $coverageEnd;
        $this->entries = $entries;

        $collections = $entries->where('type', CashBookEntry::TYPE_COLLECTION);
        $expenses = $entries->where('type', CashBookEntry::TYPE_EXPENSE);

        $this->totalCollections = round((float) $collections->sum('amount'), 2);
        $this->totalExpenses = round((float) $expenses->sum('amount'), 2);

        foreach (CashBookEntry::CATEGORIES as $category) {
            $items = $expenses->where('category', $category);
            if ($items->isNotEmpty()) {
                $this->categoryTotals[$category] = round((float) $items->sum('amount'), 2);
            }
        }
    }

    /** "October 2026". */
    public function label(): string
    {
        return $this->monthStart->format('F Y');
    }

    /** "2026-10", for linking to the single-month cash book sheets. */
    public function key(): string
    {
        return $this->monthStart->format('Y-m');
    }

    /** True when the term starts or ends part-way through this month. */
    public function isPartial(): bool
    {
        return !$this->coverageStart->isSameDay($this->monthStart)
            || !$this->coverageEnd->isSameDay($this->monthStart->copy()->endOfMonth());
    }

    /** "October 3 - 31, 2026", shown only when the month is partly covered. */
    public function coverageLabel(): string
    {
        return $this->coverageStart->format('F j') . ' - ' . $this->coverageEnd->format('F j, Y');
    }

    /** What this month hands on to the next one. */
    public function endingBalance(): float
    {
        return round($this->beginningBalance + $this->totalCollections - $this->totalExpenses, 2);
    }

    /** Cash DR column total: the balance brought forward plus this month's collections. */
    public function cashDebitTotal(): float
    {
        return round($this->beginningBalance + $this->totalCollections, 2);
    }

    public function previousMonthName(): string
    {
        return $this->monthStart->copy()->subMonth()->format('F');
    }

    /** The month's amount for one category, or null when it had none. */
    public function categoryTotal(string $category): ?float
    {
        return $this->categoryTotals[$category] ?? null;
    }
}
