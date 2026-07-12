@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header"><div><h1>Proposals</h1><p>Review and manage project proposals</p></div></div>

<div class="card mb-4"><div class="card-body-custom">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5"><input type="text" name="search" class="form-control-custom" placeholder="Search proposals..." value="{{ $search }}"></div>
        <div class="col-md-3"><select name="status" class="form-select-custom"><option value="">All Statuses</option>@foreach(['Pending','Approved','Rejected'] as $s)<option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
        <div class="col-md-4 d-flex gap-2"><button type="submit" class="btn-primary-custom flex-fill justify-content-center"><i class="bi bi-search"></i></button>@if($search || $status)<a href="{{ route('admin.proposals') }}" class="btn btn-outline-secondary">Reset</a>@endif</div>
    </form>
</div></div>

<div class="card"><div class="table-responsive-custom"><table class="table-custom">
    <thead><tr><th>#</th><th>Project</th><th>Officer</th><th>Requested</th><th>Approved</th><th>Status</th><th>Comments</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($proposals as $i => $p)
    <tr>
        <td style="color:#a0aec0;font-size:.8rem;">{{ $i+1 }}</td>
        <td><div style="font-weight:700;color:var(--navy-900);">{{ $p->project_title }}</div><div style="font-size:.75rem;color:#718096;">{{ Str::limit($p->description, 80) }}</div></td>
        <td style="font-size:.82rem;">{{ $p->officer->fullname ?? 'N/A' }}</td>
        <td>{!! \App\Helpers\SscHelper::formatCurrency($p->requested_budget) !!}</td>
        <td>{{ $p->approved_budget ? \App\Helpers\SscHelper::formatCurrency($p->approved_budget) : '—' }}</td>
        <td>{!! \App\Helpers\SscHelper::statusBadge($p->status) !!}</td>
        <td><span class="badge bg-secondary" style="font-size:.72rem;">{{ $p->comments_count }}</span></td>
        <td>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('proposals.print', $p) }}" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;"><i class="bi bi-printer"></i> Print</a>
                @if($p->status === 'Pending')
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $p->id }}" style="font-size:.72rem;"><i class="bi bi-pencil-square"></i> Review</button>
                @else
                <span class="text-muted small">{{ $p->status }}</span>
                @endif
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center py-4 text-muted">No proposals found.</td></tr>
    @endforelse
    </tbody>
</table></div></div>

{{-- Review Modals --}}
@foreach($proposals->where('status', 'Pending') as $p)
<div class="modal fade" id="reviewModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:var(--radius);border:none;">
        <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;">Review: {{ $p->project_title }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('admin.proposals.review', $p) }}">
            @csrf
            <div class="modal-body p-4">
                <p style="font-size:.85rem;color:#718096;margin-bottom:16px;">{{ $p->description }}</p>
                <div class="mb-3"><label class="form-label-custom">Approved Budget (₱)</label><input type="number" name="approved_budget" class="form-control-custom" value="{{ $p->requested_budget }}" step="0.01"></div>
                <div class="mb-3"><label class="form-label-custom">Admin Notes</label><textarea name="admin_notes" class="form-control-custom" rows="3" style="resize:vertical;"></textarea></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="submit" name="action" value="reject" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Reject</button>
                <button type="submit" name="action" value="approve" class="btn-primary-custom"><i class="bi bi-check2-circle"></i> Approve</button>
            </div>
        </form>
    </div></div>
</div>
@endforeach
@endsection
