@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header"><div><h1>Expenses</h1><p>Review and approve officer expense reports</p></div></div>

<div class="card mb-4"><div class="card-body-custom">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5"><input type="text" name="search" class="form-control-custom" placeholder="Search expenses..." value="{{ $search }}"></div>
        <div class="col-md-3"><select name="status" class="form-select-custom"><option value="">All Statuses</option>@foreach(['Pending','Approved','Rejected'] as $s)<option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
        <div class="col-md-4 d-flex gap-2"><button type="submit" class="btn-primary-custom flex-fill justify-content-center"><i class="bi bi-search"></i></button>@if($search || $status)<a href="{{ route('admin.expenses') }}" class="btn btn-outline-secondary">Reset</a>@endif</div>
    </form>
</div></div>

<div class="card"><div class="table-responsive-custom"><table class="table-custom">
    <thead><tr><th>#</th><th>Expense</th><th>Officer</th><th>Budget Fund</th><th>Amount</th><th>Receipt</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($expenses as $i => $ex)
    <tr>
        <td style="color:#a0aec0;font-size:.8rem;">{{ $i+1 }}</td>
        <td><div style="font-weight:700;">{{ $ex->expense_title }}</div><div style="font-size:.75rem;color:#718096;">{{ Str::limit($ex->description, 60) }}</div></td>
        <td style="font-size:.82rem;">{{ $ex->officer->fullname ?? 'N/A' }}</td>
        <td><span class="badge bg-primary" style="font-size:.7rem;">{{ $ex->budget->title ?? 'N/A' }}</span></td>
        <td style="font-weight:700;color:var(--danger);">{!! \App\Helpers\SscHelper::formatCurrency($ex->amount) !!}</td>
        <td>@if($ex->receipt)<a href="{{ asset('storage/' . $ex->receipt) }}" target="_blank" class="btn btn-outline-primary btn-sm" style="font-size:.72rem;"><i class="bi bi-file-earmark"></i> View</a>@else<span class="text-muted">—</span>@endif</td>
        <td>{!! \App\Helpers\SscHelper::statusBadge($ex->status) !!}</td>
        <td>
            @if($ex->status === 'Pending')
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewExpense{{ $ex->id }}" style="font-size:.72rem;"><i class="bi bi-pencil-square"></i></button>
            @endif
        </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center py-4 text-muted">No expenses found.</td></tr>
    @endforelse
    </tbody>
</table></div></div>

@foreach($expenses->where('status', 'Pending') as $ex)
<div class="modal fade" id="reviewExpense{{ $ex->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:var(--radius);border:none;">
        <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;">{{ $ex->expense_title }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('admin.expenses.review', $ex) }}">@csrf
            <div class="modal-body p-4">
                <p class="text-muted small">Amount: <strong style="color:var(--danger);">{!! \App\Helpers\SscHelper::formatCurrency($ex->amount) !!}</strong></p>
                <div class="mb-3"><label class="form-label-custom">Admin Notes</label><textarea name="admin_notes" class="form-control-custom" rows="3" style="resize:vertical;"></textarea></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="submit" name="action" value="reject" class="btn btn-outline-danger"><i class="bi bi-x"></i> Reject</button>
                <button type="submit" name="action" value="approve" class="btn-primary-custom"><i class="bi bi-check2"></i> Approve</button>
            </div>
        </form>
    </div></div>
</div>
@endforeach
@endsection
