{{-- ══════════════════════════════════════
Auth Backdrop — Shared Partial
Decorative layers behind the glass card on every auth screen. Purely
ornamental: all layers are aria-hidden and pointer-events:none, so nothing
here can catch a tap meant for the form.

Drop it in as the FIRST child of .login-page (which is position:relative and
overflow:hidden, so these are clipped to the page and sit under the card at
z-index 1).

Usage: @include('partials.auth-backdrop')
═══════════════════════════════════════ --}}

@once
<style>
    .login-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(150deg,
            rgba(255, 255, 255, 0.28) 0%,
            rgba(255, 255, 255, 0.04) 32%,
            transparent 55%,
            rgba(227, 79, 38, 0.16) 100%);
        /* Punch the fill out so only the 1px frame paints. */
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        mask-composite: exclude;
        pointer-events: none;
    }

    .auth-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
        overflow: hidden;
        pointer-events: none;
    }

    /* ── Drifting colour orbs ── */
    .auth-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(70px);
        opacity: 0.7;
        will-change: transform;
    }

    .auth-orb-1 {
        width: 420px;
        height: 420px;
        top: -120px;
        left: -110px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.85) 0%, transparent 70%);
        animation: authDrift1 22s ease-in-out infinite;
    }

    .auth-orb-2 {
        width: 360px;
        height: 360px;
        bottom: -130px;
        right: -90px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.7) 0%, transparent 70%);
        animation: authDrift2 27s ease-in-out infinite;
    }

    /* The brand orange, used once and kept small — it reads as an accent
       rather than a third competing wash. */
    .auth-orb-3 {
        width: 280px;
        height: 280px;
        top: 45%;
        right: 12%;
        background: radial-gradient(circle, rgba(227, 79, 38, 0.5) 0%, transparent 70%);
        animation: authDrift3 31s ease-in-out infinite;
    }

    @keyframes authDrift1 {
        0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
        50%      { transform: translate3d(60px, 50px, 0) scale(1.12); }
    }

    @keyframes authDrift2 {
        0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
        50%      { transform: translate3d(-70px, -40px, 0) scale(1.08); }
    }

    @keyframes authDrift3 {
        0%, 100% { transform: translate3d(0, 0, 0) scale(0.95); }
        50%      { transform: translate3d(-40px, 60px, 0) scale(1.15); }
    }

    /* ── Dot grid ── two offset dot fields give the depth a single grid lacks */
    .auth-grid {
        position: absolute;
        inset: -40px;
        background-image:
            radial-gradient(rgba(255, 255, 255, 0.12) 1px, transparent 1px),
            radial-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px);
        background-size: 44px 44px, 88px 88px;
        background-position: 0 0, 22px 22px;
        mask-image: radial-gradient(ellipse 80% 65% at 50% 40%, #000 30%, transparent 78%);
        -webkit-mask-image: radial-gradient(ellipse 80% 65% at 50% 40%, #000 30%, transparent 78%);
    }

    /* ── Slow aurora sweep behind everything ── */
    .auth-aurora {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 150vmax;
        height: 150vmax;
        margin: -75vmax 0 0 -75vmax;
        background: conic-gradient(from 0deg,
            transparent 0deg,
            rgba(99, 102, 241, 0.13) 60deg,
            transparent 140deg,
            rgba(14, 165, 233, 0.11) 220deg,
            transparent 300deg);
        animation: authSpin 60s linear infinite;
        will-change: transform;
    }

    @keyframes authSpin {
        to { transform: rotate(360deg); }
    }

    /* ── Grounding fade so the card does not float on a busy edge ── */
    .auth-vignette {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(to bottom, rgba(10, 15, 29, 0.45) 0%, transparent 30%, transparent 62%, rgba(10, 15, 29, 0.72) 100%);
    }

    /* Everything here is decoration, so on smaller screens the heavy blurs come
       off first — they are the expensive layers and the least missed. */
    @media (max-width: 575.98px) {
        .auth-orb { filter: blur(52px); opacity: 0.55; }
        .auth-orb-3 { display: none; }
        .auth-aurora { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .auth-orb,
        .auth-aurora { animation: none; }
    }
</style>
@endonce

<div class="auth-bg" aria-hidden="true">
    <div class="auth-aurora"></div>
    <div class="auth-grid"></div>
    <div class="auth-orb auth-orb-1"></div>
    <div class="auth-orb auth-orb-2"></div>
    <div class="auth-orb auth-orb-3"></div>
    <div class="auth-vignette"></div>
</div>
