<!DOCTYPE html>
<html lang="en">

<head>
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
            background: rgba(255,255,255,0.92);
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
            position: absolute;
            bottom: -30px;
            right: -30px;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(227, 79, 38, 0.06), transparent 70%);
            border-radius: 50%;
        }

        .feature-card:hover {
            background: #fff;
            border-color: rgba(227, 79, 38, 0.2);
            box-shadow: 0 16px 40px rgba(227, 79, 38, 0.06);
            transform: translateY(-4px);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #fff;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(227, 79, 38, 0.25);
        }

        .feature-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 10px;
        }

        .feature-desc {
            font-size: 0.875rem;
            color: var(--slate-500);
            line-height: 1.7;
        }

        /* ─── STATS SECTION ─── */
        .stats-section {
            padding: 80px 5%;
            background: linear-gradient(135deg, #0f172a 0%, var(--charcoal) 100%);
            position: relative;
            overflow: hidden;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.015'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .stats-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            position: relative;
            z-index: 1;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.04em;
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-number span {
            color: var(--primary-light);
        }

        .stat-label {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.45);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* ─── OFFICERS SECTION ─── */
        .officers-section {
            padding: 100px 5%;
            background: var(--slate-50);
        }

        .officers-grid {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 16px;
        }

        .officer-card {
            background: #fff;
            border: 1px solid var(--slate-200);
            border-radius: 18px;
            padding: 24px 12px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .officer-card:hover {
            border-color: rgba(227, 79, 38, 0.2);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
            transform: translateY(-4px);
        }

        .officer-avatar {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 900;
            color: #fff;
            margin: 0 auto 12px;
            box-shadow: 0 6px 16px rgba(227, 79, 38, 0.2);
        }

        .officer-name {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--slate-800);
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .officer-pos {
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* ─── CTA SECTION ─── */
        .cta-section {
            padding: 100px 5%;
            background: #fff;
            text-align: center;
        }

        .cta-inner {
            max-width: 700px;
            margin: 0 auto;
        }

        .cta-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 900;
            color: var(--slate-900);
            letter-spacing: -0.04em;
            line-height: 1.15;
            margin-bottom: 20px;
        }

        .cta-title span {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .cta-subtitle {
            font-size: 1rem;
            color: var(--slate-500);
            line-height: 1.7;
            margin-bottom: 40px;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        /* ─── FOOTER ─── */
        .footer {
            background: var(--charcoal);
            padding: 60px 5% 30px;
        }

        .footer-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
            margin-bottom: 48px;
            padding-bottom: 48px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .footer-brand img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .footer-brand-text {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
        }

        .footer-tagline {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 12px;
            max-width: 260px;
            line-height: 1.6;
        }

        .footer-links-group h4 {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 16px;
        }

        .footer-links-group a {
            display: block;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
            margin-bottom: 10px;
            transition: color 0.2s;
        }

        .footer-links-group a:hover {
            color: var(--primary-light);
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer-copyright {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.3);
        }

        .footer-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(227, 79, 38, 0.12);
            border: 1px solid rgba(227, 79, 38, 0.2);
            padding: 6px 14px;
            border-radius: 100px;
        }

        .footer-badge span {
            font-size: 0.75rem;
            font-weight: 700;
            color: #f06529;
        }

        /* ─── SCROLL ANIMATIONS ─── */
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1024px) {
            .portals-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .officers-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-inner {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .hero-inner {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-actions {
                justify-content: center;
            }

            .hero-stats {
                justify-content: center;
            }

            .hero-image {
                display: none;
            }

            .portals-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .officers-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .footer-top {
                flex-direction: column;
            }

            .nav-links .nav-link {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .portals-grid {
                grid-template-columns: 1fr 1fr;
            }

            .officers-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-inner {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>

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
            <a href="#portals" class="nav-link">Portals</a>
            <a href="{{ route('login', 'student') }}" class="nav-cta">
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
                    ABANTE PARTY &bull; S.Y. 2025–2026
                </div>
                <h1 class="hero-title">
                    Where Student<br>Governance Meets<br><span>Transparency</span>
                </h1>
                <p class="hero-subtitle">
                    The official Supreme Student Council Transparency and Budget Allocation System of Madridejos
                    Community College — empowering students through open governance and real-time accountability.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('login', 'student') }}" class="btn-primary-hero" id="hero-student-login">
                        <i class="bi bi-mortarboard-fill"></i> Access Student Portal
                    </a>
                    <a href="/app-debug.apk" class="btn-outline-hero" style="border-color: var(--primary-light); color: var(--primary-light);">
                        <i class="bi bi-android2"></i> Install Android App
                    </a>
                    <a href="#features" class="btn-outline-hero">
                        Explore Features <i class="bi bi-arrow-down"></i>
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-number">17<span>+</span></div>
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
                <h2 class="overview-title">Forge ahead with heart and honor — the SSC that advances open governance for MCC.</h2>
                <p class="overview-text">
                    This portal brings together students, officers, deans, and administrators in one modern system for budget transparency,
                    project management, election oversight, and community feedback. It is built to support informed decision-making and
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
                        <span>Real-time announcements, proposals, and feedback channels that connect the student body.</span>
                    </div>
                    <div class="overview-list-item">
                        <span class="overview-list-item-icon"><i class="bi bi-check-lg"></i></span>
                        <span>Professional digital experience designed for the unique needs of MCC’s student governance.</span>
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
            <p class="gallery-subtitle">A visual overview of the Supreme Student Council’s activities, community engagement, and leadership presence at MCC.</p>
        </div>
        <div class="gallery-grid">
            <div class="gallery-card fade-up" style="transition-delay: 0.04s;">
                <img src="{{ asset('assets/images/b1.jpg') }}" alt="SSC event photo 1">
                <div class="gallery-caption">Council leadership and faculty partners gathering for a collaborative planning session.</div>
            </div>
            <div class="gallery-card fade-up" style="transition-delay: 0.08s;">
                <img src="{{ asset('assets/images/b2.jpg') }}" alt="SSC event photo 2">
                <div class="gallery-caption">Student officers representing MCC at a campus event with pride and purpose.</div>
            </div>
            <div class="gallery-card fade-up" style="transition-delay: 0.12s;">
                <img src="{{ asset('assets/images/b3.jpg') }}" alt="SSC event photo 3">
                <div class="gallery-caption">The SSC team in action during an announcement or briefing session.</div>
            </div>
            <div class="gallery-card fade-up" style="transition-delay: 0.16s;">
                <img src="{{ asset('assets/images/b4.jpg') }}" alt="SSC event photo 4">
                <div class="gallery-caption">Community-driven engagement reflecting the council’s mission to serve MCC students.</div>
            </div>
        </div>
    </section>

    <!-- ─── IMAGE MODAL ─── -->
    <div class="image-modal-overlay" id="imageModal">
        <div class="image-modal-card">
            <button type="button" class="image-modal-close" id="imageModalClose" aria-label="Close image modal">×</button>
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
                <div class="stat-number">17<span>+</span></div>
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
                <span class="section-eyebrow">ABANTE Party · S.Y. 2025–2026</span>
                <h2 class="section-title">Meet the Executive Board</h2>
                <p class="section-subtitle">Your dedicated leaders committed to transparent and accountable student
                    governance.</p>
            </div>
            <div class="officers-grid">
                @php
                    $executives = [
                        ['name' => 'Villacarlos, Jireh Joy A.', 'position' => 'President'],
                        ['name' => 'Licardo, Juvy Irish C.', 'position' => 'Vice President'],
                        ['name' => 'Carabio, Margarette B.', 'position' => 'Secretary'],
                        ['name' => 'Maru, Florane D.', 'position' => 'Treasurer'],
                        ['name' => 'Salvaña, Althea Mae D.', 'position' => 'Auditor'],
                        ['name' => 'Manos, Shanei M.', 'position' => 'PIO'],
                        ['name' => 'Escala, Marlon', 'position' => 'PIO'],
                    ];
                @endphp
                @foreach($executives as $i => $exec)
                    @php
                        $parts = explode(',', $exec['name'], 2);
                        $last = trim($parts[0]);
                        $first = isset($parts[1]) ? trim($parts[1]) : '';
                        $initials = strtoupper(substr($last, 0, 1) . substr($first, 0, 1));
                        $fullname = trim($first . ' ' . $last);
                    @endphp
                    <div class="officer-card fade-up" style="transition-delay: {{ $i * 0.06 }}s">
                        <div class="officer-avatar">{{ $initials }}</div>
                        <div class="officer-name">{{ $fullname }}</div>
                        <div class="officer-pos">{{ $exec['position'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ─── PORTALS SECTION ─── -->
    <section class="portals-section" id="portals">
        <div style="max-width:1200px; margin: 0 auto;">
            <div class="section-header fade-up">
                <span class="section-eyebrow">Portal Access</span>
                <h2 class="section-title">One System, Every Role</h2>
                <p class="section-subtitle">Each portal is tailored to the specific responsibilities and access levels
                    of every stakeholder in the SSC.</p>
            </div>
            <div class="portals-grid">
                <a href="{{ route('login', 'student') }}" class="portal-card fade-up" id="student-portal-card"
                    style="transition-delay: 0s">
                    <div class="portal-icon" style="background: rgba(227,79,38,0.1); color: var(--primary);">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div class="portal-title">Student Portal</div>
                    <div class="portal-desc">View proposals, vote, give feedback, and follow SSC updates.</div>
                    <div class="portal-arrow">Enter <i class="bi bi-arrow-right"></i></div>
                </a>
                <a href="{{ route('login', 'officer') }}" class="portal-card fade-up" id="officer-portal-card"
                    style="transition-delay: 0.07s">
                    <div class="portal-icon" style="background: rgba(14,165,233,0.1); color: #0ea5e9;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div class="portal-title">Officer Portal</div>
                    <div class="portal-desc">Submit proposals, manage expenses, and post official announcements.</div>
                    <div class="portal-arrow">Enter <i class="bi bi-arrow-right"></i></div>
                </a>
                <a href="{{ route('login', 'treasurer') }}" class="portal-card fade-up" id="treasurer-portal-card"
                    style="transition-delay: 0.14s">
                    <div class="portal-icon" style="background: rgba(217,119,6,0.1); color: #d97706;">
                        <i class="bi bi-safe2-fill"></i>
                    </div>
                    <div class="portal-title">Treasurer Portal</div>
                    <div class="portal-desc">Release approved budgets, track disbursements and financial records.</div>
                    <div class="portal-arrow">Enter <i class="bi bi-arrow-right"></i></div>
                </a>
                <a href="{{ route('login', 'dean') }}" class="portal-card fade-up" id="dean-portal-card"
                    style="transition-delay: 0.21s">
                    <div class="portal-icon" style="background: rgba(139,92,246,0.1); color: #8b5cf6;">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <div class="portal-title">Dean Portal</div>
                    <div class="portal-desc">Review and approve department candidacies and election results.</div>
                    <div class="portal-arrow">Enter <i class="bi bi-arrow-right"></i></div>
                </a>
                <a href="{{ route('login', 'admin') }}" class="portal-card fade-up" id="admin-portal-card"
                    style="transition-delay: 0.28s">
                    <div class="portal-icon" style="background: rgba(236,72,153,0.1); color: #ec4899;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div class="portal-title">Admin Portal</div>
                    <div class="portal-desc">Full system control — users, settings, logs, and budget oversight.</div>
                    <div class="portal-arrow">Enter <i class="bi bi-arrow-right"></i></div>
                </a>
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
                <a href="{{ route('login', 'student') }}" class="btn-primary-hero" id="cta-student-login">
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
                <h4>Portals</h4>
                <a href="{{ route('login', 'student') }}">Student Portal</a>
                <a href="{{ route('login', 'officer') }}">Officer Portal</a>
                <a href="{{ route('login', 'treasurer') }}">Treasurer Portal</a>
                <a href="{{ route('login', 'dean') }}">Dean Portal</a>
                <a href="{{ route('login', 'admin') }}">Admin Portal</a>
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
                All rights reserved.</p>
            <div class="footer-badge">
                <i class="bi bi-patch-check-fill" style="color: #f06529; font-size: 0.85rem;"></i>
                <span>ABANTE PARTY · S.Y. 2025–2026</span>
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
    @include('partials.pwa-installer', ['floating' => true])
</body>

</html>