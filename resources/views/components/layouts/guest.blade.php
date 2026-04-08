<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BAI') }} — {{ $title ?? '' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --auth-primary: #6366f1;
            --auth-purple: #8b5cf6;
            --auth-pink: #d946ef;
            --auth-cyan: #06b6d4;
            --auth-dark: #0a0a0f;
            --auth-dark-card: #111118;
            --auth-dark-input: #16161f;
            --auth-border: rgba(255, 255, 255, 0.06);
            --auth-border-hover: rgba(255, 255, 255, 0.12);
            --auth-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
        }

        body.auth-body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background: var(--auth-dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background */
        .auth-bg { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .auth-orb {
            position: absolute; border-radius: 50%;
            filter: blur(100px); opacity: 0.35;
            animation: orbDrift 25s ease-in-out infinite;
        }
        .auth-orb-1 { width: 500px; height: 500px; background: var(--auth-primary); top: -15%; left: -10%; }
        .auth-orb-2 { width: 400px; height: 400px; background: var(--auth-purple); bottom: -10%; right: -5%; animation-delay: -8s; }
        .auth-orb-3 { width: 300px; height: 300px; background: var(--auth-pink); top: 40%; left: 50%; animation-delay: -16s; opacity: 0.2; }

        @keyframes orbDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -30px) scale(1.08); }
            66% { transform: translate(-30px, 40px) scale(0.95); }
        }

        /* Grid overlay */
        .auth-grid {
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none; z-index: 1;
            mask-image: radial-gradient(ellipse at center, black 0%, transparent 70%);
        }

        /* Main wrapper */
        .auth-wrapper {
            position: relative; z-index: 2;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem;
        }

        /* Glass card */
        .auth-card {
            width: 100%; max-width: 460px;
            background: rgba(17, 17, 24, 0.75);
            backdrop-filter: blur(40px);
            border: 1px solid var(--auth-border);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.03) inset,
                0 30px 60px rgba(0, 0, 0, 0.5),
                0 0 100px rgba(99, 102, 241, 0.05);
            animation: cardIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Logo */
        .auth-logo {
            display: flex; flex-direction: column; align-items: center;
            margin-bottom: 2rem;
        }
        .auth-logo img { height: 48px; width: auto; }

        /* Headings */
        .auth-title {
            font-size: 1.75rem; font-weight: 800; color: #fff;
            letter-spacing: -0.02em; margin-bottom: 0.375rem;
        }
        .auth-subtitle {
            font-size: 0.9rem; color: #64748b;
            margin-bottom: 1.75rem;
        }

        /* OAuth buttons */
        .auth-oauth-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.5rem; }
        .auth-oauth-btn {
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.75rem 1rem; border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--auth-border);
            color: #94a3b8; font-size: 0.875rem; font-weight: 500;
            text-decoration: none;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .auth-oauth-btn:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: var(--auth-border-hover);
            color: #e2e8f0;
            transform: translateY(-1px);
        }
        .auth-oauth-btn svg { width: 18px; height: 18px; }

        /* Divider */
        .auth-divider {
            display: flex; align-items: center; gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1; height: 1px;
            background: var(--auth-border);
        }
        .auth-divider span {
            font-size: 0.75rem; color: #475569;
            text-transform: uppercase; letter-spacing: 0.1em; font-weight: 500;
            white-space: nowrap;
        }

        /* Inputs */
        .auth-field { margin-bottom: 1.25rem; }
        .auth-field:last-of-type { margin-bottom: 0; }
        .auth-label {
            display: block; font-size: 0.8rem; font-weight: 600;
            color: #94a3b8; margin-bottom: 0.5rem;
            letter-spacing: 0.02em;
        }
        .auth-input {
            width: 100%; padding: 0.75rem 1rem;
            background: var(--auth-dark-input);
            border: 1px solid var(--auth-border);
            border-radius: 12px;
            color: #e2e8f0; font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.25s ease;
            outline: none;
        }
        .auth-input::placeholder { color: #3b3b4f; }
        .auth-input:focus {
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12), 0 0 20px rgba(99, 102, 241, 0.08);
        }
        .auth-input.has-error {
            border-color: rgba(239, 68, 68, 0.5);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        .auth-error { font-size: 0.78rem; color: #f87171; margin-top: 0.375rem; }

        /* Locked input */
        .auth-locked-input {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: rgba(99, 102, 241, 0.06);
            border: 1px solid rgba(99, 102, 241, 0.15);
            border-radius: 12px; color: #a5b4fc; font-size: 0.9rem;
        }
        .auth-locked-input svg { width: 16px; height: 16px; color: #6366f1; flex-shrink: 0; }
        .auth-locked-hint { font-size: 0.72rem; color: #475569; margin-top: 0.375rem; }

        /* Row helpers */
        .auth-row-between {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 0.5rem;
        }

        /* Checkbox */
        .auth-checkbox-label {
            display: flex; align-items: center; gap: 0.5rem;
            cursor: pointer; font-size: 0.85rem; color: #64748b;
        }
        .auth-checkbox-label input[type="checkbox"] {
            width: 16px; height: 16px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: var(--auth-dark-input);
            accent-color: #6366f1;
        }

        /* Links */
        .auth-link {
            font-size: 0.825rem; font-weight: 500;
            color: #818cf8; text-decoration: none;
            transition: color 0.2s;
        }
        .auth-link:hover { color: #a5b4fc; }

        /* Submit button */
        .auth-submit {
            width: 100%;
            padding: 0.85rem 1.5rem;
            border: none; border-radius: 14px;
            background: var(--auth-gradient);
            color: #fff; font-size: 0.95rem; font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            position: relative; overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 24px rgba(99, 102, 241, 0.35);
            margin-top: 1.75rem;
        }
        .auth-submit::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }
        .auth-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(99, 102, 241, 0.5); }
        .auth-submit:hover::before { transform: translateX(100%); }
        .auth-submit:active { transform: translateY(0) scale(0.99); }
        .auth-submit.is-loading { opacity: 0.75; cursor: wait; }
        .auth-submit .auth-spinner {
            display: inline-block; width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
            border-radius: 50%; animation: spin 0.6s linear infinite;
            margin-right: 0.5rem; vertical-align: middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Footer text */
        .auth-footer {
            text-align: center; margin-top: 1.75rem;
            font-size: 0.85rem; color: #475569;
        }
        .auth-footer a {
            color: #818cf8; font-weight: 600; text-decoration: none;
            transition: color 0.2s;
        }
        .auth-footer a:hover { color: #a5b4fc; }

        /* Alert boxes */
        .auth-alert {
            display: flex; align-items: flex-start; gap: 0.75rem;
            padding: 1rem; border-radius: 14px;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }
        .auth-alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.15);
            color: #fca5a5;
        }
        .auth-alert-success {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
        }
        .auth-alert-info {
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
        }
        .auth-alert-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .auth-alert-icon svg { width: 18px; height: 18px; }

        /* Password toggle */
        .auth-password-wrap { position: relative; }
        .auth-password-wrap .auth-input { padding-right: 2.75rem; }
        .auth-password-toggle {
            position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #475569; padding: 4px;
            transition: color 0.2s;
        }
        .auth-password-toggle:hover { color: #818cf8; }
        .auth-password-toggle svg { width: 18px; height: 18px; }

        /* Responsive */
        @media (max-width: 480px) {
            .auth-card { padding: 1.75rem 1.25rem; border-radius: 20px; }
            .auth-title { font-size: 1.5rem; }
            .auth-oauth-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="auth-body" x-data>
    <!-- Animated Background -->
    <div class="auth-bg">
        <div class="auth-orb auth-orb-1"></div>
        <div class="auth-orb auth-orb-2"></div>
        <div class="auth-orb auth-orb-3"></div>
    </div>
    <div class="auth-grid"></div>

    {{ $slot }}

    <x-ui.toast />
</body>
</html>
