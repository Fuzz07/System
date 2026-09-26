<?php

namespace App\Services;

use App\Models\CashBookEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Monthly figures behind the treasurer's "Records of Expenses" and "Financial Report".
 */
class CashBookReport
{
    public Carbon $start;
    public Carbon $end;
    /** Cash on hand at the start of the month, including beginning-balance entries dated within it. */
    public float $beginningBalance;
    /** Collections and expenses dated within the month, oldest first. */
    public Collection $entries;
    public float $totalCollections;
    public float $totalExpenses;
    /** Expense category => amount, only categories used this month, in print order. */
    public array $categoryTotals;
    /** Collection particulars => amount. */
    public array $collectionTotals;

    /** @param string $month Month in Y-m format, e.g. 2026-07. */
    public static function forMonth(string $month): self
    {
        $report = new self;
        $report->start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        $report->end = $report->start->copy()->endOfMonth();

        // Compare against the first day of each month so both DATE and DATETIME storage work.
        $monthStart = $report->start->toDateString();
        $nextMonthStart = $report->start->copy()->addMonth()->toDateString();

        $opening = (float) CashBookEntry::where('type', CashBookEntry::TYPE_OPENING)
            ->where('entry_date', '<', $nextMonthStart)->sum('amount');
        $collectedBefore = (float) CashBookEntry::where('type', CashBookEntry::TYPE_COLLECTION)
            ->where('entry_date', '<', $monthStart)->sum('amount');
        $spentBefore = (float) CashBookEntry::where('type', CashBookEntry::TYPE_EXPENSE)
            ->where('entry_date', '<', $monthStart)->sum('amount');
        $report->beginningBalance = round($opening + $collectedBefore - $spentBefore, 2);

        $report->entries = CashBookEntry::whereIn('type', [CashBookEntry::TYPE_COLLECTION, CashBookEntry::TYPE_EXPENSE])
            ->where('entry_date', '>=', $monthStart)
            ->where('entry_date', '<', $nextMonthStart)
            ->orderBy('entry_date')->orderBy('id')
            ->get();

        $collections = $report->entries->where('type', CashBookEntry::TYPE_COLLECTION);
        $expenses = $report->entries->where('type', CashBookEntry::TYPE_EXPENSE);
        $report->totalCollections = round((float) $collections->sum('amount'), 2);
        $report->totalExpenses = round((float) $expenses->sum('amount'), 2);

        $report->categoryTotals = [];
        foreach (CashBookEntry::CATEGORIES as $category) {
            $items = $expenses->where('category', $category);
            if ($items->isNotEmpty()) {
                $report->categoryTotals[$category] = round((float) $items->sum('amount'), 2);
            }
        }

        $report->collectionTotals = $collections
            ->groupBy(fn ($entry) => trim($entry->particulars))
            ->map(fn ($group) => round((float) $group->sum('amount'), 2))
            ->all();

        return $report;
    }

    public function endingBalance(): float
    {
        return round($this->beginningBalance + $this->totalCollections - $this->totalExpenses, 2);
    }

    /** Cash DR column total: the balance brought forward plus the month's collections. */
    public function cashDebitTotal(): float
    {
        return round($this->beginningBalance + $this->totalCollections, 2);
    }

    public function previousMonthName(): string
    {
        return $this->start->copy()->subMonth()->format('F');
    }
}
