<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 — System Maintenance | SSC System</title>
    <meta name="description" content="The Supreme Student Council System is currently undergoing scheduled maintenance.">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            background: linear-gradient(135deg, #090d16 0%, #0f172a 40%, #1e293b 100%);
            overflow: hidden;
            position: relative;
            padding: 20px;
        }

        /* Ambient glowing orbs */
        body::before {
            content: '';
            position: absolute;
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.14) 0%, transparent 70%);
            top: -150px;
            right: -150px;
            border-radius: 50%;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
            bottom: -120px;
            left: -120px;
            border-radius: 50%;
            pointer-events: none;
        }

        .maintenance-card {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 50px 40px;
            max-width: 580px;
            width: 100%;
            text-align: center;
            position: relative;
            z-index: 1;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .gear-container {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gear-bg {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(10px);
        }

        .gear-icon {
            font-size: 3.6rem;
            color: #f59e0b;
            display: inline-block;
            animation: spinGear 12s linear infinite;
        }

        @keyframes spinGear {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(245, 158, 11, 0.12);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 9999px;
            padding: 6px 16px;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 18px;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #f59e0b;
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(245, 158, 11, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
            }
        }

        h1 {
            color: #f8fafc;
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 14px;
            line-height: 1.25;
        }

        .message-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 16px 20px;
            color: #cbd5e1;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 28px;
            text-align: center;
        }

        .info-grid {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 28px;
            font-size: 0.82rem;
            color: #94a3b8;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-refresh {
            background: #f59e0b;
            color: #0f172a;
            font-weight: 700;
        }

        .btn-refresh:hover {
            background: #d97706;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
        }

        .btn-admin {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-admin:hover {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .footer-note {
            margin-top: 26px;
            font-size: 0.78rem;
            color: #64748b;
        }
    </style>
</head>

<body>
    <div class="maintenance-card">
        <div class="gear-container">
            <div class="gear-bg"></div>
            <i class="bi bi-gear-wide-connected gear-icon"></i>
        </div>

        <div class="status-badge">
            <span class="pulse-dot"></span>
            Scheduled Maintenance
        </div>

        <h1>Under Maintenance</h1>

        <div class="message-box">
            {{ $message ?? 'The system is currently undergoing scheduled maintenance and updates. Regular services will resume shortly. Thank you for your patience!' }}
        </div>

        <div class="info-grid">
            <div class="info-item">
                <i class="bi bi-shield-check text-warning"></i>
                <span>Data Protected</span>
            </div>
            <div class="info-item">
                <i class="bi bi-clock-history text-info"></i>
                <span>Back Online Soon</span>
            </div>
        </div>

        <div class="actions">
            <button onclick="window.location.reload();" class="btn btn-refresh">
                <i class="bi bi-arrow-clockwise"></i> Check Again
            </button>
            <a href="{{ url('/admin/dashboard') }}" class="btn btn-admin">
                <i class="bi bi-shield-lock"></i> Admin Portal
            </a>
        </div>

        <div class="footer-note">
            Supreme Student Council &bull; Transparency &amp; Budget System
        </div>
    </div>
</body>

</html>
