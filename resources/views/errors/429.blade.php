<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 — Too Many Requests | SSC System</title>
    <meta name="description" content="You have made too many requests. Please wait and try again.">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1c1917 40%, #292524 100%);
            overflow: hidden;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(245,158,11,0.15) 0%, transparent 70%);
            top: -150px; left: 50%;
            transform: translateX(-50%);
            border-radius: 50%; pointer-events: none;
        }

        .error-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 28px;
            padding: 3rem 3.5rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
            position: relative;
            z-index: 1;
            box-shadow: 0 32px 80px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.08);
            animation: fadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .error-icon-wrap {
            width: 90px; height: 90px;
            background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(245,158,11,0.05));
            border: 2px solid rgba(245,158,11,0.4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .error-icon-wrap i {
            font-size: 2.5rem;
            color: #fbbf24;
            animation: shake 0.5s ease-in-out infinite alternate;
        }
        @keyframes shake {
            from { transform: rotate(-10deg); }
            to   { transform: rotate(10deg); }
        }

        .error-code {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #fbbf24, #fde68a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            letter-spacing: -2px;
        }
        .error-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
        }
        .error-desc {
            font-size: 0.95rem;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .ssc-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(245,158,11,0.1);
            border: 1px solid rgba(245,158,11,0.25);
            color: #fcd34d;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
        }

        /* Countdown timer */
        .countdown-wrap {
            background: rgba(245,158,11,0.08);
            border: 1px solid rgba(245,158,11,0.2);
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 2rem;
        }
        .countdown-label {
            font-size: 0.78rem;
            color: #92400e;
            color: #fcd34d;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.5rem;
        }
        .countdown-timer {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fbbf24;
            font-variant-numeric: tabular-nums;
            letter-spacing: -1px;
        }
        .countdown-sub {
            font-size: 0.8rem;
            color: #78716c;
            margin-top: 0.25rem;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #0f172a;
            text-decoration: none;
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.2s;
            box-shadow: 0 8px 24px rgba(245,158,11,0.35);
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(245,158,11,0.5);
            color: #0f172a;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
            margin: 2rem 0;
        }
        .footer-note { font-size: 0.78rem; color: #475569; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="ssc-badge"><i class="bi bi-hourglass-split"></i> Rate Limited</div>

        <div class="error-icon-wrap">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>

        <div class="error-code">429</div>
        <div class="error-title">Too Many Requests</div>
        <div class="error-desc">
            You've exceeded the number of allowed login attempts.
            Your access has been temporarily suspended to protect your account.
        </div>

        <div class="countdown-wrap">
            <div class="countdown-label"><i class="bi bi-clock"></i> Try again in</div>
            <div class="countdown-timer" id="countdown">10:00</div>
            <div class="countdown-sub">minutes : seconds</div>
        </div>

        <a href="{{ url('/') }}" class="btn-home">
            <i class="bi bi-house-fill"></i> Return to Home
        </a>

        <div class="divider"></div>
        <div class="footer-note">Supreme Student Council — Transparency &amp; Budget System</div>
    </div>

    <script>
        // Read the Retry-After header value if available, default 600s (10 min)
        let seconds = parseInt('{{ $exception->getHeaders()["Retry-After"] ?? 600 }}', 10) || 600;
        const el = document.getElementById('countdown');

        function formatTime(s) {
            const m = Math.floor(s / 60).toString().padStart(2, '0');
            const sec = (s % 60).toString().padStart(2, '0');
            return `${m}:${sec}`;
        }

        el.textContent = formatTime(seconds);

        const timer = setInterval(() => {
            seconds--;
            if (seconds <= 0) {
                clearInterval(timer);
                el.textContent = '00:00';
                // Reload the page once the lockout expires
                setTimeout(() => window.location.href = '{{ url("/login") }}', 500);
            } else {
                el.textContent = formatTime(seconds);
            }
        }, 1000);
    </script>
</body>
</html>
