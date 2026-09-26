@extends('layouts.app')

@section('sidebar-nav') @include('partials.sidebar-treasurer') @endsection

@php
    $fmt = fn ($amount) => \App\Helpers\SscHelper::formatCurrency($amount);
    $prevMonth = $report->start->copy()->subMonth()->format('Y-m');
    $nextMonth = $report->start->copy()->addMonth()->format('Y-m');
    $defaultDate = now()->format('Y-m') === $month ? now()->toDateString() : $report->start->toDateString();
    $fromCreate = old('entry_form') === 'create';
    $newValues = [
        'entry_date'   => $fromCreate ? old('entry_date') : $defaultDate,
        'type'         => $fromCreate ? old('type') : \App\Models\CashBookEntry::TYPE_EXPENSE,
        'particulars'  => $fromCreate ? old('particulars') : '',
        'reference_no' => $fromCreate ? old('reference_no') : '',
        'category'     => $fromCreate ? old('category') : '',
        'amount'       => $fromCreate ? old('amount') : '',
    ];
@endphp

@section('content')
<div class="page-header">
    <div>
        <h1><i class="bi bi-journal-bookmark me-2" style="color:#d97706;"></i>Cash Book</h1>
        <p>Record collections and expenses, then print the monthly Records of Expenses and Financial Report</p>
    </div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#entryModal"><i class="bi bi-plus-circle"></i> New Entry</button>
</div>

<div class="card mb-4"><div class="card-body-custom d-flex flex-wrap gap-2 align-items-center justify-content-between">
    <form method="GET" action="{{ route('treasurer.cashbook') }}" class="d-flex gap-2 align-items-center">
        <a href="{{ route('treasurer.cashbook', ['month' => $prevMonth]) }}" class="btn btn-outline-secondary btn-sm" title="Previous month" aria-label="Previous month"><i class="bi bi-chevron-left"></i></a>
        <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="width:170px;" onchange="this.form.submit()" aria-label="Month">
        <a href="{{ route('treasurer.cashbook', ['month' => $nextMonth]) }}" class="btn btn-outline-secondary btn-sm" title="Next month" aria-label="Next month"><i class="bi bi-chevron-right"></i></a>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('treasurer.cashbook.records', $month) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> Records of Expenses</a>
        <a href="{{ route('treasurer.cashbook.financial', $month) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> Financial Report</a>
    </div>
