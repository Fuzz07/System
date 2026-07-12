<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | SSC System</title>
    <meta name="description" content="The page you are looking for could not be found.">
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
            background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, transparent 70%);
            top: -150px; right: -150px;
            border-radius: 50%; pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(16,185,129,0.1) 0%, transparent 70%);
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
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 28px;
            padding: 3rem 3.5rem;
            text-align: center;
            max-width: 480px;
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
            background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(59,130,246,0.05));
            border: 2px solid rgba(59,130,246,0.35);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .error-icon-wrap i { font-size: 2.5rem; color: #60a5fa; }

        .error-code {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #60a5fa, #93c5fd);
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
            margin-bottom: 2rem;
        }

        .ssc-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(59,130,246,0.12);
            border: 1px solid rgba(59,130,246,0.25);
            color: #93c5fd;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
        }

        .btn-group { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            text-decoration: none;
            padding: 0.8rem 1.6rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            box-shadow: 0 8px 24px rgba(59,130,246,0.35);
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(59,130,246,0.5);
            color: #fff;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: #94a3b8;
            text-decoration: none;
            padding: 0.8rem 1.6rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
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
    <!-- Twinkling stars background -->
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
        <div class="ssc-badge"><i class="bi bi-compass"></i> SSC System</div>

        <div class="error-icon-wrap">
            <i class="bi bi-map"></i>
        </div>

        <div class="error-code">404</div>
        <div class="error-title">Page Not Found</div>
        <div class="error-desc">
            The page you're looking for doesn't exist or may have been moved.
            Please check the URL and try again.
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
