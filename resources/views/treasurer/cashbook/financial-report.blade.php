@extends('treasurer.cashbook.print-layout')

@php
    $num = fn ($amount) => number_format((float) $amount, 2);
@endphp

@section('title', 'Financial Report for the Month Ending ' . $report->end->format('F j, Y'))
@section('page-size', 'letter portrait')

@section('styles')
    .letterhead { align-items: center; display: grid; gap: 20px; grid-template-columns: 100px 1fr 100px; text-align: center; }
    .letterhead img { height: 84px; justify-self: center; object-fit: contain; width: 84px; }
    .letterhead-lines { font-size: .9rem; line-height: 1.3; }
    .letterhead-school { font-weight: 800; }
    .letterhead-rule { border: 0; border-top: 1.5px solid #111827; margin: 8px auto 0; width: 70%; }
    .report-heading { font-weight: 700; line-height: 1.5; margin: 14px 0 34px; text-align: center; }
    .report-heading .council { text-transform: uppercase; }
    .financial-table { border-collapse: collapse; font-size: .95rem; margin: 0 auto; max-width: 680px; width: 100%; }
    .financial-table td { padding: 2px 0; }
    .financial-table td.amount { width: 150px; }
    .financial-table .strong { font-weight: 700; }
    .financial-table .indent { padding-left: 28px; }
    .financial-table .spacer td { height: 18px; }
    .financial-table .total { border-bottom: 1.5px solid #111827; }
    .financial-table .grand-total { border-bottom: 4px double #111827; font-weight: 700; }
@endsection

@section('content')
    <header class="letterhead">
        <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC Logo">
        <div class="letterhead-lines">
            Republic of the Philippines<br>
            Province of Cebu<br>
            Municipality of Madridejos<br>
            <span class="letterhead-school">MADRIDEJOS COMMUNITY COLLEGE</span><br>
            Crossing Bunakan, Madridejos, Cebu 6053
            <hr class="letterhead-rule">
        </div>
        <img src="{{ asset('assets/images/mcc_logo.png') }}" alt="MCC Logo">
    </header>

    <div class="report-heading">
        <div class="council">Supreme Student Council</div>
        <div>MCC-SSC</div>
        <div>Financial Report</div>
        <div>for the Month Ending {{ $report->end->format('F j, Y') }}</div>
    </div>

    <table class="financial-table">
        <tr>
            <td class="strong">Beginning Total Cash Balance</td>
            <td class="amount"></td>
            <td class="amount strong">{{ $num($report->beginningBalance) }}</td>
        </tr>

        @if (count($report->collectionTotals))
            <tr class="spacer"><td colspan="3"></td></tr>
            <tr><td class="strong" colspan="3">Collections</td></tr>
            @foreach ($report->collectionTotals as $particulars => $amount)
                <tr>
                    <td class="indent">{{ $particulars }}</td>
                    <td class="amount">{{ $num($amount) }}</td>
                    <td class="amount"></td>
                </tr>
            @endforeach
            <tr>
                <td>Add: Total Collections</td>
                <td class="amount"></td>
                <td class="amount total">{{ $num($report->totalCollections) }}</td>
            </tr>
            <tr>
                <td class="strong">Total Cash Available</td>
                <td class="amount"></td>
                <td class="amount strong">{{ $num($report->cashDebitTotal()) }}</td>
            </tr>
        @endif

        <tr class="spacer"><td colspan="3"></td></tr>
        <tr><td class="strong" colspan="3">Expenses</td></tr>
        @forelse ($report->categoryTotals as $category => $amount)
            <tr>
                <td class="indent">{{ $category }}</td>
                <td class="amount">{{ $num($amount) }}</td>
                <td class="amount"></td>
            </tr>
        @empty
            <tr><td class="indent" colspan="3">No expenses this month</td></tr>
        @endforelse
        <tr>
            <td>Less: Total Expenses</td>
            <td class="amount"></td>
            <td class="amount total">{{ $num($report->totalExpenses) }}</td>
        </tr>
        <tr>
            <td class="strong">Total Cash Balance</td>
            <td class="amount"></td>
            <td class="amount strong">{{ $num($report->endingBalance()) }}</td>
        </tr>

        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr>
            <td class="strong">Ending Total Cash Balance</td>
            <td class="amount"></td>
            <td class="amount grand-total">{{ $num($report->endingBalance()) }}</td>
        </tr>
    </table>
@endsection
