@extends('layouts.app')
@section('sidebar-nav') @include('partials.sidebar-admin') @endsection

@section('content')
<div class="page-header"><div><h1>Student Feedback</h1><p>Review and respond to student concerns</p></div></div>

<div class="card"><div class="table-responsive-custom"><table class="table-custom">
    <thead><tr><th>#</th><th>Student</th><th>Message</th><th>Status</th><th>Reply</th><th>Date</th><th>Action</th></tr></thead>
    <tbody>
    @forelse($feedbacks as $i => $fb)
    <tr>
        <td style="color:#a0aec0;font-size:.8rem;">{{ $i+1 }}</td>
        <td style="font-weight:600;font-size:.85rem;">{{ $fb->student->fullname ?? 'Anonymous' }}</td>
        <td style="font-size:.82rem;max-width:200px;">{{ Str::limit($fb->message, 80) }}</td>
        <td>{!! \App\Helpers\SscHelper::statusBadge($fb->status) !!}</td>
        <td style="font-size:.78rem;color:#718096;max-width:150px;">{{ $fb->reply ? Str::limit($fb->reply, 60) : '—' }}</td>
        <td style="font-size:.78rem;white-space:nowrap;">{{ $fb->created_at?->format('M d, Y') }}</td>
        <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#replyModal{{ $fb->id }}" style="font-size:.72rem;"><i class="bi bi-reply"></i> Reply</button></td>
    </tr>
    @empty
    <tr><td colspan="7" class="text-center py-4 text-muted">No feedback yet.</td></tr>
    @endforelse
    </tbody>
</table></div></div>

@foreach($feedbacks as $fb)
<div class="modal fade" id="replyModal{{ $fb->id }}" tabindex="-1" aria-hidden="true">
<div class="modal-dialog"><div class="modal-content" style="border-radius:var(--radius);border:none;">
    <div class="modal-header modal-header-custom"><h5 class="modal-title" style="font-weight:700;">Reply to Feedback</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('admin.feedback.reply', $fb) }}">@csrf
        <div class="modal-body p-4">
            <div class="p-3 bg-light rounded-3 mb-3" style="font-size:.85rem;">{{ $fb->message }}</div>
            <label class="form-label-custom">Your Reply</label>
            <textarea name="reply" class="form-control-custom" rows="4" required style="resize:vertical;">{{ $fb->reply }}</textarea>
        </div>
        <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary-custom"><i class="bi bi-send"></i> Send Reply</button>
        </div>
    </form>
</div></div></div>
@endforeach
@endsection
