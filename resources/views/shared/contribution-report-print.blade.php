{{-- Printable contribution-fee collection sheet. --}}
@extends('treasurer.cashbook.print-layout')

@php
    $money = fn ($amount) => number_format((float) $amount, 2);
@endphp

@section('title', 'SSC Monthly Dues' . ($term ? ' - ' . $term->academic_term : ''))
@section('page-size', 'letter portrait')
@section('hide-signatories', true)

@section('styles')
    .letterhead { align-items: center; display: grid; gap: 20px; grid-template-columns: 80px 1fr 80px; text-align: center; }
    .letterhead img { height: 68px; justify-self: center; object-fit: contain; width: 68px; }
    .letterhead-lines { font-size: .85rem; line-height: 1.3; }
    .letterhead-school { font-weight: 800; }
    .letterhead-rule { border: 0; border-top: 1.5px solid #111827; margin: 8px auto 0; width: 70%; }
    .report-heading { font-weight: 700; line-height: 1.5; margin: 14px 0 16px; text-align: center; }
    .report-date { font-size: .9rem; margin-bottom: 10px; }
    .filters { font-size: .85rem; margin-bottom: 14px; }
    .details { border-collapse: collapse; font-size: .9rem; width: 100%; }
    .details th, .details td { border: 1px solid #374151; padding: 6px 8px; }
    .details th { font-weight: 700; text-align: center; }
    .details .amount { text-align: right; white-space: nowrap; }
    .total-row { font-weight: 700; }
    @media print { .details th { background: #f1f5f9 !important; } }
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
        <div>SUPREME STUDENT COUNCIL</div>
        <div>SSC MONTHLY DUES - {{ $term?->academic_term ?? 'All Terms' }}</div>
    </div>

    <div class="report-date">DATE: {{ now()->format('m/d/y') }}</div>
    <div class="filters">
        Department: <strong>{{ $department ?: 'All Departments' }}</strong>
        &nbsp;&bull;&nbsp;
        Year Level: <strong>{{ $yearLevel ?: 'All Year Levels' }}</strong>
        &nbsp;&bull;&nbsp;
        Section: <strong>{{ $section ?: 'All Sections' }}</strong>
    </div>

    <table class="details">
        <thead>
            <tr>
                <th style="width: 48px;">No.</th>
                <th>Name</th>
                <th>Section &amp; Year</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $loop->iteration }}.</td>
                    <td>{{ $payment->user->fullname }}</td>
                    <td>{{ trim(($payment->user->section ? $payment->user->section . ' - ' : '') . ($payment->user->year_level ?? '')) ?: '—' }}</td>
                    <td class="amount">{{ $money($payment->amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;">No paid contributions match these filters.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3">Total</td>
                <td class="amount">{{ $money($totalCollected) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
