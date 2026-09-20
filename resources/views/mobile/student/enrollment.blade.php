@php
    $pageTitle = 'Enrollment Fee';
    $labels = ['gcash' => 'Manual GCash', 'instapay' => 'InstaPay', 'walk_in' => 'Walk-in', 'paymongo' => 'PayMongo'];
    $methodLabel = $payment ? ($labels[$payment->method] ?? ucfirst($payment->method)) : null;
    $manualMethod = old('payment_method', in_array($payment?->method, ['gcash', 'instapay'], true) ? $payment->method : 'gcash');
@endphp
@extends('layouts.mobile-student', ['pageTitle' => $pageTitle, 'showBack' => true, 'backUrl' => route('mobile.student.proposals')])

@section('content')
<style>
.fee-hero{margin-top:14px;padding:22px;color:#fff;border-radius:22px;background:linear-gradient(145deg,#172554,#2563eb);box-shadow:0 16px 35px rgba(30,64,175,.2)}
.fee-term{display:inline-flex;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.2);font-size:.72rem;font-weight:700}
.fee-total{margin-top:13px;font-size:2.35rem;font-weight:850;letter-spacing:-.04em;line-height:1}
.pay-card{margin-top:14px;padding:18px;border-radius:18px;background:#fff;border:1px solid #dbeafe}.pay-card.online{background:linear-gradient(180deg,#fff,#f8fbff)}
.option-title{color:#0f172a;font-size:1rem;font-weight:800}.option-copy{color:#64748b;font-size:.81rem;line-height:1.5;margin-top:6px}
.method-tags{display:flex;gap:6px;flex-wrap:wrap;margin:13px 0}.method-tags span{color:#1e40af;background:#eff6ff;border:1px solid #dbeafe;border-radius:999px;padding:4px 8px;font-size:.67rem;font-weight:750}
.secure-btn{width:100%;min-height:49px;border:0;border-radius:13px;color:#fff;background:linear-gradient(135deg,#2563eb,#4f46e5);font-weight:800;box-shadow:0 9px 18px rgba(37,99,235,.2)}.secure-btn:disabled{opacity:.55}
.divider{display:flex;align-items:center;gap:10px;margin:20px 0;color:#94a3b8;font-size:.67rem;font-weight:800;text-transform:uppercase}.divider:before,.divider:after{content:'';flex:1;height:1px;background:#e2e8f0}
.instructions{margin:14px 0;padding:14px;border-radius:13px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:.79rem;line-height:1.55}.instructions p{margin:0 0 7px}.instructions p:last-child{margin:0}
.ref{font-family:monospace;color:#92400e;background:#fef3c7;border-radius:5px;padding:1px 5px;font-weight:800}.pay-status{margin-top:14px;padding:14px;border-radius:14px;font-size:.81rem}.pay-status.paid{color:#065f46;background:#ecfdf5;border:1px solid #a7f3d0}.pay-status.pending{color:#92400e;background:#fffbeb;border:1px solid #fde68a}
</style>

<section class="fee-hero">
    <span class="fee-term"><i class="bi bi-calendar3 me-1"></i>{{ $currentSy }}</span>
    <div style="font-weight:750;margin-top:14px;opacity:.9">Semester enrollment fee</div>
    <div class="fee-total">{{ \App\Helpers\SscHelper::formatCurrency($amount) }}</div>
    <div style="font-size:.77rem;opacity:.74;margin-top:8px">One payment for the active semester</div>
</section>

@foreach(['success'=>'success','info'=>'warning','error'=>'danger','danger'=>'danger'] as $key=>$type)
    @if(session($key))<div class="m-alert m-alert-{{ $type }}" style="margin-top:12px">{{ session($key) }}</div>@endif
@endforeach
@if(request('paymongo') === 'cancelled')<div class="m-alert m-alert-warning" style="margin-top:12px">Checkout was closed. You were not charged.</div>@endif
@if($errors->any())<div class="m-alert m-alert-danger" style="margin-top:12px">{{ $errors->first() }}</div>@endif

@if($payment && $payment->status === 'paid')
    <div class="pay-status paid"><strong><i class="bi bi-check-circle-fill me-1"></i> Payment complete</strong><div style="margin-top:7px">{{ $methodLabel }} · {{ $payment->reference }}</div>@if($payment->paid_at)<div style="margin-top:3px;opacity:.8">{{ $payment->paid_at->format('M d, Y · h:i A') }}</div>@endif</div>
@else
    @if($payment)<div class="pay-status pending"><strong><i class="bi bi-clock-history me-1"></i> Payment pending</strong><div style="margin-top:4px">{{ $methodLabel }} · {{ $payment->reference }}</div></div>@endif

    <section class="pay-card online">
        <div class="option-title"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Pay with PayMongo <span style="font-size:.59rem;color:#1d4ed8;background:#dbeafe;padding:3px 6px;border-radius:999px">RECOMMENDED</span></div>
        <div class="option-copy">Secure checkout with automatic confirmation. No receipt upload needed.</div>
        <div class="method-tags"><span>GCash</span><span>QR Ph</span><span>Cards</span></div>
        <form method="POST" action="{{ route('mobile.student.enrollment.paymongo.checkout') }}" id="paymongo-form">@csrf
            <button class="secure-btn" type="submit" {{ $paymongoEnabled ? '' : 'disabled' }}><i class="bi bi-lock-fill me-2"></i>{{ $payment?->method === 'paymongo' ? 'Continue secure checkout' : 'Pay securely' }}</button>
        </form>
        <div style="text-align:center;color:#64748b;font-size:.69rem;margin-top:9px"><i class="bi bi-box-arrow-up-right"></i> Opens PayMongo secure checkout</div>
        @unless($paymongoEnabled)<div style="font-size:.71rem;color:#dc2626;text-align:center;margin-top:7px">Online checkout is not configured yet.</div>@endunless
    </section>

    <div class="divider">or manual transfer</div>
    <section class="pay-card">
        <div class="option-title">Upload a transfer receipt</div><div class="option-copy">Manual receipts are reviewed by the SSC office.</div>
        <form method="POST" action="{{ route('mobile.student.enrollment.store') }}" enctype="multipart/form-data" style="margin-top:14px">@csrf
            <div class="m-field"><label for="manual_method">Payment method</label><select name="payment_method" id="manual_method" style="padding:12px;border-radius:12px;border:1px solid #d1d5db;width:100%;font-weight:650;background:#fff"><option value="gcash" {{ $manualMethod === 'gcash' ? 'selected' : '' }}>GCash transfer</option><option value="instapay" {{ $manualMethod === 'instapay' ? 'selected' : '' }}>InstaPay / bank transfer</option></select></div>
            <div id="gcash_help" class="instructions"><p>Send <strong>{{ \App\Helpers\SscHelper::formatCurrency($amount) }}</strong> to {{ config('ssc.gcash_number') ?: 'the SSC GCash account' }}.</p><p>Use reference <span class="ref">{{ $payment?->reference ?? 'generated after submission' }}</span>, then save the receipt.</p></div>
            <div id="instapay_help" class="instructions" style="display:none"><p><strong>{{ config('ssc.bank_name') }}</strong><br>{{ config('ssc.bank_account_name') }}<br><strong style="font-family:monospace;color:#047857">{{ config('ssc.bank_account_number') }}</strong></p><p>Transfer <strong>{{ \App\Helpers\SscHelper::formatCurrency($amount) }}</strong> with reference <span class="ref">{{ $payment?->reference ?? 'generated after submission' }}</span>.</p></div>
            @if($payment?->proof_path)<div class="m-alert m-alert-warning" style="margin-bottom:12px">Receipt status: <strong>{{ ucfirst($payment->proof_status ?? 'pending') }}</strong>@if($payment->proof_notes)<br>{{ $payment->proof_notes }}@endif</div>@endif
            <div class="m-field" style="margin-bottom:16px"><label for="proof">Payment receipt</label><input id="proof" type="file" name="proof" accept="image/jpeg,image/png,application/pdf,video/mp4" style="padding:10px;border-radius:12px;border:1px solid #d1d5db;width:100%" required><div style="font-size:.69rem;color:#64748b;margin-top:6px">JPG, PNG, PDF, or MP4 · Up to 5 MB</div></div>
            <button type="submit" class="m-btn m-btn-primary" style="width:100%;min-height:48px;font-weight:800"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Submit receipt</button>
        </form>
    </section>
@endif
<script>
document.addEventListener('DOMContentLoaded',function(){const s=document.getElementById('manual_method'),g=document.getElementById('gcash_help'),i=document.getElementById('instapay_help');function update(){if(!s)return;g.style.display=s.value==='gcash'?'block':'none';i.style.display=s.value==='instapay'?'block':'none'}if(s){s.addEventListener('change',update);update()}const f=document.getElementById('paymongo-form');if(f)f.addEventListener('submit',function(){const b=f.querySelector('button');b.disabled=true;b.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Opening checkout…'})});
</script>
@endsection