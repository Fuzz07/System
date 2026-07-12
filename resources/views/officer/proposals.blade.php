@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-officer') @endsection

@section('content')
<div class="page-header"><div><h1>My Proposals</h1><p>Submit and track your project proposals</p></div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#proposalModal"><i class="bi bi-plus-circle"></i> New Proposal</button>
</div>

<div class="card"><div class="table-responsive-custom"><table class="table-custom">
    <thead><tr><th>#</th><th>Project Title</th><th>Requested</th><th>Approved</th><th>Status</th><th>Project State</th><th>Action</th></tr></thead>
    <tbody>
    @forelse($proposals as $i => $p)
    <tr>
        <td style="color:#a0aec0;font-size:.8rem;">{{ $i+1 }}</td>
        <td><div style="font-weight:700;color:var(--navy-900);">{{ $p->project_title }}</div><div style="font-size:.75rem;color:#718096;">{{ Str::limit($p->description, 80) }}...</div></td>
        <td>{!! \App\Helpers\SscHelper::formatCurrency($p->requested_budget) !!}</td>
        <td>{{ $p->approved_budget ? \App\Helpers\SscHelper::formatCurrency($p->approved_budget) : '—' }}</td>
        <td>{!! \App\Helpers\SscHelper::statusBadge($p->status) !!}</td>
        <td>
            @if($p->status === 'Approved')
            <span class="badge {{ $p->project_status==='Completed' ? 'bg-success' : 'bg-info' }} bg-opacity-10 {{ $p->project_status==='Completed' ? 'text-success' : 'text-info' }} border">{{ $p->project_status }}</span>
            @else <span class="text-muted small">N/A</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('proposals.print', $p) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i> Print</a>
                @if($p->status === 'Approved' && $p->project_status === 'Ongoing')
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#completeModal{{ $p->id }}"><i class="bi bi-check2-circle"></i> Complete</button>
                @elseif($p->project_status === 'Completed')
                <span class="text-success small fw-bold px-2 py-1"><i class="bi bi-patch-check"></i> Liquidated</span>
                @endif
                <span class="badge bg-secondary" style="font-size:.72rem;"><i class="bi bi-chat-text"></i> {{ $p->comments_count }}</span>
                @if($p->status === 'Pending')
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $p->id }}"><i class="bi bi-pencil-square"></i> Edit</button>
                @endif
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-file-earmark-plus" style="font-size:2rem;opacity:.2;"></i><div class="mt-2">No proposals submitted yet.</div></td></tr>
    @endforelse
    </tbody>
</table></div></div>

{{-- Complete Modals --}}
@foreach($proposals->where('status', 'Approved')->where('project_status', 'Ongoing') as $p)
<div class="modal fade" id="completeModal{{ $p->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title">Project Completion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.proposals.complete', $p) }}" enctype="multipart/form-data">@csrf
        <div class="modal-body p-4">
            <div class="text-center mb-4"><div class="stat-icon success mx-auto mb-3"><i class="bi bi-clipboard-check"></i></div><h6 class="fw-bold">{{ $p->project_title }}</h6><p class="text-muted small">Upload receipts for liquidation.</p></div>
            <div class="mb-3"><label class="form-label-custom">Upload Official Receipt</label><input type="file" name="receipt" class="form-control-custom" accept=".jpg,.jpeg,.png,.pdf,.mp4" required><div style="font-size:.72rem;color:#a0aec0;margin-top:4px;">JPG, JPEG, PNG, PDF, MP4 — Max 5MB</div></div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-teal"><i class="bi bi-cloud-upload"></i> Complete & Liquidate</button></div>
    </form>
</div></div></div>
@endforeach

{{-- Edit Modals --}}
@foreach($proposals->where('status', 'Pending') as $p)
<div class="modal fade" id="editModal{{ $p->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Proposal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.proposals.update', $p) }}">@csrf @method('PUT')
        <div class="modal-body p-4">
            <div class="mb-3"><label class="form-label-custom">Project Title</label><input type="text" name="project_title" class="form-control-custom" value="{{ $p->project_title }}" required></div>
            <div class="mb-3"><label class="form-label-custom">Requested Budget (₱)</label><input type="number" name="requested_budget" class="form-control-custom" value="{{ $p->requested_budget }}" min="1" step="0.01" required></div>
            <div class="mb-3"><label class="form-label-custom">Description</label><textarea name="description" class="form-control-custom" rows="5" required style="resize:vertical;">{{ $p->description }}</textarea></div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-save"></i> Save Changes</button></div>
    </form>
</div></div></div>
@endforeach

{{-- New Proposal Modal --}}
<div class="modal fade" id="proposalModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-file-earmark-text"></i> New Project Proposal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.proposals.store') }}">@csrf
        <div class="modal-body p-4">
            <div class="mb-3"><label class="form-label-custom">Project Title <span class="text-danger">*</span></label><input type="text" name="project_title" class="form-control-custom" placeholder="e.g. Inter-School Sports Fest 2026" required></div>
            <div class="mb-3"><label class="form-label-custom">Requested Budget (₱) <span class="text-danger">*</span></label><input type="number" name="requested_budget" class="form-control-custom" placeholder="0.00" min="1" step="0.01" required></div>
            <div class="mb-3"><label class="form-label-custom">Description <span class="text-danger">*</span></label><textarea name="description" class="form-control-custom" rows="5" placeholder="Describe your project..." required style="resize:vertical;"></textarea></div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-send"></i> Submit Proposal</button></div>
    </form>
</div></div></div>
@endsection
