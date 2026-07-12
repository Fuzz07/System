<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Denied | SSC System</title>
    <meta name="description" content="You do not have permission to access this resource.">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #312e81 100%);
            overflow: hidden;
            position: relative;
        }

        /* Decorative glowing orbs */
        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.15) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            border-radius: 50%;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            border-radius: 50%;
            pointer-events: none;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 3rem 3.5rem;
            text-align: center;
            max-width: 480px;
            width: 90%;
            position: relative;
            z-index: 1;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: fadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-icon-wrap {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.05));
            border: 2px solid rgba(239, 68, 68, 0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3);
            }

            50% {
                box-shadow: 0 0 0 14px rgba(239, 68, 68, 0);
            }
        }

        .error-icon-wrap i {
            font-size: 2.5rem;
            color: #f87171;
        }

        .error-code {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #f87171, #fca5a5);
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
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            text-decoration: none;
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.35);
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(99, 102, 241, 0.5);
            color: #fff;
        }

        .btn-home:active {
            transform: translateY(0);
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            margin: 2rem 0;
        }

        .footer-note {
            font-size: 0.78rem;
            color: #475569;
        }
    </style>
</head>

<body>
    <div class="error-card">
        <div class="ssc-badge"><i class="bi bi-shield-lock-fill"></i> SSC System</div>

        <div class="error-icon-wrap">
            <i class="bi bi-shield-x"></i>
        </div>

        <div class="error-code">403</div>
        <div class="error-title">Access Denied</div>
        <div class="error-desc">
            You don't have the required permissions to access this resource.
            Please contact your administrator if you believe this is a mistake.
        </div>

        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}" class="btn-home">
            <i class="bi bi-arrow-left"></i> Go Back
        </a>

        <div class="divider"></div>
        <div class="footer-note">Supreme Student Council — Transparency &amp; Budget System</div>
    </div>
</body>

</html>