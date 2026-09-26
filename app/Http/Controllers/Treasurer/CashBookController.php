<?php

namespace App\Http\Controllers\Treasurer;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\CashBookEntry;
use App\Services\CashBookReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CashBookController extends Controller
{
    public function index(Request $request)
    {
        $month = $this->month($request->query('month'));
        $report = CashBookReport::forMonth($month);

        // Beginning-balance entries are folded into the printed balance, but listed here so they can be edited.
        $entries = CashBookEntry::with('recorder')
            ->where('entry_date', '>=', $report->start->toDateString())
            ->where('entry_date', '<', $report->start->copy()->addMonth()->toDateString())
            ->orderBy('entry_date')->orderBy('id')
            ->get();

        return view('treasurer.cashbook.index', compact('month', 'report', 'entries'));
    }

    public function store(Request $request)
    {
        $entry = CashBookEntry::create($this->validatedEntry($request) + ['recorded_by' => Auth::id()]);

        SscHelper::logActivity(Auth::id(), 'CASHBOOK_ADD', "Recorded {$entry->type}: {$entry->particulars} (" . SscHelper::formatCurrency($entry->amount) . ')');
        return redirect()->route('treasurer.cashbook', ['month' => $entry->entry_date->format('Y-m')])
            ->with('success', 'Cash book entry recorded.');
    }

    public function update(Request $request, CashBookEntry $entry)
    {
        $entry->update($this->validatedEntry($request));

        SscHelper::logActivity(Auth::id(), 'CASHBOOK_UPDATE', "Updated cash book entry #{$entry->id}: {$entry->particulars}");
        return redirect()->route('treasurer.cashbook', ['month' => $entry->entry_date->format('Y-m')])
            ->with('success', 'Cash book entry updated.');
    }

    public function destroy(CashBookEntry $entry)
    {
        $month = $entry->entry_date->format('Y-m');
        $entry->delete();

        SscHelper::logActivity(Auth::id(), 'CASHBOOK_DELETE', "Deleted cash book entry #{$entry->id}: {$entry->particulars}");
        return redirect()->route('treasurer.cashbook', ['month' => $month])
            ->with('success', 'Cash book entry deleted.');
    }

    public function records(string $month)
    {
        return view('treasurer.cashbook.records', ['report' => CashBookReport::forMonth($month)]);
    }

    public function financialReport(string $month)
    {
        return view('treasurer.cashbook.financial-report', ['report' => CashBookReport::forMonth($month)]);
    }

    private function validatedEntry(Request $request): array
    {
        $data = $request->validate([
            'entry_date'   => 'required|date',
            'type'         => ['required', Rule::in(array_keys(CashBookEntry::TYPES))],
            'particulars'  => 'required|string|max:255',
            'reference_no' => 'nullable|string|max:100',
            'category'     => ['nullable', 'required_if:type,' . CashBookEntry::TYPE_EXPENSE, Rule::in(CashBookEntry::CATEGORIES)],
            'amount'       => 'required|numeric|min:0.01|max:9999999999',
        ], [
            'category.required_if' => 'Choose a category for the expense.',
        ]);

        if ($data['type'] !== CashBookEntry::TYPE_EXPENSE) {
            $data['category'] = null;
        }
        return $data;
    }

    /** The requested month as Y-m, falling back to the current month. */
    private function month(?string $month): string
    {
        return $month && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) ? $month : Carbon::now()->format('Y-m');
    }
}