</div></div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3"><div class="stat-card">
        <div class="stat-icon" style="background:rgba(13,43,92,.1);color:#0d2b5c;"><i class="bi bi-box-arrow-in-right"></i></div>
        <div class="stat-info"><div class="label">Beginning Balance</div><div class="value" style="font-size:1.3rem;">{{ $fmt($report->beginningBalance) }}</div><div class="sub" style="font-size:.65rem;color:#64748b;">Carried from {{ $report->previousMonthName() }}</div></div>
    </div></div>
    <div class="col-md-6 col-xl-3"><div class="stat-card">
        <div class="stat-icon" style="background:rgba(5,150,105,.1);color:#059669;"><i class="bi bi-arrow-down-circle"></i></div>
        <div class="stat-info"><div class="label">Collections</div><div class="value" style="font-size:1.3rem;">{{ $fmt($report->totalCollections) }}</div><div class="sub" style="font-size:.65rem;color:#64748b;">{{ $report->start->format('F Y') }}</div></div>
    </div></div>
    <div class="col-md-6 col-xl-3"><div class="stat-card">
        <div class="stat-icon" style="background:rgba(220,38,38,.1);color:#dc2626;"><i class="bi bi-arrow-up-circle"></i></div>
        <div class="stat-info"><div class="label">Expenses</div><div class="value" style="font-size:1.3rem;">{{ $fmt($report->totalExpenses) }}</div><div class="sub" style="font-size:.65rem;color:#64748b;">{{ $report->start->format('F Y') }}</div></div>
    </div></div>
    <div class="col-md-6 col-xl-3"><div class="stat-card">
        <div class="stat-icon" style="background:rgba(217,119,6,.1);color:#d97706;"><i class="bi bi-safe2"></i></div>
        <div class="stat-info"><div class="label">Ending Balance</div><div class="value" style="font-size:1.3rem;">{{ $fmt($report->endingBalance()) }}</div><div class="sub" style="font-size:.65rem;color:#64748b;">As of {{ $report->end->format('M j, Y') }}</div></div>
    </div></div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span class="card-title">Entries for {{ $report->start->format('F Y') }}</span>
                <span class="badge bg-secondary">{{ $entries->count() }} records</span>
            </div>
            <div class="table-responsive-custom"><table class="table-custom">
                <thead><tr><th>Date</th><th style="min-width:220px;">Particulars</th><th>OR/AR No.</th><th>Category</th><th style="text-align:right;">Amount</th><th style="text-align:center;">Actions</th></tr></thead>
                <tbody>
                @forelse($entries as $entry)
                <tr>
                    <td style="white-space:nowrap;font-size:.82rem;">{{ $entry->entry_date->format('M j') }}</td>
                    <td>
                        <div style="font-weight:600;color:var(--navy-900);">{{ $entry->particulars }}</div>
                        <div style="font-size:.72rem;color:#718096;">{{ \App\Models\CashBookEntry::TYPES[$entry->type] ?? $entry->type }}</div>
                    </td>
                    <td style="font-size:.82rem;">{{ $entry->reference_no ?: '—' }}</td>
                    <td style="font-size:.82rem;">{{ $entry->category ?: '—' }}</td>
                    <td style="text-align:right;font-weight:700;white-space:nowrap;color:{{ $entry->isExpense() ? '#dc2626' : '#059669' }};">
                        {{ $entry->isExpense() ? '−' : '+' }}{{ $fmt($entry->amount) }}
                    </td>
                    <td style="text-align:center;white-space:nowrap;">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editEntry{{ $entry->id }}" title="Edit" aria-label="Edit entry"><i class="bi bi-pencil-square"></i></button>
                        <form method="POST" action="{{ route('treasurer.cashbook.destroy', $entry) }}" class="d-inline" onsubmit="return confirm('Delete this cash book entry?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete entry"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-journal" style="font-size:2rem;opacity:.2;"></i><div class="mt-2">No entries recorded for {{ $report->start->format('F Y') }}.</div></td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header-custom"><span class="card-title">Expenses by Category</span></div>
            <div style="padding:16px;">
                @forelse($report->categoryTotals as $category => $amount)
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f1f5f9;font-size:.85rem;">
                    <span>{{ $category }}</span><strong>{{ $fmt($amount) }}</strong>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">No expenses this month.</div>
                @endforelse
                @if(count($report->categoryTotals))
                <div class="d-flex justify-content-between pt-3" style="font-size:.9rem;"><strong>Total Expenses</strong><strong>{{ $fmt($report->totalExpenses) }}</strong></div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- New Entry Modal --}}
<div class="modal fade" id="entryModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-journal-plus"></i> New Cash Book Entry</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('treasurer.cashbook.store') }}">@csrf
        <input type="hidden" name="entry_form" value="create">
        <div class="modal-body p-4">@include('treasurer.cashbook.entry-fields', ['values' => $newValues])</div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-save"></i> Save Entry</button></div>
    </form>
</div></div></div>

{{-- Edit Entry Modals --}}
@foreach($entries as $entry)
<div class="modal fade" id="editEntry{{ $entry->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-pencil-square"></i> Edit Entry</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('treasurer.cashbook.update', $entry) }}">@csrf @method('PUT')
        <div class="modal-body p-4">@include('treasurer.cashbook.entry-fields', ['values' => [
            'entry_date'   => $entry->entry_date->toDateString(),
            'type'         => $entry->type,
            'particulars'  => $entry->particulars,
            'reference_no' => $entry->reference_no,
            'category'     => $entry->category,
            'amount'       => $entry->amount,
        ]])</div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-save"></i> Save Changes</button></div>
    </form>
</div></div></div>
@endforeach
@endsection

@push('scripts')
<script>
(function () {
    // Only expenses have a category; the beginning-balance hint only applies to that type.
    function syncFields(fields) {
        const type = fields.querySelector('[data-cashbook-type]').value;
        const category = fields.querySelector('[data-cashbook-category]');
        category.hidden = type !== 'expense';
        category.querySelector('select').required = type === 'expense';
        fields.querySelector('[data-cashbook-opening-hint]').hidden = type !== 'opening';
    }

    document.querySelectorAll('[data-cashbook-fields]').forEach((fields) => {
        syncFields(fields);
        fields.querySelector('[data-cashbook-type]').addEventListener('change', () => syncFields(fields));
    });

    @if($errors->any() && old('entry_form') === 'create')
    bootstrap.Modal.getOrCreateInstance(document.getElementById('entryModal')).show();
    @endif
})();
</script>
@endpush
