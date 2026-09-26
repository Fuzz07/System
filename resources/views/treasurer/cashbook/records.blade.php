@extends('treasurer.cashbook.print-layout')

@php
    $num = fn ($amount) => number_format((float) $amount, 2);
    $categories = array_keys($report->categoryTotals);
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
    $title = 'Records of Expenses for the Month of ' . $report->start->format('F Y');
@endphp

@section('title', $title)
@section('page-size', 'legal landscape')

@section('styles')
    :root { --sheet-width: 1200px; }
    .records-title { font-size: 1.1rem; font-weight: 900; margin: 0 0 12px; text-align: center; text-transform: uppercase; }
    .records-table { border-collapse: collapse; font-size: .74rem; width: 100%; }
    .records-table th, .records-table td { border: 1px solid #111827; padding: 3px 6px; }
    .records-table th { font-weight: 800; text-align: center; }
    .records-table .head { background: #facc15; text-transform: uppercase; }
    .records-table .dr { background: #f59e0b; }
    .records-table .cr { background: #4d9f3a; color: #fff; }
    .records-table .particulars { min-width: 230px; text-align: center; }
    .records-table .center { text-align: center; }
    .records-table .total-row td { background: #fde68a; font-weight: 800; }
@endsection

@section('content')
    <h1 class="records-title">{{ $title }}</h1>
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
                <th class="head" colspan="2">{{ $report->start->format('Y') }}</th>
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
                <td class="particulars">Total Cash Balance from {{ $report->previousMonthName() }}</td>
                <td></td>
                <td class="amount">{{ $num($report->beginningBalance) }}</td>
                <td></td>
                <td class="amount">{{ $num($report->beginningBalance) }}</td>
                @foreach ($categories as $category)
                    <td></td>
                @endforeach
            </tr>
            @php $previousDate = null; @endphp
            @foreach ($report->entries as $entry)
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
            @if ($report->entries->isEmpty())
                <tr><td colspan="{{ 7 + count($categories) }}" class="center" style="padding:10px;color:#6b7280;">No collections or expenses recorded this month.</td></tr>
            @endif
            <tr class="total-row">
                <td colspan="4" class="center">Total</td>
                <td class="amount">{{ $num($report->cashDebitTotal()) }}</td>
                <td class="amount">{{ $num($report->totalExpenses) }}</td>
                <td class="amount">{{ $num($report->cashDebitTotal()) }}</td>
                @foreach ($categories as $category)
                    <td class="amount">{{ $num($report->categoryTotals[$category]) }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
@endsection
