{{-- Printable contribution-fee breakdown. --}}
@extends('treasurer.cashbook.print-layout')

@php
    $money = fn ($amount) => number_format((float) $amount, 2);
@endphp

@section('title', 'Contribution Fee Report' . ($term ? ' - ' . $term->academic_term : ''))
@section('page-size', 'letter landscape')

@section('styles')
    .letterhead { align-items: center; display: grid; gap: 20px; grid-template-columns: 80px 1fr 80px; text-align: center; }
    .letterhead img { height: 68px; justify-self: center; object-fit: contain; width: 68px; }
    .letterhead-lines { font-size: .85rem; line-height: 1.3; }
    .letterhead-school { font-weight: 800; }
    .letterhead-rule { border: 0; border-top: 1.5px solid #111827; margin: 8px auto 0; width: 70%; }
    .report-heading { font-weight: 700; line-height: 1.5; margin: 14px 0 22px; text-align: center; }
    .filters { font-size: .85rem; margin: 0 auto 18px; text-align: center; }
    .summary { border-collapse: collapse; margin: 0 auto 22px; max-width: 720px; width: 100%; }
    .summary th, .summary td, .details th, .details td { border: 1px solid #cbd5e1; padding: 6px 8px; }
    .summary th, .details th { background: #f1f5f9; font-weight: 700; }
    .summary .amount, .details .amount { text-align: right; white-space: nowrap; }
    .details { border-collapse: collapse; font-size: .78rem; width: 100%; }
    .details-title { font-size: 1rem; font-weight: 700; margin: 22px 0 8px; }
    .total-row { font-weight: 700; }
    @media print { .summary th, .details th { background: #f1f5f9 !important; } }
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
        <div>Supreme Student Council</div>
        <div>Contribution Fee Collection Report</div>
        <div>{{ $term?->academic_term ?? 'All Terms' }}</div>
    </div>

    <div class="filters">
        Department: <strong>{{ $department ?: 'All Departments' }}</strong>
        &nbsp;&bull;&nbsp;
        Year Level: <strong>{{ $yearLevel ?: 'All Year Levels' }}</strong>
    </div>

    <table class="summary">
        <thead>
            <tr>
                <th>Department</th>
                <th>Year Level</th>
                <th>Students Paid</th>
                <th>Payment Records</th>
                <th class="amount">Collected</th>
            </tr>
        </thead>
        <tbody>
            @forelse($breakdown as $row)
                <tr>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['year_level'] }}</td>
                    <td>{{ $row['students'] }}</td>
                    <td>{{ $row['payments'] }}</td>
                    <td class="amount">{{ $money($row['collected']) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">No paid contributions match these filters.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2">Total</td>
                <td>{{ $payments->pluck('user_id')->unique()->count() }}</td>
                <td>{{ $payments->count() }}</td>
                <td class="amount">{{ $money($totalCollected) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="details-title">Paid Contribution Details</div>
    <table class="details">
        <thead>
            <tr>
                <th>Student</th>
                <th>Student ID</th>
                <th>Department</th>
                <th>Year Level</th>
                <th>Payment Date</th>
                <th>Reference</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->user->fullname }}</td>
                    <td>{{ $payment->user->student_id }}</td>
                    <td>{{ $payment->user->department ?: '—' }}</td>
                    <td>{{ $payment->user->year_level ?: '—' }}</td>
                    <td>{{ ($payment->paid_at ?? $payment->created_at)?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $payment->reference ?: '—' }}</td>
                    <td class="amount">{{ $money($payment->amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;">No paid contribution details to display.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
