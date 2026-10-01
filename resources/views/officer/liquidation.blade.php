@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-officer') @endsection

@section('content')
<div class="page-header"><div><h1>Liquidation Reports</h1><p>Upload post-event financial liquidation documents</p></div>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#liqModal"><i class="bi bi-cloud-upload"></i> Upload Report</button>
</div>

@if($awaitingRelease->isNotEmpty())
<div class="alert alert-info d-flex gap-3 align-items-start" style="border-radius:var(--radius);">
    <i class="bi bi-hourglass-split fs-5"></i>
    <div>
        <div class="fw-bold mb-1">Waiting for the treasurer's budget release</div>
        <div class="small mb-2">You can upload a liquidation report for these approved projects once their full budget has been released:</div>
        <ul class="small mb-0 ps-3">
            @foreach($awaitingRelease as $p)
            <li><strong>{{ $p->project_title }}</strong> — {!! \App\Helpers\SscHelper::formatCurrency($p->releasedAmount()) !!} of {!! \App\Helpers\SscHelper::formatCurrency((float) $p->approved_budget) !!} released</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="card"><div class="table-responsive-custom"><table class="table-custom">
    <thead><tr><th>#</th><th>Report Title</th><th>Linked Project</th><th>Notes</th><th>Status</th><th>Date</th><th>File</th></tr></thead>
    <tbody>
    @forelse($liquidations as $i => $liq)
    <tr>
        <td style="color:#a0aec0;font-size:.8rem;">{{ $i+1 }}</td>
        <td style="font-weight:700;">{{ $liq->title }}</td>
        <td>{{ $liq->proposal->project_title ?? '—' }}</td>
        <td style="font-size:.8rem;color:#718096;">{{ Str::limit($liq->notes, 80) }}</td>
        <td>{!! \App\Helpers\SscHelper::statusBadge($liq->status) !!}</td>
        <td style="font-size:.78rem;white-space:nowrap;">{{ $liq->created_at?->format('M d, Y') }}</td>
        <td><a href="{{ \App\Helpers\SscHelper::getUploadUrl($liq->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm" style="font-size:.72rem;"><i class="bi bi-download"></i> View</a></td>
    </tr>
    @empty
    <tr><td colspan="7" class="text-center py-5 text-muted">No liquidation reports yet.</td></tr>
    @endforelse
    </tbody>
</table></div></div>

<div class="modal fade" id="liqModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;"><i class="bi bi-folder-check"></i> Upload Liquidation Report</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('officer.liquidation.store') }}" enctype="multipart/form-data">@csrf
        <div class="modal-body p-4">
            <div class="mb-3"><label class="form-label-custom">Report Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control-custom" required></div>
            <div class="mb-3"><label class="form-label-custom">Linked Project <span class="text-danger">*</span></label><select name="proposal_id" class="form-select-custom" required><option value="">{{ $proposals->isEmpty() ? 'No project has had its budget released yet' : 'Select a project with its budget released...' }}</option>@foreach($proposals as $p)<option value="{{ $p->id }}">{{ $p->project_title }}</option>@endforeach</select><div style="font-size:.72rem;color:#a0aec0;margin-top:4px;">Only projects whose full budget the treasurer has released are listed.</div></div>
            <div class="mb-3"><label class="form-label-custom">Upload File <span class="text-danger">*</span></label><input type="file" name="liq_file" class="form-control-custom" accept="{{ \App\Support\UploadValidation::ACCEPT }}" style="padding:8px 14px;" required><div style="font-size:.72rem;color:#a0aec0;margin-top:4px;">PNG, JPG or JPEG only — Max 5MB</div></div>
            <div class="mb-3"><label class="form-label-custom">Notes</label><textarea name="notes" class="form-control-custom" rows="3" style="resize:vertical;"></textarea></div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-custom"><i class="bi bi-cloud-upload"></i> Upload</button></div>
    </form>
</div></div></div>
@endsection
