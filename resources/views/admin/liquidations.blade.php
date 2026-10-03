@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header"><div><h1>Liquidation Reports</h1><p>Review reports submitted by officers</p></div></div>

<div class="card mb-4"><div class="card-body-custom">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label-custom">Status</label>
            <select name="status" class="form-select-custom">
                <option value="">All Statuses</option>
                @foreach(['Pending', 'Approved', 'Rejected'] as $option)
                <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn-primary-custom flex-fill justify-content-center"><i class="bi bi-filter"></i> Filter</button>
            @if($status)<a href="{{ route('admin.liquidations') }}" class="btn btn-outline-secondary">Reset</a>@endif
        </div>
    </form>
</div></div>

<div class="card"><div class="table-responsive-custom"><table class="table-custom">
    <thead><tr><th>#</th><th>Report</th><th>Project</th><th>Officer</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($liquidations as $liquidation)
    <tr>
        <td style="color:#a0aec0;font-size:.8rem;">{{ $liquidations->firstItem() + $loop->index }}</td>
        <td style="font-weight:700;">{{ $liquidation->title }}</td>
        <td>{{ $liquidation->proposal->project_title ?? '—' }}</td>
        <td>{{ $liquidation->officer->fullname ?? '—' }}</td>
        <td style="font-size:.78rem;white-space:nowrap;">{{ $liquidation->created_at?->format('M d, Y') }}</td>
        <td>{!! \App\Helpers\SscHelper::statusBadge($liquidation->status) !!}</td>
        <td>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#liquidationView{{ $liquidation->id }}"><i class="bi bi-eye"></i> View</button>
                @if($liquidation->status === 'Pending')
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#liquidationReview{{ $liquidation->id }}"><i class="bi bi-clipboard-check"></i> Review</button>
                @endif
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="text-center py-5 text-muted">No liquidation reports found.</td></tr>
    @endforelse
    </tbody>
</table></div>
{{ $liquidations->links('partials.pagination') }}
</div>

@foreach($liquidations as $liquidation)
<div class="modal fade" id="liquidationView{{ $liquidation->id }}" tabindex="-1" aria-labelledby="liquidationViewLabel{{ $liquidation->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content" style="border-radius:var(--radius);border:none;">
        <div class="modal-header modal-header-custom">
            <h5 class="modal-title" id="liquidationViewLabel{{ $liquidation->id }}" style="font-weight:700;">{{ $liquidation->title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="d-flex flex-wrap gap-4 small mb-3">
                <span><strong>Project:</strong> {{ $liquidation->proposal->project_title ?? '—' }}</span>
                <span><strong>Officer:</strong> {{ $liquidation->officer->fullname ?? '—' }}</span>
                <span><strong>Status:</strong> {{ $liquidation->status }}</span>
            </div>
            @if($liquidation->notes)<p class="small"><strong>Officer notes:</strong> {{ $liquidation->notes }}</p>@endif
            @if($liquidation->review_notes)<p class="small"><strong>Review note:</strong> {{ $liquidation->review_notes }}</p>@endif
            <div class="text-center">
                <img src="{{ \App\Helpers\SscHelper::getUploadUrl($liquidation->file_path) }}" alt="{{ $liquidation->title }}" style="max-width:100%;max-height:70vh;object-fit:contain;">
            </div>
        </div>
    </div></div>
</div>

@if($liquidation->status === 'Pending')
<div class="modal fade" id="liquidationReview{{ $liquidation->id }}" tabindex="-1" aria-labelledby="liquidationReviewLabel{{ $liquidation->id }}" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content" style="border-radius:var(--radius);border:none;">
        <div class="modal-header modal-header-custom">
            <h5 class="modal-title" id="liquidationReviewLabel{{ $liquidation->id }}" style="font-weight:700;">Review: {{ $liquidation->title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.liquidations.review', $liquidation) }}">
            @csrf
            <div class="modal-body p-4">
                <p class="small text-muted">Submitted by {{ $liquidation->officer->fullname ?? 'the officer' }} for {{ $liquidation->proposal->project_title ?? 'this project' }}.</p>
                <div class="mb-3">
                    <label class="form-label-custom">Review note <span class="text-muted">(required when rejecting)</span></label>
                    <textarea name="review_notes" class="form-control-custom" rows="3" maxlength="1000"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="submit" name="action" value="reject" class="btn btn-outline-danger" formnovalidate><i class="bi bi-x-circle"></i> Reject</button>
                <button type="submit" name="action" value="approve" class="btn-primary-custom"><i class="bi bi-check2-circle"></i> Approve</button>
            </div>
        </form>
    </div></div>
</div>
@endif
@endforeach
@endsection
