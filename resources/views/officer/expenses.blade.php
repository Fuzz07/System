@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-officer') @endsection

@section('content')
<div class="page-header"><div><h1>My Expenses</h1><p>Submit and track expense reports with receipts</p></div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="bi bi-plus-circle"></i> File Expense</button>
</div>

<div class="card"><div class="table-responsive-custom"><table class="table-custom">
    <thead><tr><th>#</th><th>Expense Title</th><th>Budget Fund</th><th>Amount</th><th>Receipt</th><th>Status</th><th>Notes</th><th>Date</th></tr></thead>
    <tbody>
    @forelse($expenses as $i => $ex)
    <tr>
        <td style="color:#a0aec0;font-size:.8rem;">{{ $i+1 }}</td>
        <td><div style="font-weight:700;">{{ $ex->expense_title }}</div><div style="font-size:.75rem;color:#718096;">{{ Str::limit($ex->description, 60) }}</div></td>
        <td><span class="badge bg-primary" style="font-size:.7rem;">{{ $ex->budget->title ?? 'N/A' }}</span></td>
        <td style="font-weight:700;color:var(--danger);">{!! \App\Helpers\SscHelper::formatCurrency($ex->amount) !!}</td>
        <td>@if($ex->receipt)<a href="{{ asset('storage/' . $ex->receipt) }}" target="_blank" class="btn btn-outline-primary btn-sm" style="font-size:.72rem;"><i class="bi bi-file-earmark"></i> View</a>@else —@endif</td>
        <td>{!! \App\Helpers\SscHelper::statusBadge($ex->status) !!}</td>
        <td style="font-size:.78rem;color:#718096;max-width:140px;">{{ $ex->admin_notes ?? '—' }}</td>
        <td style="font-size:.78rem;white-space:nowrap;">{{ $ex->created_at?->format('M d, Y') }}</td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center py-5 text-muted">No expenses filed yet.</td></tr>
    @endforelse
    </tbody>
</table></div></div>

<div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-receipt"></i> File New Expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.expenses.store') }}" enctype="multipart/form-data">@csrf
        <div class="modal-body p-4"><div class="row g-3">
            <div class="col-md-8"><label class="form-label-custom">Expense Title <span class="text-danger">*</span></label><input type="text" name="expense_title" class="form-control-custom" placeholder="e.g. Sound System Rental" required></div>
            <div class="col-md-4"><label class="form-label-custom">Amount (₱) <span class="text-danger">*</span></label><input type="number" name="amount" class="form-control-custom" placeholder="0.00" min="1" max="100000" step="0.01" required></div>
            <div class="col-12"><label class="form-label-custom">Budget Fund <span class="text-danger">*</span></label><select name="budget_id" class="form-select-custom" required><option value="">Select approved budget fund...</option>@foreach($budgets as $b)<option value="{{ $b->id }}">{{ $b->title }} — Remaining: {!! \App\Helpers\SscHelper::formatCurrency($b->remaining_balance) !!}</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label-custom">Description</label><textarea name="description" class="form-control-custom" rows="3" style="resize:vertical;"></textarea></div>
            <div class="col-12"><label class="form-label-custom">Receipt</label><input type="file" name="receipt" class="form-control-custom" accept=".jpg,.jpeg,.png,.pdf,.mp4" style="padding:8px 14px;"><div style="font-size:.72rem;color:#a0aec0;margin-top:4px;">JPG, JPEG, PNG, PDF, MP4 — Max 5MB</div></div>
        </div></div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-send"></i> Submit Expense</button></div>
    </form>
</div></div></div>
@endsection
