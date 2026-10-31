<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server Error | SSC System</title>
    <meta name="description" content="An internal server error occurred while processing your request.">
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
            background: linear-gradient(135deg, #0f172a 0%, #0c1a3a 40%, #1e3a5f 100%);
            overflow: hidden;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(239,68,68,0.15) 0%, transparent 70%);
            top: -150px; right: -150px;
            border-radius: 50%; pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(245,158,11,0.1) 0%, transparent 70%);
            bottom: -120px; left: -120px;
            border-radius: 50%; pointer-events: none;
        }

        .stars {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .star {
            position: absolute;
            width: 2px; height: 2px;
            background: rgba(255,255,255,0.6);
            border-radius: 50%;
            animation: twinkle var(--dur, 3s) ease-in-out infinite;
        }
        @keyframes twinkle {
            0%, 100% { opacity: 0.2; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.5); }
        }

        .error-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 25px 60px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05);
            position: relative;
            z-index: 10;
        }

        .ssc-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 9999px;
            padding: 0.35rem 0.9rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #fca5a5;
            margin-bottom: 1.75rem;
            letter-spacing: 0.03em;
        }

        .error-icon-wrap {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(239,68,68,0.2), rgba(245,158,11,0.1));
            border: 2px dashed rgba(239,68,68,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.75rem;
            color: #f87171;
            position: relative;
        }

        .error-code {
            font-size: 4.5rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #ffffff 30%, #fca5a5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.04em;
        }

        .error-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
        }

        .error-desc {
            font-size: 0.92rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            font-size: 0.88rem;
            font-weight: 600;
            padding: 0.7rem 1.4rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(239,68,68,0.35);
        }
        .btn-home:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: #94a3b8;
            font-size: 0.88rem;
            font-weight: 600;
            padding: 0.7rem 1.4rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.1);
            color: #e2e8f0;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
            margin: 2rem 0;
        }
        .footer-note {
            font-size: 0.78rem;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="stars" aria-hidden="true">
        @for ($i = 0; $i < 40; $i++)
            <div class="star" style="
                left: {{ rand(0,100) }}%;
                top: {{ rand(0,100) }}%;
                --dur: {{ rand(2,5) }}s;
                animation-delay: {{ rand(0,3000) }}ms;
                opacity: {{ rand(2,8)/10 }};
            "></div>
        @endfor
    </div>

    <div class="error-card">
        <div class="ssc-badge"><i class="bi bi-shield-exclamation"></i> SSC System</div>

        <div class="error-icon-wrap">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>

        <div class="error-code">500</div>
        <div class="error-title">Internal Server Error</div>
        <div class="error-desc">
            An unexpected error occurred while processing your request.
            Our team has been notified and is working to resolve it.
        </div>

        <div class="btn-group">
            <a href="/" class="btn-home">
                <i class="bi bi-house-fill"></i> Go Home
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i class="bi bi-arrow-left"></i> Go Back
            </a>
        </div>

        <div class="divider"></div>
        <div class="footer-note">Supreme Student Council — Transparency &amp; Budget System</div>
    </div>
</body>
</html>
