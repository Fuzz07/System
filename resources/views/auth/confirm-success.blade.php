<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified Successfully — SSC Transparency System</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <style>
        .success-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 10% 20%, rgba(30, 58, 138, 0.95) 0%, rgba(15, 23, 42, 0.99) 90%);
            padding: 20px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .success-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            max-width: 540px;
            width: 100%;
            text-align: center;
            color: #ffffff;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .animated-check-wrapper {
            width: 100px;
            height: 100px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            border: 2px solid rgba(16, 185, 129, 0.3);
            animation: pulseSuccess 2s infinite;
        }

        .success-icon {
            font-size: 50px;
            color: #10b981;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .status-badge {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: inline-block;
            margin-bottom: 24px;
            text-transform: uppercase;
        }

        .action-button {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            border: none;
            color: #ffffff;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
            margin-top: 10px;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
            color: #ffffff;
        }

        @keyframes pulseSuccess {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @keyframes scaleIn {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
<div class="success-page">
    <div class="success-card">
        <div class="animated-check-wrapper">
            <i class="bi bi-shield-check success-icon"></i>
        </div>

        <span class="status-badge">
            <i class="bi bi-clock-history"></i> Awaiting Approval
        </span>

        <h1 class="fw-bold mb-3" style="font-size: 1.85rem; letter-spacing: -0.5px;">Email Confirmed!</h1>
        
        <p class="text-white-50 mb-4" style="line-height: 1.6; font-size: 0.98rem;">
            Hello, <strong class="text-white">{{ $user->first_name }}</strong>! Your Microsoft 365 school email address (<strong>{{ $user->email }}</strong>) has been successfully verified.
        </p>

        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 16px; padding: 20px; text-align: left; margin-bottom: 30px;">
            <h6 class="fw-bold text-white d-flex align-items-center gap-2 mb-2" style="font-size:0.95rem;">
                <i class="bi bi-shield-fill-exclamation text-warning"></i> What happens next?
            </h6>
            <p class="text-white-50 mb-0" style="font-size: 0.88rem; line-height: 1.65;">
                Your verified account has been securely placed in the administrative queue. The **Supreme Student Council Administrator** will review your enrollment status. Once approved, your account will be fully activated, and you can access your portal.
            </p>
        </div>

        <a href="{{ route('login', 'student') }}" class="action-button">
            <i class="bi bi-box-arrow-in-right"></i> Return to Login Portal
        </a>
    </div>
</div>
</body>
</html>