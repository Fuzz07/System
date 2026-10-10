{{-- Printable semester transparency report. Expects $report (App\Services\SemesterReport). --}}
@extends('treasurer.cashbook.print-layout')

@php
    $num = fn ($amount) => number_format((float) $amount, 2);
@endphp

@section('title', 'Semester Report for ' . $report->term->academic_term)
@section('page-size', 'letter portrait')

@section('styles')
    .letterhead { align-items: center; display: grid; gap: 20px; grid-template-columns: 100px 1fr 100px; text-align: center; }
    .letterhead img { height: 84px; justify-self: center; object-fit: contain; width: 84px; }
    .letterhead-lines { font-size: .9rem; line-height: 1.3; }
    .letterhead-school { font-weight: 800; }
    .letterhead-rule { border: 0; border-top: 1.5px solid #111827; margin: 8px auto 0; width: 70%; }
    .report-heading { font-weight: 700; line-height: 1.5; margin: 14px 0 28px; text-align: center; }
    .report-heading .council { text-transform: uppercase; }
    .report-heading .term-range { font-size: .88rem; font-weight: 400; }
    .financial-table { border-collapse: collapse; font-size: .95rem; margin: 0 auto; max-width: 680px; width: 100%; }
    .financial-table td, .financial-table th { padding: 2px 0; }
    .financial-table td.amount, .financial-table th.amount { width: 130px; }
    .financial-table .strong { font-weight: 700; }
    .financial-table .indent { padding-left: 28px; }
    .financial-table .spacer td { height: 18px; }
    .financial-table .total { border-bottom: 1.5px solid #111827; }
    .financial-table .grand-total { border-bottom: 4px double #111827; font-weight: 700; }
    .section-head { border-bottom: 1px solid #111827; font-weight: 700; padding-bottom: 2px; }
    .muted { color: #4b5563; font-style: italic; }
    .count-note { font-size: .85rem; font-weight: 400; }
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
        <div>Semester Transparency Report</div>
        <div>{{ $report->term->academic_term }}</div>
        <div class="term-range">Covering {{ $report->term->termRangeLabel() }}</div>
    </div>

    <table class="financial-table">
        {{-- Funds received --}}
        <tr><td class="section-head" colspan="3">Funds Received</td></tr>
        <tr>
            <td class="indent">
                Contribution fees collected
                <span class="count-note">({{ number_format($report->contributionPayers) }} student{{ $report->contributionPayers === 1 ? '' : 's' }})</span>
            </td>
            <td class="amount">{{ $num($report->contributionsCollected) }}</td>
            <td class="amount"></td>
        </tr>
        <tr>
            <td class="indent">Cash book collections</td>
            <td class="amount">{{ $num($report->cashCollections) }}</td>
            <td class="amount"></td>
        </tr>
        <tr>
            <td>Total Funds Received</td>
            <td class="amount"></td>
            <td class="amount total">{{ $num($report->contributionsCollected + $report->cashCollections) }}</td>
        </tr>

        {{-- Funds allocated --}}
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr><td class="section-head" colspan="3">Funds Allocated</td></tr>
        @forelse ($report->funds as $fund)
            <tr>
                <td class="indent">{{ $fund->title }}@if($fund->department) &mdash; {{ $fund->department }}@endif</td>
                <td class="amount">{{ $num($fund->allocated_amount) }}</td>
                <td class="amount"></td>
            </tr>
        @empty
            <tr><td class="indent muted" colspan="3">No approved funds for this semester</td></tr>
        @endforelse
        <tr>
            <td>Total Allocated</td>
            <td class="amount"></td>
            <td class="amount total">{{ $num($report->totalAllocated) }}</td>
        </tr>

        {{-- Expenditures --}}
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr><td class="section-head" colspan="3">Expenditures</td></tr>
        @forelse ($report->cashCategoryTotals as $category => $amount)
            <tr>
                <td class="indent">{{ $category }}</td>
                <td class="amount">{{ $num($amount) }}</td>
                <td class="amount"></td>
            </tr>
        @empty
            <tr><td class="indent muted" colspan="3">No cash expenses recorded this semester</td></tr>
        @endforelse
        <tr>
            <td>Total Cash Expenses</td>
            <td class="amount"></td>
            <td class="amount total">{{ $num($report->cashExpenses) }}</td>
        </tr>
        <tr>
            <td>Approved Expenses Charged to Semester Funds</td>
            <td class="amount"></td>
            <td class="amount total">{{ $num($report->totalSpent) }}</td>
        </tr>
        <tr>
            <td class="strong">Unspent Balance of Allocated Funds</td>
            <td class="amount"></td>
            <td class="amount grand-total">{{ $num(max(0, $report->totalRemaining)) }}</td>
        </tr>

        {{-- Projects --}}
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr><td class="section-head" colspan="3">Projects and Accountability</td></tr>
        <tr>
            <td class="indent">Projects proposed</td>
            <td class="amount">{{ number_format($report->projectsProposed) }}</td>
            <td class="amount"></td>
        </tr>
        <tr>
            <td class="indent">Projects approved</td>
            <td class="amount">{{ number_format($report->projectsApproved) }}</td>
            <td class="amount"></td>
        </tr>
        <tr>
            <td class="indent">Projects completed</td>
            <td class="amount">{{ number_format($report->projectsCompleted) }}</td>
            <td class="amount"></td>
        </tr>
        <tr>
            <td class="indent">Approved project budgets</td>
            <td class="amount">{{ $num($report->projectsApprovedBudget) }}</td>
            <td class="amount"></td>
        </tr>
        <tr>
            <td class="indent">
                Released to projects
                <span class="count-note">({{ $report->releaseCount }} disbursement{{ $report->releaseCount === 1 ? '' : 's' }})</span>
            </td>
            <td class="amount">{{ $num($report->totalReleased) }}</td>
            <td class="amount"></td>
        </tr>
        <tr>
            <td class="indent">Liquidation reports filed</td>
            <td class="amount">{{ number_format($report->liquidationsFiled) }}</td>
            <td class="amount"></td>
        </tr>
        <tr>
            <td class="indent">Liquidation reports approved</td>
            <td class="amount">{{ number_format($report->liquidationsApproved) }}</td>
            <td class="amount"></td>
        </tr>
    </table>
@endsection
