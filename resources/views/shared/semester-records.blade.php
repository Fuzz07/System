{{--
  The semester's Records of Expenses: one printed ledger per month the term
  covers, each listing its own entries line by line, followed by a semester
  grand total. Expects $report (App\Services\SemesterReport).
--}}
@extends('treasurer.cashbook.print-layout')

@php
    $num = fn ($amount) => number_format((float) $amount, 2);
    // Columns stay the same across every month so the sheets line up and the
    // grand total row below them adds up column by column.
    $categories = array_keys($report->cashCategoryTotals);
    // Header colours follow the paper Records of Expenses.
    $categoryColors = [
        'Cash Advances'                => '#c7c7c7',
        'Office Supplies/Equipments'   => '#f6cbb7',
        'Office Meals/Snacks'          => '#9fd3e6',
        'Event Supplies'               => '#f5c6d6',
        'Traveling and Transportation' => '#9ec2e6',
        "Officers' Uniform"            => '#5fae84',
        'Subsidy'                      => '#5a9bd4',
    ];
    $title = 'Records of Expenses for ' . $report->term->academic_term;
    $columnCount = 7 + count($categories);
@endphp

@section('title', $title)
@section('page-size', 'legal landscape')

@section('styles')
    :root { --sheet-width: 1200px; }
    .records-title { font-size: 1.1rem; font-weight: 900; margin: 0 0 4px; text-align: center; text-transform: uppercase; }
    .records-subtitle { font-size: .86rem; margin: 0 0 18px; text-align: center; }
    .month-block { margin-bottom: 26px; }
    .month-heading { font-size: .92rem; font-weight: 800; margin: 0 0 6px; text-transform: uppercase; }
    .month-heading .partial { font-size: .76rem; font-weight: 400; text-transform: none; }
    .records-table { border-collapse: collapse; font-size: .74rem; width: 100%; }
    .records-table th, .records-table td { border: 1px solid #111827; padding: 3px 6px; }
    .records-table th { font-weight: 800; text-align: center; }
    .records-table .head { background: #facc15; text-transform: uppercase; }
    .records-table .dr { background: #f59e0b; }
    .records-table .cr { background: #4d9f3a; color: #fff; }
    .records-table .particulars { min-width: 230px; text-align: center; }
    .records-table .center { text-align: center; }
    .records-table .total-row td { background: #fde68a; font-weight: 800; }
    .records-table .empty-row td { color: #6b7280; padding: 8px; }
    .grand-total-table .total-row td { background: #fca5a5; }
    @media print {
        .month-block { break-inside: avoid; page-break-inside: avoid; }
    }
@endsection

@section('content')
    <h1 class="records-title">{{ $title }}</h1>
    <p class="records-subtitle">Covering {{ $report->term->termRangeLabel() }}</p>

    @forelse ($report->months as $month)
        <div class="month-block">
            <h2 class="month-heading">
                {{ $month->label() }}
                @if ($month->isPartial())
                    <span class="partial">({{ $month->coverageLabel() }})</span>
                @endif
            </h2>

            <table class="records-table">
                <thead>
                    <tr>
                        <th class="head" colspan="2">Date</th>
                        <th class="head" rowspan="2">Particulars</th>
                        <th class="head" rowspan="2">OR/AR No.</th>
                        <th class="head" colspan="2">Cash</th>
                        <th class="head">Fees</th>
                        @foreach ($categories as $category)
                            <th style="background: {{ $categoryColors[$category] ?? '#e5e7eb' }};">{{ $category }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th class="head" colspan="2">{{ $month->monthStart->format('Y') }}</th>
                        <th class="dr">DR</th>
                        <th class="cr">CR</th>
                        <th class="cr">CR</th>
                        @foreach ($categories as $category)
                            <th class="dr">DR</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="particulars">Total Cash Balance from {{ $month->previousMonthName() }}</td>
                        <td></td>
                        <td class="amount">{{ $num($month->beginningBalance) }}</td>
                        <td></td>
                        <td class="amount">{{ $num($month->beginningBalance) }}</td>
                        @foreach ($categories as $category)
                            <td></td>
                        @endforeach
                    </tr>

                    @php $previousDate = null; @endphp
                    @foreach ($month->entries as $entry)
                        @php
                            $isExpense = $entry->type === \App\Models\CashBookEntry::TYPE_EXPENSE;
                            $newDay = $previousDate !== $entry->entry_date->toDateString();
                            $previousDate = $entry->entry_date->toDateString();
                        @endphp
                        <tr>
                            <td class="center">{{ $newDay ? $entry->entry_date->format('F') : '' }}</td>
                            <td class="center">{{ $entry->entry_date->format('j') }}</td>
                            <td class="particulars">{{ $entry->particulars }}</td>
                            <td class="center">{{ $entry->reference_no }}</td>
                            <td class="amount">{{ $isExpense ? '' : $num($entry->amount) }}</td>
                            <td class="amount">{{ $isExpense ? $num($entry->amount) : '' }}</td>
                            <td class="amount">{{ $isExpense ? '' : $num($entry->amount) }}</td>
                            @foreach ($categories as $category)
                                <td class="amount">{{ $isExpense && $entry->category === $category ? $num($entry->amount) : '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach

                    @if ($month->entries->isEmpty())
                        <tr class="empty-row">
                            <td colspan="{{ $columnCount }}" class="center">No collections or expenses recorded this month.</td>
                        </tr>
                    @endif

                    <tr class="total-row">
                        <td colspan="4" class="center">Total for {{ $month->monthStart->format('F') }}</td>
                        <td class="amount">{{ $num($month->cashDebitTotal()) }}</td>
                        <td class="amount">{{ $num($month->totalExpenses) }}</td>
                        <td class="amount">{{ $num($month->cashDebitTotal()) }}</td>
                        @foreach ($categories as $category)
                            <td class="amount">{{ $month->categoryTotal($category) !== null ? $num($month->categoryTotal($category)) : '' }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <p class="records-subtitle">This term has no date range set, so its months cannot be listed.</p>
    @endforelse

    {{-- Semester grand total across every month above --}}
    @if ($report->months->isNotEmpty())
        <div class="month-block">
            <h2 class="month-heading">Semester Total</h2>
            <table class="records-table grand-total-table">
                <thead>
                    <tr>
                        <th class="head">Month</th>
                        <th class="head">Balance Brought Forward</th>
                        <th class="head">Collections</th>
                        <th class="head">Expenses</th>
                        <th class="head">Ending Balance</th>
                        @foreach ($categories as $category)
                            <th style="background: {{ $categoryColors[$category] ?? '#e5e7eb' }};">{{ $category }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report->months as $month)
                        <tr>
                            <td class="center">{{ $month->label() }}</td>
                            <td class="amount">{{ $num($month->beginningBalance) }}</td>
                            <td class="amount">{{ $num($month->totalCollections) }}</td>
                            <td class="amount">{{ $num($month->totalExpenses) }}</td>
                            <td class="amount">{{ $num($month->endingBalance()) }}</td>
                            @foreach ($categories as $category)
                                <td class="amount">{{ $month->categoryTotal($category) !== null ? $num($month->categoryTotal($category)) : '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td class="center">Whole Semester</td>
                        <td class="amount">{{ $num($report->openingBalance) }}</td>
                        <td class="amount">{{ $num($report->cashCollections) }}</td>
                        <td class="amount">{{ $num($report->cashExpenses) }}</td>
                        <td class="amount">{{ $num($report->closingBalance()) }}</td>
                        @foreach ($categories as $category)
                            <td class="amount">{{ $num($report->cashCategoryTotals[$category]) }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
@endsection
