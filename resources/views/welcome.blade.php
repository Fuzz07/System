<!DOCTYPE html>
<html lang="en">

<head>
    <script>
        if (navigator.userAgent && navigator.userAgent.includes('SSCStudentApp')) {
            window.location.replace("{{ route('login.student') }}");
        }
    </script>
    @include('partials.security-guard')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSC Transparency System — Madridejos Community College</title>
    <meta name="description"
        content="The official Supreme Student Council Transparency and Budget Allocation System of Madridejos Community College. Track budgets, view proposals, and participate in student governance.">
    <meta name="keywords"
        content="SSC, Supreme Student Council, Transparency, Budget, Madridejos Community College, Student Government">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/icon-192.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SSC Student">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #e34f26;
            --primary-light: #f06529;
            --primary-dark: #d13f19;
            --charcoal: #18181b;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fff;
            color: var(--slate-800);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* ─── NAVBAR ─── */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 0 5%;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.07);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .nav-brand img {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }

        .nav-brand-text {
            display: flex;
            flex-direction: column;
        }

        .nav-brand-title {
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--slate-900);
            line-height: 1;
        }

        .nav-brand-sub {
            font-size: 0.65rem;
            color: var(--slate-500);
            font-weight: 500;
            line-height: 1.4;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--slate-600);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: var(--primary);
            background: rgba(227, 79, 38, 0.06);
        }

        .nav-cta {
            background: var(--primary);
            color: #fff !important;
            border-radius: 10px;
            padding: 10px 22px;
            font-weight: 700;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(227, 79, 38, 0.3);
        }

        .nav-cta:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(227, 79, 38, 0.4);
        }

        /* ─── HERO SECTION ─── */
        .hero {
            min-height: 100vh;
            padding: 120px 5% 80px;
            background: linear-gradient(135deg, #0f172a 0%, #18181b 50%, #1e0a02 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(227, 79, 38, 0.12) 0%, transparent 70%);
            top: -200px;
            right: -100px;
            border-radius: 50%;
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(227, 79, 38, 0.08) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            border-radius: 50%;
        }

        .hero-inner {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 60px;
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(227, 79, 38, 0.15);
            border: 1px solid rgba(227, 79, 38, 0.3);
            color: #f06529;
            padding: 6px 14px 6px 8px;
            border-radius: 100px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 24px;
        }

        .hero-badge-dot {
            width: 22px;
            height: 22px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
        }

        .hero-title {
            font-size: clamp(2.2rem, 4vw, 3.5rem);
            font-weight: 900;
            color: #fff;
            line-height: 1.1;
            letter-spacing: -0.04em;
            margin-bottom: 20px;
        }

        .hero-title span {
            background: linear-gradient(135deg, #e34f26, #f9a47a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.7;
            margin-bottom: 40px;
            font-weight: 400;
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-primary-hero {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            box-shadow: 0 8px 24px rgba(227, 79, 38, 0.35);
        }

        .btn-primary-hero:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(227, 79, 38, 0.45);
            color: #fff;
        }

        .btn-outline-hero {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            backdrop-filter: blur(8px);
        }

        .btn-outline-hero:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        .hero-image {
            position: relative;
        }

        .hero-image-frame {
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.07);
        }

        .hero-image-frame img {
            width: 100%;
            height: auto;
            display: block;
        }

        .hero-stats {
            display: flex;
            gap: 32px;
            margin-top: 48px;
            padding-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .hero-stat {
            text-align: left;
        }

        .hero-stat-number {
            font-size: 1.8rem;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .hero-stat-number span {
            color: var(--primary-light);
        }

        .hero-stat-label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.45);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 4px;
        }

        /* ─── SSC OVERVIEW SECTION ─── */
        .overview-section {
            padding: 100px 5%;
            background: #f8fafc;
        }

        .overview-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
            gap: 32px;
            align-items: center;
        }

        .overview-card {
            background: #fff;
            border-radius: 28px;
            padding: 40px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
        }

        .overview-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--primary);
            margin-bottom: 16px;
        }

        .overview-title {
            font-size: clamp(2rem, 2.8vw, 3.2rem);
            line-height: 1.05;
            font-weight: 900;
            color: var(--slate-900);
            margin-bottom: 18px;
        }

        .overview-text {
            font-size: 1rem;
            line-height: 1.75;
            color: var(--slate-600);
            margin-bottom: 28px;
        }

        .overview-list {
            display: grid;
            gap: 12px;
            margin-top: 12px;
        }

        .overview-list-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: var(--slate-600);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .overview-list-item-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: rgba(227, 79, 38, 0.12);
            display: grid;
            place-items: center;
            color: var(--primary);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .overview-image {
            width: 100%;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.08);
        }

        .overview-image img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }

        .gallery-section {
            padding: 60px 5% 100px;
            background: #fff;
        }

        .gallery-header {
            text-align: center;
            margin-bottom: 42px;
        }

        .gallery-title {
            font-size: clamp(2rem, 2.5vw, 2.8rem);
            font-weight: 900;
            color: var(--slate-900);
            margin-bottom: 12px;
        }

        .gallery-subtitle {
            font-size: 1rem;
            color: var(--slate-500);
            line-height: 1.75;
            max-width: 700px;
            margin: 0 auto;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            max-width: 1280px;
            margin: 0 auto;
        }

        .gallery-card {
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #fff;
        }

        .image-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.78);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 24px;
        }

        .image-modal-overlay.active {
            display: flex;
        }

        .image-modal-card {
            width: 100%;
            max-width: 960px;
            border-radius: 28px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.24);
            position: relative;
        }

        .image-modal-photo {
            width: 100%;
            height: auto;
            display: block;
            max-height: 78vh;
            object-fit: contain;
            background: #000;
        }

        .image-modal-footer {
            padding: 18px 24px 24px;
            background: #fff;
        }

        .image-modal-caption {
            font-size: 0.95rem;
            color: var(--slate-600);
            line-height: 1.7;
        }

        .image-modal-close {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: none;
            background: rgba(255, 255, 255, 0.92);
            color: var(--slate-900);
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12);
        }

        .gallery-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 26px 65px rgba(15, 23, 42, 0.12);
        }

        .gallery-card img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            display: block;
            cursor: pointer;
            transition: transform 0.35s ease, filter 0.35s ease;
        }

        .gallery-card img:hover {
            transform: scale(1.04);
            filter: brightness(1.03);
        }

        .gallery-caption {
            padding: 18px 20px 22px;
            font-size: 0.93rem;
            color: var(--slate-600);
            line-height: 1.6;
            background: #fff;
        }

        @media (max-width: 980px) {
            .overview-inner {
                grid-template-columns: 1fr;
            }

            .gallery-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 680px) {
            .overview-card {
                padding: 28px;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 680px) {
            .overview-card {
                padding: 28px;
            }
        }

        /* ─── PORTALS SECTION ─── */
        .portals-section {
            padding: 100px 5%;
            background: var(--slate-50);
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-eyebrow {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--primary);
            margin-bottom: 14px;
        }

        .section-title {
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            font-weight: 900;
            color: var(--slate-900);
            letter-spacing: -0.03em;
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .section-subtitle {
            font-size: 1rem;
            color: var(--slate-500);
            line-height: 1.7;
            max-width: 520px;
            margin: 0 auto;
        }

        .portals-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .portal-card {
            background: #fff;
            border: 1px solid var(--slate-200);
            border-radius: 20px;
            padding: 28px 18px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .portal-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: transparent;
        }

        .portal-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .portal-title {
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--slate-800);
        }

        .portal-desc {
            font-size: 0.75rem;
            color: var(--slate-500);
            line-height: 1.5;
        }

        .portal-arrow {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: auto;
        }

        /* ─── FEATURES SECTION ─── */
        .features-section {
            padding: 100px 5%;
            background: #fff;
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        .feature-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 24px;
            padding: 36px 30px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            padding: 32px;
            border-radius: 20px;
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            background: #fff;
            border-color: rgba(227, 79, 38, 0.3);
            box-shadow: 0 12px 32px rgba(227, 79, 38, 0.08);
            transform: translateY(-3px);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(227, 79, 38, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 10px;
            letter-spacing: -0.3px;
        }

        .feature-desc {
            font-size: 0.88rem;
            color: var(--slate-600);
            line-height: 1.7;
        }

        /* ─── STATS ─── */
        .stats-section {
            padding: 64px 32px;
            background: linear-gradient(135deg, #18181b 0%, #0f172a 100%);
            color: #fff;
        }

        .stats-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            text-align: center;
        }

        .stat-item {
            padding: 16px;
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 6px;
        }

        .stat-number span {
            color: var(--primary-light);
        }

        .stat-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--slate-400);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* ─── OFFICERS ─── */
        .officers-section {
            padding: 96px 32px;
            background: var(--slate-50);
        }

        .officers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 48px;
        }

        .officer-card {
            padding: 24px 18px;
            border-radius: 16px;
            background: #fff;
            border: 1px solid var(--slate-200);
            text-align: center;
            transition: all 0.3s ease;
        }

        .officer-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.07);
            border-color: rgba(227, 79, 38, 0.3);
        }

        .officer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            margin: 0 auto 14px;
        }

        .officer-name {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 4px;
        }

        .officer-pos {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ─── CTA ─── */
        .cta-section {
            padding: 96px 32px;
            background: #fff;
        }

        .cta-inner {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            padding: 64px 48px;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(227, 79, 38, 0.04) 0%, rgba(249, 115, 22, 0.06) 100%);
            border: 1px solid rgba(227, 79, 38, 0.15);
        }

        .cta-title {
            font-size: 2.6rem;
            font-weight: 900;
            color: var(--slate-900);
            letter-spacing: -1px;
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .cta-title span {
            color: var(--primary);
        }

        .cta-subtitle {
            font-size: 1rem;
            color: var(--slate-600);
            line-height: 1.7;
            max-width: 520px;
            margin: 0 auto 36px;
        }

        .cta-buttons {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        /* ─── FOOTER ─── */
        .footer {
            padding: 64px 32px 32px;
            background: #0f172a;
            color: var(--slate-400);
        }

        .footer-top {
            max-width: 1200px;
            margin: 0 auto 48px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 64px;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            margin-bottom: 16px;
        }

        .footer-brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .footer-brand-text {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
        }

        .footer-tagline {
            font-size: 0.88rem;
            line-height: 1.7;
            max-width: 440px;
        }

        .footer-links-group h4 {
            font-size: 0.85rem;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 16px;
        }

        .footer-links-group a {
            display: block;
            font-size: 0.88rem;
            color: var(--slate-400);
            text-decoration: none;
            margin-bottom: 10px;
            transition: color 0.2s ease;
        }

        .footer-links-group a:hover {
            color: var(--primary-light);
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 0.82rem;
        }

        .footer-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            color: var(--slate-300);
        }

        /* ─── MODAL ─── */
        .image-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .image-modal-overlay.active {
            display: flex;
        }

        .image-modal-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            max-width: 700px;
            width: 100%;
            position: relative;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        }

        .image-modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .image-modal-photo {
            width: 100%;
            max-height: 480px;
            object-fit: cover;
            display: block;
        }

        .image-modal-footer {
            padding: 16px 20px;
        }

        .image-modal-caption {
            font-size: 0.88rem;
            color: var(--slate-600);
            line-height: 1.5;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 960px) {
            .hero-inner, .overview-inner {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .stats-inner {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-top {
                grid-template-columns: 1fr;
                gap: 32px;
            }
        }

        @media (max-width: 640px) {
            .navbar {
                padding: 14px 20px;
            }

            .nav-links a:not(.nav-cta) {
                display: none;
            }

            .hero {
                padding: 100px 20px 60px;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-stats {
                gap: 20px;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Fade-up animations */
        .fade-up {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body>

    @php
        $activeSyObj = \App\Models\SchoolYear::where('is_active', 1)->first();
        $activeSyLabel = $activeSyObj?->label ?? 'Current';
        $currentOfficers = \App\Models\User::activeOfficers()->get();
    @endphp

    <!-- ─── NAVBAR ─── -->
    <nav class="navbar" id="mainNav">
        <a href="#" class="nav-brand">
            <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC Logo">
            <div class="nav-brand-text">
                <span class="nav-brand-title">SSC System</span>
                <span class="nav-brand-sub">Madridejos Community College</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="#home" class="nav-link">Home</a>
            <a href="#features" class="nav-link">Features</a>
            <a href="#officers" class="nav-link">Officers</a>

            <a href="{{ route('login.student') }}" class="nav-cta">
                <i class="bi bi-box-arrow-in-right"></i> Student Login
            </a>
        </div>
    </nav>

    <!-- ─── HERO SECTION ─── -->
    <section class="hero" id="home">
        <div class="hero-inner">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="hero-badge-dot"><i class="bi bi-lightning-charge-fill" style="color:#fff;"></i></span>
                    Supreme Student Council &bull; S.Y. {{ $activeSyLabel }}
                </div>
                <h1 class="hero-title">
                    Where Student<br>Governance Meets<br><span>Transparency</span>
                </h1>
                <p class="hero-subtitle">
                    The official Supreme Student Council Transparency and Budget Allocation System of Madridejos
                    Community College — empowering students through open governance and real-time accountability.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('login.student') }}" class="btn-primary-hero" id="hero-student-login">
                        <i class="bi bi-mortarboard-fill"></i> Access Student Portal
                    </a>
                    <a href="#features" class="btn-outline-hero">
                        Explore Features <i class="bi bi-arrow-down"></i>
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-number">{{ $currentOfficers->count() ?: '10+' }}<span>+</span></div>
                        <div class="hero-stat-label">Council Officers</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-number">5<span>+</span></div>
                        <div class="hero-stat-label">Departments</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-number">100<span>%</span></div>
                        <div class="hero-stat-label">Transparent</div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-image-frame">
                    <img src="{{ asset('assets/images/baner_landingpage.jpg') }}" alt="SSC Dashboard Illustration">
                </div>
            </div>
        </div>
    </section>

    <!-- ─── SSC OVERVIEW SECTION ─── -->
    <section class="overview-section" id="overview">
        <div class="overview-inner">
            <div class="overview-card fade-up">
                <div class="overview-eyebrow">
                    <i class="bi bi-building"></i> Supreme Student Council
                </div>
                <h2 class="overview-title">Forge ahead with heart and honor — the SSC that advances open governance for
                    MCC.</h2>
                <p class="overview-text">
                    This portal brings together students, officers, deans, and administrators in one modern system for
                    budget transparency,
                    project management, election oversight, and community feedback. It is built to support informed
                    decision-making and
                    encourage meaningful participation across the college.
                </p>
                <div class="overview-list">
                    <div class="overview-list-item">
                        <span class="overview-list-item-icon"><i class="bi bi-check-lg"></i></span>
                        <span>Transparent budget and expense tracking across official school-year allocations.</span>
                    </div>
                    <div class="overview-list-item">
                        <span class="overview-list-item-icon"><i class="bi bi-check-lg"></i></span>
                        <span>Secure candidate filing and verified voting for student council officers.</span>
                    </div>
                    <div class="overview-list-item">
                        <span class="overview-list-item-icon"><i class="bi bi-check-lg"></i></span>
                        <span>Real-time announcements, proposals, and feedback channels that connect the student
                            body.</span>
                    </div>
                    <div class="overview-list-item">
                        <span class="overview-list-item-icon"><i class="bi bi-check-lg"></i></span>
                        <span>Professional digital experience designed for the unique needs of MCC’s student
                            governance.</span>
                    </div>
                </div>
            </div>
            <div class="overview-image fade-up" style="animation-delay: 0.06s;">
                <img src="{{ asset('assets/images/b5.jpg') }}" alt="Madridejos Community College SSC" />
            </div>
        </div>
    </section>

    <!-- ─── SSC PHOTO GALLERY SECTION ─── -->
    <section class="gallery-section" id="gallery">
        <div class="gallery-header fade-up">
            <div class="gallery-title">Student council moments captured in action</div>
            <p class="gallery-subtitle">A visual overview of the Supreme Student Council’s activities, community
                engagement, and leadership presence at MCC.</p>
        </div>
        <div class="gallery-grid">
            <div class="gallery-card fade-up" style="transition-delay: 0.04s;">
                <img src="{{ asset('assets/images/b1.jpg') }}" alt="SSC event photo 1">
                <div class="gallery-caption">Council leadership and faculty partners gathering for a collaborative
                    planning session.</div>
            </div>
            <div class="gallery-card fade-up" style="transition-delay: 0.08s;">
                <img src="{{ asset('assets/images/b2.jpg') }}" alt="SSC event photo 2">
                <div class="gallery-caption">Student officers representing MCC at a campus event with pride and purpose.
                </div>
            </div>
            <div class="gallery-card fade-up" style="transition-delay: 0.12s;">
                <img src="{{ asset('assets/images/b3.jpg') }}" alt="SSC event photo 3">
                <div class="gallery-caption">The SSC team in action during an announcement or briefing session.</div>
            </div>
            <div class="gallery-card fade-up" style="transition-delay: 0.16s;">
                <img src="{{ asset('assets/images/b4.jpg') }}" alt="SSC event photo 4">
                <div class="gallery-caption">Community-driven engagement reflecting the council’s mission to serve MCC
                    students.</div>
            </div>
        </div>
    </section>

    <!-- ─── IMAGE MODAL ─── -->
    <div class="image-modal-overlay" id="imageModal">
        <div class="image-modal-card">
            <button type="button" class="image-modal-close" id="imageModalClose"
                aria-label="Close image modal">×</button>
            <img class="image-modal-photo" id="imageModalPhoto" src="" alt="SSC photo preview">
            <div class="image-modal-footer">
                <div class="image-modal-caption" id="imageModalCaption"></div>
            </div>
        </div>
    </div>

    <!-- ─── FEATURES SECTION ─── -->
    <section class="features-section" id="features">
        <div style="max-width:1200px; margin: 0 auto;">
            <div class="section-header fade-up">
                <span class="section-eyebrow">Platform Features</span>
                <h2 class="section-title">Built for Transparency,<br>Designed for Action</h2>
                <p class="section-subtitle">Everything the SSC and its students need to manage, track, and communicate
                    budgets effectively.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card fade-up" style="transition-delay: 0.05s">
                    <div class="feature-icon"><i class="bi bi-bar-chart-fill"></i></div>
                    <div class="feature-title">Budget Tracking</div>
                    <div class="feature-desc">Real-time visibility into budget allocations, expenditures, remaining
                    balances, and financial reports across all departments and school years.</div>
                </div>
                <div class="feature-card fade-up" style="transition-delay: 0.1s">
                    <div class="feature-icon"><i class="bi bi-file-earmark-check-fill"></i></div>
                    <div class="feature-title">Project Proposals</div>
                    <div class="feature-desc">Officers can file, track, and update project proposals while students can
                        view, discuss, and comment on approved initiatives.</div>
                </div>
                <div class="feature-card fade-up" style="transition-delay: 0.15s">
                    <div class="feature-icon"><i class="bi bi-megaphone-fill"></i></div>
                    <div class="feature-title">Announcements</div>
                    <div class="feature-desc">Stay informed with official SSC announcements linked to completed
                        projects, school events, and council updates.</div>
                </div>
                <div class="feature-card fade-up" style="transition-delay: 0.2s">
                    <div class="feature-icon"><i class="bi bi-chat-heart-fill"></i></div>
                    <div class="feature-title">Student Feedback</div>
                    <div class="feature-desc">Students can send feedback and suggestions directly to the SSC, building
                        an open channel between the council and the student body.</div>
                </div>
                <div class="feature-card fade-up" style="transition-delay: 0.25s">
                    <div class="feature-icon"><i class="bi bi-shield-fill-check"></i></div>
                    <div class="feature-title">Secure Voting</div>
                    <div class="feature-desc">Participate in official SSC elections securely, with real-time candidate
                        results published after the voting period ends.</div>
                </div>
                <div class="feature-card fade-up" style="transition-delay: 0.3s">
                    <div class="feature-icon"><i class="bi bi-phone-fill"></i></div>
                    <div class="feature-title">Mobile App</div>
                    <div class="feature-desc">A native Android app that lets students access the full portal on their
                        smartphones — optimized for performance and native experience.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ─── STATS SECTION ─── -->
    <section class="stats-section">
        <div class="stats-inner">
            <div class="stat-item fade-up">
                <div class="stat-number">₱340<span>k+</span></div>
                <div class="stat-label">Budget Managed</div>
            </div>
            <div class="stat-item fade-up" style="transition-delay: 0.1s">
                <div class="stat-number">{{ $currentOfficers->count() ?: '10+' }}<span>+</span></div>
                <div class="stat-label">SSC Officers</div>
            </div>
            <div class="stat-item fade-up" style="transition-delay: 0.2s">
                <div class="stat-number">5<span></span></div>
                <div class="stat-label">Departments</div>
            </div>
            <div class="stat-item fade-up" style="transition-delay: 0.3s">
                <div class="stat-number">100<span>%</span></div>
                <div class="stat-label">Open Governance</div>
            </div>
        </div>
    </section>

    <!-- ─── OFFICERS SECTION ─── -->
    <section class="officers-section" id="officers">
        <div style="max-width:1100px; margin: 0 auto;">
            <div class="section-header fade-up">
                <span class="section-eyebrow">Supreme Student Council · S.Y. {{ $activeSyLabel }}</span>
                <h2 class="section-title">Meet the Council Officers</h2>
                <p class="section-subtitle">Your dedicated leaders committed to transparent and accountable student
                    governance.</p>
            </div>
            <div class="officers-grid">
                @forelse($currentOfficers as $i => $off)
                    <div class="officer-card fade-up" style="transition-delay: {{ $i * 0.05 }}s">
                        @if($off->photo_url)
                            <div class="officer-avatar" style="padding:0; overflow:hidden; border-radius:50%;">
                                <img src="{{ $off->photo_url }}" alt="{{ $off->fullname }}" style="width:100%; height:100%; object-fit:cover; display:block;">
                            </div>
                        @else
                            <div class="officer-avatar">{{ $off->avatar }}</div>
                        @endif
                        <div class="officer-name">{{ $off->fullname }}</div>
                        <div class="officer-pos">{{ $off->position }}</div>
                        @if($off->department)
                            <div class="text-muted small" style="font-size:0.75rem; margin-top:3px;">{{ $off->department }}</div>
                        @endif
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0">Active officers for School Year {{ $activeSyLabel }} will appear here once election results are announced.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ─── CTA SECTION ─── -->
    <section class="cta-section">
        <div class="cta-inner fade-up">
            <div style="display:flex; justify-content:center; margin-bottom:28px;">
                <img src="{{ asset('assets/images/mcc_logo.png') }}" alt="MCC Logo"
                    style="height:60px; object-fit:contain;">
            </div>
            <h2 class="cta-title">Ready to experience<br><span>transparent governance?</span></h2>
            <p class="cta-subtitle">Sign into your portal and become part of a more open, accountable, and participatory
                student council system.</p>
            <div class="cta-buttons">
                <a href="{{ route('login.student') }}" class="btn-primary-hero" id="cta-student-login">
                    <i class="bi bi-mortarboard-fill"></i> Student Login
                </a>
                <a href="{{ route('register') }}" class="btn-outline-hero"
                    style="background: #f1f5f9; border-color: #e2e8f0; color: var(--slate-700);" id="cta-register">
                    <i class="bi bi-person-plus-fill"></i> Create Account
                </a>
            </div>
        </div>
    </section>

    <!-- ─── FOOTER ─── -->
    <footer class="footer">
        <div class="footer-top">
            <div>
                <a href="#" class="footer-brand">
                    <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC Logo">
                    <span class="footer-brand-text">SSC Transparency System</span>
                </a>
                <p class="footer-tagline">The official Supreme Student Council transparency and budget management
                    platform of Madridejos Community College.</p>
            </div>

            <div class="footer-links-group">
                <h4>Quick Links</h4>
                <a href="#features">Features</a>
                <a href="#officers">Officers</a>
                <a href="{{ route('register') }}">Register</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="footer-copyright">&copy; {{ date('Y') }} Supreme Student Council — Madridejos Community College.
                All rights reserved. </p>
            <p style="color: red;">Developed by : Dave A.</p>
            <div class="footer-badge">
                <i class="bi bi-patch-check-fill" style="color: #f06529; font-size: 0.85rem;"></i>
                <span>Supreme Student Council · S.Y. {{ $activeSyLabel }}</span>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll behavior
        const navbar = document.getElementById('mainNav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Intersection observer for fade-up animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

        const imageModal = document.getElementById('imageModal');
        const imageModalPhoto = document.getElementById('imageModalPhoto');
        const imageModalCaption = document.getElementById('imageModalCaption');
        const imageModalClose = document.getElementById('imageModalClose');

        document.querySelectorAll('.gallery-card img').forEach(img => {
            img.addEventListener('click', () => {
                imageModalPhoto.src = img.src;
                imageModalPhoto.alt = img.alt;
                imageModalCaption.textContent = img.closest('.gallery-card').querySelector('.gallery-caption').textContent;
                imageModal.classList.add('active');
            });
        });

        const closeImageModal = () => {
            imageModal.classList.remove('active');
            imageModalPhoto.src = '';
            imageModalCaption.textContent = '';
        };

        imageModalClose.addEventListener('click', closeImageModal);
        imageModal.addEventListener('click', (event) => {
            if (event.target === imageModal) {
                closeImageModal();
            }
        });
    </script>

    <script>
        // Hide the "Install Android App" button if already running inside the SSC APK WebView.
        // This is a client-side guard that pairs with the server-side check.
        (function () {
            if (navigator.userAgent && navigator.userAgent.includes('SSCStudentApp')) {
                // Hide all install/download APK buttons
                document.querySelectorAll('a[href*=".apk"], a[href*="ssc-student-app"]').forEach(function (el) {
                    el.style.display = 'none';
                });
                // Hide any element with text containing "Install Android App" or "Download APK"
                document.querySelectorAll('.btn-outline-hero, [id*="apk"], [id*="android"]').forEach(function (el) {
                    if (el.textContent && (el.textContent.includes('Install Android') || el.textContent.includes('Download APK'))) {
                        el.style.display = 'none';
                    }
                });
                // Hide the app download modal if it exists (shouldn't be rendered from server but safety net)
                var modal = document.getElementById('appDownloadModal');
                if (modal) { modal.remove(); }
            }
        })();
    </script>
    <script src="{{ asset('assets/js/main.js') }}?v=1.0.3"></script>
</body>

</html>