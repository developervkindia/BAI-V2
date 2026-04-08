<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAI — Business Automation &amp; Insights | Run your business. Know it better.</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #0ea5e9;
            --accent: #f43f5e;
            --purple: #8b5cf6;
            --cyan: #06b6d4;
            --teal: #14b8a6;
            --amber: #f59e0b;
            --rose: #f43f5e;
            --emerald: #10b981;
            --dark: #0a0a0f;
            --dark-light: #12121a;
            --dark-card: #16161f;
            --gray: #64748b;
            --gray-light: #94a3b8;
            --light: #f1f5f9;
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            --gradient-2: linear-gradient(135deg, #06b6d4 0%, #6366f1 100%);
            --gradient-3: linear-gradient(135deg, #f43f5e 0%, #8b5cf6 100%);
            --glow-primary: 0 0 60px rgba(99, 102, 241, 0.5);
            --glow-purple: 0 0 60px rgba(139, 92, 246, 0.5);
            --glow-cyan: 0 0 60px rgba(6, 182, 212, 0.5);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark);
            color: var(--white);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Custom Cursor */
        .cursor {
            width: 20px; height: 20px;
            border: 2px solid var(--primary);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.1s ease, width 0.3s, height 0.3s, background 0.3s;
            transform: translate(-50%, -50%);
        }
        .cursor-follower {
            width: 8px; height: 8px;
            background: var(--primary);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9998;
            transition: transform 0.05s ease;
            transform: translate(-50%, -50%);
        }
        .cursor.hover {
            width: 50px; height: 50px;
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--purple);
        }

        /* Animated Background */
        .bg-animated {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none; z-index: 0; overflow: hidden;
        }
        .bg-orb {
            position: absolute; border-radius: 50%;
            filter: blur(80px); opacity: 0.5;
            animation: orbFloat 20s ease-in-out infinite;
        }
        .bg-orb-1 { width: 600px; height: 600px; background: var(--primary); top: -200px; left: -200px; }
        .bg-orb-2 { width: 500px; height: 500px; background: var(--purple); top: 50%; right: -150px; animation-delay: -5s; }
        .bg-orb-3 { width: 400px; height: 400px; background: var(--cyan); bottom: -100px; left: 30%; animation-delay: -10s; }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, 50px) scale(1.1); }
            50% { transform: translate(0, 100px) scale(0.9); }
            75% { transform: translate(-50px, 50px) scale(1.05); }
        }

        /* Particles */
        .particles {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none; z-index: 1;
        }
        .particle {
            position: absolute; width: 4px; height: 4px;
            background: var(--primary); border-radius: 50%; opacity: 0.3;
            animation: particleFloat 15s ease-in-out infinite;
        }
        @keyframes particleFloat {
            0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.5; }
            90% { opacity: 0.5; }
            100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
        }

        /* Grid Pattern */
        .grid-pattern {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
            background-size: 80px 80px;
            pointer-events: none; z-index: 1;
            mask-image: radial-gradient(ellipse at center, black 0%, transparent 70%);
        }

        /* Navigation */
        nav {
            position: fixed; top: 0; left: 0; right: 0;
            z-index: 1000; padding: 1rem 2rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        nav.scrolled {
            background: rgba(10, 10, 15, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 0.75rem 2rem;
        }
        .nav-container {
            max-width: 1400px; margin: 0 auto;
            display: flex; justify-content: space-between; align-items: center;
        }
        .logo {
            display: flex; align-items: center; gap: 0.75rem;
            text-decoration: none; color: var(--white);
            transition: transform 0.3s ease;
        }
        .logo:hover { transform: scale(1.05); }
        .logo-img {
            height: 48px;
            width: auto;
        }
        nav.scrolled .logo-img {
            height: 40px;
        }
        .footer-logo-img {
            height: 44px;
            width: auto;
        }
        .logo-text {
            font-size: 1.4rem; font-weight: 700;
            background: var(--gradient-1);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .logo-tagline {
            font-size: 0.7rem;
            color: var(--gray-light);
            font-weight: 400;
            letter-spacing: 0.05em;
            -webkit-text-fill-color: var(--gray-light);
        }
        .hero-logo-img {
            width: clamp(280px, 40vw, 480px);
            height: auto;
            margin-bottom: 2rem;
        }

        .nav-links {
            display: flex; gap: 2.5rem; list-style: none;
        }
        .nav-links a {
            color: var(--gray-light); text-decoration: none;
            font-weight: 500; font-size: 0.95rem;
            transition: all 0.3s ease; position: relative; padding: 0.5rem 0;
        }
        .nav-links a::before {
            content: ''; position: absolute; bottom: 0; left: 0;
            width: 0; height: 2px;
            background: var(--gradient-1); transition: width 0.3s ease;
        }
        .nav-links a:hover { color: var(--white); }
        .nav-links a:hover::before { width: 100%; }
        .nav-buttons { display: flex; gap: 1rem; align-items: center; }

        /* Buttons */
        .btn {
            padding: 0.85rem 1.75rem; border-radius: 12px;
            font-weight: 600; font-size: 0.95rem; cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none; display: inline-flex;
            align-items: center; gap: 0.5rem;
            border: none; position: relative; overflow: hidden;
        }
        .btn::before {
            content: ''; position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        .btn:hover::before { left: 100%; }
        .btn-ghost {
            background: rgba(255, 255, 255, 0.05); color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        .btn-primary {
            background: var(--gradient-1); color: var(--white);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: var(--glow-primary);
        }
        .btn-large {
            padding: 1.1rem 2.25rem; font-size: 1.05rem; border-radius: 14px;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh; display: flex; align-items: center;
            padding: 8rem 2rem 4rem; position: relative; z-index: 2;
        }
        .hero-container {
            max-width: 1400px; margin: 0 auto; width: 100%;
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 4rem; align-items: center;
        }
        .hero-content { animation: fadeInLeft 1s ease; }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 0.6rem 1.25rem; border-radius: 50px;
            font-size: 0.9rem; color: var(--primary);
            margin-bottom: 2rem;
        }
        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem;
        }
        .hero h1 .gradient-text {
            background: var(--gradient-1);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; position: relative;
        }
        .hero h1 .gradient-text::after {
            content: ''; position: absolute; bottom: -5px; left: 0;
            width: 100%; height: 4px;
            background: var(--gradient-1); border-radius: 2px;
            animation: underlineGrow 1s ease 0.5s backwards;
        }
        @keyframes underlineGrow {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }
        .hero-description {
            font-size: 1.2rem; color: var(--gray-light);
            margin-bottom: 2.5rem; line-height: 1.8;
        }
        .hero-buttons { display: flex; gap: 1rem; flex-wrap: wrap; }

        /* Hero Visual - Product Cards */
        .hero-visual {
            position: relative; perspective: 1000px;
            animation: fadeInRight 1s ease 0.3s backwards;
        }
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .hero-3d-container {
            position: relative; width: 100%; height: 500px;
            transform-style: preserve-3d; transition: transform 0.1s ease;
        }
        .floating-card {
            position: absolute;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px; padding: 1.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
        }
        .floating-card:hover {
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: var(--glow-primary);
        }
        .card-1 {
            top: 5%; left: 5%; width: 200px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
            animation: float1 6s ease-in-out infinite; z-index: 3;
        }
        .card-2 {
            top: 8%; right: 5%; width: 200px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
            animation: float2 7s ease-in-out infinite; z-index: 2;
        }
        .card-3 {
            bottom: 18%; left: 8%; width: 200px;
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.15), rgba(20, 184, 166, 0.05));
            animation: float3 8s ease-in-out infinite; z-index: 1;
        }
        .card-4 {
            bottom: 15%; right: 8%; width: 200px;
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.15), rgba(244, 63, 94, 0.05));
            animation: float4 5.5s ease-in-out infinite; z-index: 2;
        }
        .card-5 {
            top: 50%; left: 50%; transform: translate(-50%, -50%); width: 220px;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(14, 165, 233, 0.05));
            animation: float5 6.5s ease-in-out infinite; z-index: 4;
        }
        @keyframes float1 {
            0%, 100% { transform: translateY(0) rotateX(5deg) rotateY(-5deg); }
            50% { transform: translateY(-20px) rotateX(-5deg) rotateY(5deg); }
        }
        @keyframes float2 {
            0%, 100% { transform: translateY(0) rotateX(-5deg) rotateY(5deg); }
            50% { transform: translateY(-25px) rotateX(5deg) rotateY(-5deg); }
        }
        @keyframes float3 {
            0%, 100% { transform: translateY(0) rotateX(3deg) rotateY(3deg); }
            50% { transform: translateY(-15px) rotateX(-3deg) rotateY(-3deg); }
        }
        @keyframes float4 {
            0%, 100% { transform: translateY(0) rotateX(-3deg) rotateY(-3deg); }
            50% { transform: translateY(-18px) rotateX(3deg) rotateY(3deg); }
        }
        @keyframes float5 {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -55%) scale(1.02); }
        }
        .floating-card .card-icon {
            width: 46px; height: 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; margin-bottom: 0.75rem;
        }
        .card-icon-indigo { background: linear-gradient(135deg, #6366f1, #818cf8); }
        .card-icon-amber { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .card-icon-teal { background: linear-gradient(135deg, #14b8a6, #2dd4bf); }
        .card-icon-rose { background: linear-gradient(135deg, #f43f5e, #fb7185); }
        .card-icon-sky { background: linear-gradient(135deg, #0ea5e9, #38bdf8); }
        .floating-card h4 { font-size: 0.95rem; font-weight: 600; margin-bottom: 0.3rem; }
        .floating-card p { font-size: 0.8rem; color: var(--gray-light); }

        /* Hero Sphere */
        .hero-sphere {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 300px; height: 300px; border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(99, 102, 241, 0.2), transparent 70%);
            box-shadow: inset 0 0 60px rgba(99, 102, 241, 0.2), 0 0 80px rgba(99, 102, 241, 0.1);
            animation: sphereRotate 20s linear infinite; z-index: 0;
        }
        .hero-sphere::before {
            content: ''; position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 90%; height: 90%;
            border: 1px dashed rgba(99, 102, 241, 0.2);
            border-radius: 50%;
            animation: sphereRotate 15s linear infinite reverse;
        }
        .hero-sphere::after {
            content: ''; position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotateX(60deg);
            width: 100%; height: 100%;
            border: 1px dashed rgba(139, 92, 246, 0.2);
            border-radius: 50%;
            animation: sphereRotate 25s linear infinite;
        }
        @keyframes sphereRotate {
            from { transform: translate(-50%, -50%) rotateY(0deg); }
            to { transform: translate(-50%, -50%) rotateY(360deg); }
        }

        /* Stats Section */
        .stats-section { padding: 4rem 2rem; position: relative; z-index: 2; }
        .stats-container {
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem;
        }
        .stat-card {
            background: rgba(22, 22, 31, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px; padding: 2rem; text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative; overflow: hidden;
        }
        .stat-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0;
            height: 3px; background: var(--gradient-1);
            transform: scaleX(0); transition: transform 0.4s ease;
        }
        .stat-card:hover {
            transform: translateY(-10px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        .stat-card:hover::before { transform: scaleX(1); }
        .stat-number {
            font-size: 3rem; font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 0.5rem;
        }
        .stat-label { color: var(--gray-light); font-size: 1rem; }

        /* Shared Section Styles */
        .section-container { max-width: 1400px; margin: 0 auto; }
        .section-header { text-align: center; margin-bottom: 5rem; }
        .section-tag {
            display: inline-block; background: rgba(99, 102, 241, 0.1);
            color: var(--primary); padding: 0.6rem 1.25rem;
            border-radius: 50px; font-size: 0.9rem; font-weight: 600;
            margin-bottom: 1.5rem; border: 1px solid rgba(99, 102, 241, 0.2);
        }
        .section-title {
            font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 800; margin-bottom: 1rem;
        }
        .section-description {
            color: var(--gray-light); font-size: 1.15rem;
            max-width: 600px; margin: 0 auto;
        }

        /* Products Section */
        .products { padding: 8rem 2rem; position: relative; z-index: 2; }
        .products-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
        }
        .product-card {
            background: rgba(22, 22, 31, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px; padding: 2.5rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative; overflow: hidden;
            transform-style: preserve-3d; perspective: 1000px;
        }
        .product-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            opacity: 0; transition: opacity 0.4s ease;
        }
        .product-card:hover {
            transform: translateY(-15px) rotateX(5deg);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
        }
        .product-card:hover::before { opacity: 1; }
        .product-card[data-color="indigo"] { border-color: rgba(99, 102, 241, 0.1); }
        .product-card[data-color="indigo"]:hover { border-color: rgba(99, 102, 241, 0.4); box-shadow: 0 30px 60px rgba(0,0,0,0.4), 0 0 40px rgba(99,102,241,0.2); }
        .product-card[data-color="indigo"]::before { background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, transparent 50%); }
        .product-card[data-color="amber"] { border-color: rgba(245, 158, 11, 0.1); }
        .product-card[data-color="amber"]:hover { border-color: rgba(245, 158, 11, 0.4); box-shadow: 0 30px 60px rgba(0,0,0,0.4), 0 0 40px rgba(245,158,11,0.2); }
        .product-card[data-color="amber"]::before { background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, transparent 50%); }
        .product-card[data-color="teal"] { border-color: rgba(20, 184, 166, 0.1); }
        .product-card[data-color="teal"]:hover { border-color: rgba(20, 184, 166, 0.4); box-shadow: 0 30px 60px rgba(0,0,0,0.4), 0 0 40px rgba(20,184,166,0.2); }
        .product-card[data-color="teal"]::before { background: linear-gradient(135deg, rgba(20, 184, 166, 0.08) 0%, transparent 50%); }
        .product-card[data-color="rose"] { border-color: rgba(244, 63, 94, 0.1); }
        .product-card[data-color="rose"]:hover { border-color: rgba(244, 63, 94, 0.4); box-shadow: 0 30px 60px rgba(0,0,0,0.4), 0 0 40px rgba(244,63,94,0.2); }
        .product-card[data-color="rose"]::before { background: linear-gradient(135deg, rgba(244, 63, 94, 0.08) 0%, transparent 50%); }
        .product-card[data-color="sky"] { border-color: rgba(14, 165, 233, 0.1); }
        .product-card[data-color="sky"]:hover { border-color: rgba(14, 165, 233, 0.4); box-shadow: 0 30px 60px rgba(0,0,0,0.4), 0 0 40px rgba(14,165,233,0.2); }
        .product-card[data-color="sky"]::before { background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, transparent 50%); }

        .product-icon {
            width: 70px; height: 70px; border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem; position: relative;
            transition: transform 0.4s ease;
        }
        .product-card:hover .product-icon { transform: scale(1.1) rotateY(10deg); }
        .product-icon svg { width: 32px; height: 32px; stroke: white; fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
        .product-icon::after {
            content: ''; position: absolute; inset: -5px;
            border-radius: 22px; opacity: 0; z-index: -1;
            filter: blur(15px); transition: opacity 0.4s ease;
        }
        .product-card:hover .product-icon::after { opacity: 0.5; }
        .product-icon.indigo { background: linear-gradient(135deg, #6366f1, #818cf8); }
        .product-icon.indigo::after { background: linear-gradient(135deg, #6366f1, #818cf8); }
        .product-icon.amber { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .product-icon.amber::after { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .product-icon.teal { background: linear-gradient(135deg, #14b8a6, #2dd4bf); }
        .product-icon.teal::after { background: linear-gradient(135deg, #14b8a6, #2dd4bf); }
        .product-icon.rose { background: linear-gradient(135deg, #f43f5e, #fb7185); }
        .product-icon.rose::after { background: linear-gradient(135deg, #f43f5e, #fb7185); }
        .product-icon.sky { background: linear-gradient(135deg, #0ea5e9, #38bdf8); }
        .product-icon.sky::after { background: linear-gradient(135deg, #0ea5e9, #38bdf8); }

        .product-card h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem; position: relative; }
        .product-card .product-tagline { color: var(--gray-light); font-size: 0.95rem; margin-bottom: 1.25rem; line-height: 1.6; position: relative; }
        .product-features-list {
            list-style: none; margin-bottom: 1.5rem; position: relative;
        }
        .product-features-list li {
            display: flex; align-items: center; gap: 0.5rem;
            color: var(--gray-light); font-size: 0.88rem;
            padding: 0.35rem 0;
        }
        .product-features-list li i {
            font-size: 0.7rem; flex-shrink: 0;
        }
        .product-card[data-color="indigo"] .product-features-list li i { color: #6366f1; }
        .product-card[data-color="amber"] .product-features-list li i { color: #f59e0b; }
        .product-card[data-color="teal"] .product-features-list li i { color: #14b8a6; }
        .product-card[data-color="rose"] .product-features-list li i { color: #f43f5e; }
        .product-card[data-color="sky"] .product-features-list li i { color: #0ea5e9; }
        .product-badge {
            display: inline-block; padding: 0.3rem 0.75rem;
            border-radius: 50px; font-size: 0.7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .product-badge-available { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .product-badge-soon { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }

        .product-link {
            display: inline-flex; align-items: center; gap: 0.5rem;
            text-decoration: none; font-weight: 600; font-size: 0.95rem;
            margin-top: 0.5rem; transition: all 0.3s ease; position: relative;
        }
        .product-link i { transition: transform 0.3s ease; }
        .product-link:hover i { transform: translateX(5px); }
        .product-card[data-color="indigo"] .product-link { color: #818cf8; }
        .product-card[data-color="amber"] .product-link { color: #fbbf24; }
        .product-card[data-color="teal"] .product-link { color: #2dd4bf; }
        .product-card[data-color="rose"] .product-link { color: #fb7185; }
        .product-card[data-color="sky"] .product-link { color: #38bdf8; }

        /* Coming Soon cards */
        .products-coming-soon {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 2rem; margin-top: 2rem;
        }
        .coming-soon-card {
            background: rgba(22, 22, 31, 0.4);
            border: 1px dashed rgba(255, 255, 255, 0.08);
            border-radius: 24px; padding: 2rem; text-align: center;
            transition: all 0.4s ease; position: relative; overflow: hidden;
        }
        .coming-soon-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            background: rgba(22, 22, 31, 0.6);
        }
        .coming-soon-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem; opacity: 0.6;
        }
        .coming-soon-icon svg { width: 28px; height: 28px; stroke: white; fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
        .coming-soon-card h4 { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.4rem; opacity: 0.7; }
        .coming-soon-card p { font-size: 0.85rem; color: var(--gray); }

        /* Why BAI Section */
        .why-bai {
            padding: 8rem 2rem; position: relative; z-index: 2; overflow: hidden;
        }
        .why-bai-container {
            max-width: 1400px; margin: 0 auto;
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 6rem; align-items: center;
        }
        .why-bai-content h2 {
            font-size: clamp(2rem, 4vw, 3rem); font-weight: 800;
            margin-bottom: 1.5rem; line-height: 1.2;
        }
        .why-bai-content p {
            color: var(--gray-light); font-size: 1.1rem;
            margin-bottom: 2rem; line-height: 1.8;
        }
        .feature-list { list-style: none; margin-bottom: 2rem; }
        .feature-list li {
            display: flex; align-items: flex-start; gap: 1rem;
            margin-bottom: 1.25rem; padding: 1rem;
            background: rgba(22, 22, 31, 0.6); border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        .feature-list li:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.2);
            transform: translateX(10px);
        }
        .feature-list .check {
            width: 28px; height: 28px; background: var(--gradient-1);
            border-radius: 8px; display: flex; align-items: center;
            justify-content: center; font-size: 0.85rem; flex-shrink: 0;
        }
        .feature-list span { color: var(--gray-light); font-size: 1rem; }

        /* Integrations visual */
        .integrations-visual {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        .integration-tile {
            background: rgba(22, 22, 31, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px; padding: 1.75rem; text-align: center;
            transition: all 0.4s ease;
        }
        .integration-tile:hover {
            transform: translateY(-8px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        .integration-tile i {
            font-size: 2rem; margin-bottom: 0.75rem; display: block;
        }
        .integration-tile span { font-size: 0.85rem; color: var(--gray-light); display: block; }

        /* Pricing Section */
        .pricing {
            padding: 8rem 2rem; position: relative; z-index: 2;
            background: linear-gradient(180deg, transparent, rgba(99, 102, 241, 0.03), transparent);
        }
        .pricing-toggle {
            display: flex; align-items: center; justify-content: center;
            gap: 1rem; margin-bottom: 3rem;
        }
        .pricing-toggle span { color: var(--gray-light); font-size: 0.95rem; font-weight: 500; }
        .pricing-toggle span.active { color: var(--white); }
        .pricing-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 2rem; max-width: 1200px;
            margin-left: auto; margin-right: auto;
        }
        .pricing-card {
            background: rgba(22, 22, 31, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px; padding: 2.5rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative; overflow: hidden;
        }
        .pricing-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0;
            height: 4px; background: var(--gradient-1);
            transform: scaleX(0); transition: transform 0.4s ease;
        }
        .pricing-card:hover {
            transform: translateY(-15px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), var(--glow-primary);
        }
        .pricing-card:hover::before { transform: scaleX(1); }
        .pricing-card.featured {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(139, 92, 246, 0.15));
            border-color: rgba(99, 102, 241, 0.3); transform: scale(1.05);
        }
        .pricing-card.featured::before { transform: scaleX(1); }
        .pricing-badge {
            display: inline-block; background: var(--gradient-1);
            color: var(--white); padding: 0.4rem 1rem;
            border-radius: 50px; font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1.5rem;
        }
        .pricing-plan { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; }
        .pricing-description {
            color: var(--gray-light); font-size: 0.95rem;
            margin-bottom: 2rem; line-height: 1.6;
        }
        .pricing-price { margin-bottom: 2rem; }
        .pricing-amount {
            font-size: 3.5rem; font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; line-height: 1; margin-bottom: 0.5rem;
        }
        .pricing-period { color: var(--gray-light); font-size: 1rem; }
        .pricing-features { list-style: none; margin-bottom: 2.5rem; }
        .pricing-features li {
            display: flex; align-items: flex-start; gap: 0.75rem;
            margin-bottom: 1rem; color: var(--gray-light); font-size: 0.95rem;
        }
        .pricing-features li i {
            color: var(--primary); font-size: 1rem;
            margin-top: 0.2rem; flex-shrink: 0;
        }
        .pricing-button {
            width: 100%; padding: 1rem 2rem; border-radius: 12px;
            font-weight: 600; font-size: 1rem; cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none; display: inline-block;
            text-align: center; border: none;
        }
        .pricing-button-primary {
            background: var(--gradient-1); color: var(--white);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }
        .pricing-button-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: var(--glow-primary);
        }
        .pricing-button-secondary {
            background: rgba(255, 255, 255, 0.05); color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .pricing-button-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Testimonials */
        .testimonials { padding: 8rem 2rem; position: relative; z-index: 2; }
        .testimonials-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;
        }
        .testimonial-card {
            background: rgba(22, 22, 31, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px; padding: 2.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .testimonial-card::before {
            content: '\201C'; position: absolute; top: 1.5rem; right: 2rem;
            font-size: 5rem; font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; opacity: 0.2; line-height: 1;
        }
        .testimonial-card:hover {
            transform: translateY(-10px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        .testimonial-content {
            font-size: 1.05rem; color: var(--gray-light);
            line-height: 1.9; margin-bottom: 2rem; font-style: italic;
        }
        .testimonial-author { display: flex; align-items: center; gap: 1rem; }
        .author-avatar {
            width: 50px; height: 50px;
            background: var(--gradient-1); border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.2rem;
        }
        .author-info h4 { font-weight: 600; margin-bottom: 0.25rem; font-size: 1rem; }
        .author-info p { color: var(--gray); font-size: 0.85rem; }

        /* CTA Section */
        .cta { padding: 8rem 2rem; position: relative; z-index: 2; }
        .cta-card {
            max-width: 1100px; margin: 0 auto;
            background: var(--gradient-1); border-radius: 40px;
            padding: 5rem; text-align: center;
            position: relative; overflow: hidden;
        }
        .cta-card::before {
            content: ''; position: absolute; top: -100%; left: -100%;
            width: 300%; height: 300%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: ctaGlow 10s linear infinite;
        }
        @keyframes ctaGlow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .cta-card h2 {
            font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 800;
            margin-bottom: 1rem; position: relative;
        }
        .cta-card p {
            font-size: 1.25rem; opacity: 0.9;
            margin-bottom: 2.5rem; position: relative;
        }
        .cta-card .btn {
            background: var(--white); color: var(--primary-dark);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
            position: relative; font-size: 1.1rem; padding: 1.25rem 2.5rem;
        }
        .cta-card .btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        /* Footer */
        footer {
            padding: 5rem 2rem 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            position: relative; z-index: 2;
            background: rgba(10, 10, 15, 0.8);
        }
        .footer-container { max-width: 1400px; margin: 0 auto; }
        .footer-grid {
            display: grid; grid-template-columns: 2fr repeat(3, 1fr);
            gap: 4rem; margin-bottom: 4rem;
        }
        .footer-brand .logo { margin-bottom: 1.5rem; }
        .footer-brand p {
            color: var(--gray-light); font-size: 1rem;
            margin-bottom: 1.5rem; line-height: 1.8;
        }
        .social-links { display: flex; gap: 1rem; }
        .social-links a {
            width: 45px; height: 45px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px; display: flex;
            align-items: center; justify-content: center;
            color: var(--gray-light); text-decoration: none;
            transition: all 0.4s ease; font-size: 1.1rem;
        }
        .social-links a:hover {
            background: var(--gradient-1); border-color: transparent;
            color: var(--white); transform: translateY(-5px);
            box-shadow: var(--glow-primary);
        }
        .footer-column h4 { font-weight: 700; margin-bottom: 1.5rem; font-size: 1.1rem; }
        .footer-column ul { list-style: none; }
        .footer-column li { margin-bottom: 1rem; }
        .footer-column a {
            color: var(--gray-light); text-decoration: none; font-size: 1rem;
            transition: all 0.3s ease; display: inline-flex;
            align-items: center; gap: 0.5rem;
        }
        .footer-column a:hover { color: var(--white); transform: translateX(5px); }
        .footer-bottom {
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--gray); font-size: 0.95rem;
        }

        /* Scroll Animations */
        .reveal {
            opacity: 0; transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .reveal.active { opacity: 1; transform: translateY(0); }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none; background: none; border: none;
            color: var(--white); font-size: 1.5rem;
            cursor: pointer; z-index: 1001; padding: 0.5rem;
            transition: transform 0.3s ease;
        }
        .mobile-menu-btn:hover { transform: scale(1.1); }
        .mobile-menu {
            position: fixed; top: 0; right: -100%;
            width: 100%; max-width: 320px; height: 100vh;
            background: rgba(10, 10, 15, 0.98);
            backdrop-filter: blur(20px); z-index: 2000;
            transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 5rem 2rem 2rem; overflow-y: auto;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
        }
        .mobile-menu.active { right: 0; }
        .mobile-menu-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.8); z-index: 1999;
            opacity: 0; visibility: hidden; transition: all 0.3s ease;
        }
        .mobile-menu-overlay.active { opacity: 1; visibility: visible; }
        .mobile-menu-close {
            position: absolute; top: 1.5rem; right: 1.5rem;
            background: none; border: none; color: var(--white);
            font-size: 1.5rem; cursor: pointer; padding: 0.5rem;
        }
        .mobile-nav-links { list-style: none; margin-top: 2rem; }
        .mobile-nav-links li { margin-bottom: 1rem; }
        .mobile-nav-links a {
            display: block; color: var(--gray-light); text-decoration: none;
            font-size: 1.1rem; padding: 1rem; border-radius: 12px;
            transition: all 0.3s ease; border: 1px solid transparent;
        }
        .mobile-nav-links a:hover, .mobile-nav-links a.active {
            color: var(--white);
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.3);
        }
        .mobile-nav-buttons {
            margin-top: 2rem; display: flex; flex-direction: column; gap: 1rem;
        }
        .mobile-nav-buttons .btn { width: 100%; justify-content: center; }

        /* Responsive */
        @media (max-width: 1200px) {
            nav { padding: 1rem 1.5rem; }
            .products-grid, .testimonials-grid { grid-template-columns: repeat(2, 1fr); }
            .products-coming-soon { grid-template-columns: repeat(2, 1fr); }
            .pricing-grid { grid-template-columns: 1fr; max-width: 500px; }
            .pricing-card.featured { transform: scale(1); }
            .hero { padding: 7rem 1.5rem 3rem; }
            .products, .pricing, .testimonials, .cta, .why-bai { padding: 6rem 1.5rem; }
        }
        @media (max-width: 1024px) {
            .hero-container, .why-bai-container { grid-template-columns: 1fr; text-align: center; gap: 3rem; }
            .hero-visual { order: -1; }
            .hero-3d-container { height: 350px; }
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
            .stats-container { grid-template-columns: repeat(2, 1fr); }
            .nav-buttons { gap: 0.75rem; }
            .nav-buttons .btn { padding: 0.75rem 1.25rem; font-size: 0.9rem; }
            .integrations-visual { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            nav { padding: 1rem; }
            .nav-container { padding: 0; }
            .logo-text { font-size: 1.2rem; }
            .logo-img { height: 32px; }
            .nav-links, .nav-buttons { display: none; }
            .mobile-menu-btn { display: block; }
            .hero { padding: 6rem 1rem 2rem; min-height: auto; }
            .hero-container { gap: 2rem; }
            .hero-logo-img { width: clamp(220px, 60vw, 320px); }
            .hero h1 { font-size: clamp(2rem, 8vw, 3rem); margin-bottom: 1rem; }
            .hero-description { font-size: 1rem; margin-bottom: 2rem; }
            .hero-buttons { flex-direction: column; width: 100%; gap: 0.75rem; }
            .hero-buttons .btn { width: 100%; justify-content: center; }
            .hero-3d-container { height: 280px; }
            .floating-card { padding: 1rem; }
            .floating-card.card-1, .floating-card.card-2, .floating-card.card-3, .floating-card.card-4 { width: 140px; }
            .floating-card.card-5 { width: 160px; }
            .floating-card h4 { font-size: 0.85rem; }
            .floating-card p { font-size: 0.75rem; }
            .products-grid, .testimonials-grid { grid-template-columns: 1fr; gap: 1.5rem; }
            .products-coming-soon { grid-template-columns: 1fr; }
            .stats-container { grid-template-columns: 1fr; gap: 1rem; }
            .stat-card { padding: 1.5rem; }
            .stat-number { font-size: 2.5rem; }
            .products, .pricing, .testimonials, .cta, .why-bai { padding: 4rem 1rem; }
            .section-header { margin-bottom: 3rem; }
            .section-title { font-size: clamp(1.75rem, 6vw, 2.5rem); }
            .section-description { font-size: 1rem; }
            .product-card { padding: 2rem; }
            .product-icon { width: 60px; height: 60px; }
            .pricing-grid { gap: 1.5rem; }
            .pricing-card { padding: 2rem; }
            .pricing-amount { font-size: 2.5rem; }
            .why-bai-container { gap: 3rem; }
            .integrations-visual { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
            .footer-grid { grid-template-columns: 1fr; gap: 2rem; }
            .footer-bottom { flex-direction: column; gap: 1rem; text-align: center; }
            .cta-card { padding: 3rem 1.5rem; border-radius: 24px; }
            .cta-card h2 { font-size: clamp(1.75rem, 6vw, 2.5rem); }
            .cta-card p { font-size: 1rem; }
            .cursor, .cursor-follower { display: none; }
            .feature-list li:hover { transform: none; }
            .feature-list li { padding: 0.75rem; }
            .hero-sphere { width: 250px; height: 250px; }
            .bg-orb-1 { width: 400px; height: 400px; }
            .bg-orb-2 { width: 350px; height: 350px; }
            .bg-orb-3 { width: 300px; height: 300px; }
        }
        @media (max-width: 480px) {
            .hero { padding: 5rem 0.75rem 1.5rem; }
            .hero-logo-img { width: clamp(180px, 55vw, 260px); margin-bottom: 1.25rem; }
            .hero h1 { font-size: clamp(1.75rem, 10vw, 2.25rem); line-height: 1.2; }
            .hero-description { font-size: 0.95rem; }
            .hero-badge { font-size: 0.8rem; padding: 0.5rem 1rem; }
            .products, .pricing, .testimonials, .cta, .why-bai { padding: 3rem 0.75rem; }
            .section-header { margin-bottom: 2rem; }
            .product-card, .pricing-card, .testimonial-card { padding: 1.5rem; }
            .stat-card { padding: 1.25rem; }
            .stat-number { font-size: 2rem; }
            .pricing-amount { font-size: 2rem; }
            .hero-3d-container { height: 220px; }
            .floating-card.card-1, .floating-card.card-2, .floating-card.card-3, .floating-card.card-4 { width: 110px; }
            .floating-card.card-5 { width: 140px; }
            .floating-card .card-icon { width: 36px; height: 36px; font-size: 0.9rem; }
            .integrations-visual { grid-template-columns: 1fr 1fr; }
            .integration-tile { padding: 1.25rem; }
            .mobile-menu { max-width: 100%; }
        }
    </style>
</head>
<body>
    <!-- Custom Cursor -->
    <div class="cursor" id="cursor"></div>
    <div class="cursor-follower" id="cursor-follower"></div>

    <!-- Animated Background -->
    <div class="bg-animated">
        <div class="bg-orb bg-orb-1"></div>
        <div class="bg-orb bg-orb-2"></div>
        <div class="bg-orb bg-orb-3"></div>
    </div>

    <!-- Particles -->
    <div class="particles" id="particles"></div>

    <!-- Grid Pattern -->
    <div class="grid-pattern"></div>

    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <a href="{{ route('home') }}" class="logo">
                <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="logo-img">
            </a>
            <ul class="nav-links">
                <li><a href="#products">Products</a></li>
                <li><a href="#features">Why BAI</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <li><a href="#testimonials">Testimonials</a></li>
            </ul>
            <div class="nav-buttons">
                <a href="{{ route('login') }}" class="btn btn-ghost">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Start Free</a>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-menu-close" id="mobileMenuClose">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
        <ul class="mobile-nav-links">
            <li><a href="#products">Products</a></li>
            <li><a href="#features">Why BAI</a></li>
            <li><a href="#pricing">Pricing</a></li>
            <li><a href="#testimonials">Testimonials</a></li>
        </ul>
        <div class="mobile-nav-buttons">
            <a href="{{ route('login') }}" class="btn btn-ghost">Sign In</a>
            <a href="{{ route('register') }}" class="btn btn-primary">Start Free</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <img src="{{ asset('images/bai-logo.svg') }}" alt="BAI — Business Automation & Insights" class="hero-logo-img">
                <div class="hero-badge">
                    <i class="fas fa-bolt" aria-hidden="true"></i>
                    <span>One platform. Every team. Total clarity.</span>
                </div>
                <h1>
                    Run your business.<br>
                    <span class="gradient-text">Know it better.</span>
                </h1>
                <p class="hero-description">
                    BAI brings boards, projects, HR, knowledge, and task management into one unified platform &mdash;
                    so every team stays aligned and every decision is backed by insight.
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket" aria-hidden="true"></i>
                        Get Started Free
                    </a>
                    <a href="#products" class="btn btn-ghost btn-large">
                        <i class="fas fa-th-large" aria-hidden="true"></i>
                        Explore Products
                    </a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-3d-container" id="hero3d">
                    <div class="hero-sphere"></div>
                    <div class="floating-card card-1">
                        <div class="card-icon card-icon-indigo"><i class="fas fa-columns" aria-hidden="true"></i></div>
                        <h4>BAI Board</h4>
                        <p>Kanban & collaboration</p>
                    </div>
                    <div class="floating-card card-2">
                        <div class="card-icon card-icon-amber"><i class="fas fa-project-diagram" aria-hidden="true"></i></div>
                        <h4>BAI Projects</h4>
                        <p>Sprints & timelines</p>
                    </div>
                    <div class="floating-card card-3">
                        <div class="card-icon card-icon-teal"><i class="fas fa-bullseye" aria-hidden="true"></i></div>
                        <h4>Opportunity</h4>
                        <p>Goals & portfolios</p>
                    </div>
                    <div class="floating-card card-4">
                        <div class="card-icon card-icon-rose"><i class="fas fa-users" aria-hidden="true"></i></div>
                        <h4>BAI HR</h4>
                        <p>People & payroll</p>
                    </div>
                    <div class="floating-card card-5">
                        <div class="card-icon card-icon-sky"><i class="fas fa-book-open" aria-hidden="true"></i></div>
                        <h4>Knowledge Base</h4>
                        <p>Wiki & documentation</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-card reveal">
                <div class="stat-number" data-target="5">0</div>
                <div class="stat-label">Products in One Platform</div>
            </div>
            <div class="stat-card reveal">
                <div class="stat-number" data-target="50">0</div>
                <div class="stat-label">Built-in Features</div>
            </div>
            <div class="stat-card reveal">
                <div class="stat-number" data-target="3">0</div>
                <div class="stat-label">Flexible Plan Tiers</div>
            </div>
            <div class="stat-card reveal">
                <div class="stat-number" data-target="100" data-suffix="%">0</div>
                <div class="stat-label">Free Tier Available</div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products" id="products">
        <div class="section-container">
            <div class="section-header reveal">
                <span class="section-tag">The BAI Ecosystem</span>
                <h2 class="section-title">Everything Your Team Needs</h2>
                <p class="section-description">
                    Five powerful products, one seamless experience. Pick what you need now &mdash; add more as you grow.
                </p>
            </div>
            <div class="products-grid">
                <!-- BAI Board -->
                <div class="product-card reveal" data-color="indigo">
                    <span class="product-badge product-badge-available">Available</span>
                    <div class="product-icon indigo">
                        <svg viewBox="0 0 24 24"><path d="M9 3H4a1 1 0 00-1 1v5a1 1 0 001 1h5a1 1 0 001-1V4a1 1 0 00-1-1zM20 3h-5a1 1 0 00-1 1v5a1 1 0 001 1h5a1 1 0 001-1V4a1 1 0 00-1-1zM9 14H4a1 1 0 00-1 1v5a1 1 0 001 1h5a1 1 0 001-1v-5a1 1 0 00-1-1zM20 14h-5a1 1 0 00-1 1v5a1 1 0 001 1h5a1 1 0 001-1v-5a1 1 0 00-1-1z"/></svg>
                    </div>
                    <h3>BAI Board</h3>
                    <p class="product-tagline">Visual kanban boards & real-time team collaboration</p>
                    <ul class="product-features-list">
                        <li><i class="fas fa-circle"></i> Drag-and-drop kanban boards</li>
                        <li><i class="fas fa-circle"></i> Calendar, timeline & dashboard views</li>
                        <li><i class="fas fa-circle"></i> Real-time chat & comments</li>
                        <li><i class="fas fa-circle"></i> Custom fields & automations</li>
                    </ul>
                    <a href="{{ route('register') }}" class="product-link">Try Board free <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>

                <!-- BAI Projects -->
                <div class="product-card reveal" data-color="amber">
                    <span class="product-badge product-badge-available">Available</span>
                    <div class="product-icon amber">
                        <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <h3>BAI Projects</h3>
                    <p class="product-tagline">End-to-end project management with sprints & budgets</p>
                    <ul class="product-features-list">
                        <li><i class="fas fa-circle"></i> Sprint planning & milestones</li>
                        <li><i class="fas fa-circle"></i> Time tracking & timesheets</li>
                        <li><i class="fas fa-circle"></i> Budget management & billing</li>
                        <li><i class="fas fa-circle"></i> Resource allocation & workload</li>
                    </ul>
                    <a href="{{ route('register') }}" class="product-link">Try Projects free <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>

                <!-- Opportunity -->
                <div class="product-card reveal" data-color="teal">
                    <span class="product-badge product-badge-available">Available</span>
                    <div class="product-icon teal">
                        <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3>Opportunity</h3>
                    <p class="product-tagline">Task management, goals & portfolio tracking</p>
                    <ul class="product-features-list">
                        <li><i class="fas fa-circle"></i> Goals & portfolio management</li>
                        <li><i class="fas fa-circle"></i> Forms & task approvals</li>
                        <li><i class="fas fa-circle"></i> Automations & templates</li>
                        <li><i class="fas fa-circle"></i> Saved views & reporting</li>
                    </ul>
                    <a href="{{ route('register') }}" class="product-link">Try Opportunity free <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>

                <!-- BAI HR -->
                <div class="product-card reveal" data-color="rose">
                    <span class="product-badge product-badge-available">Available</span>
                    <div class="product-icon rose">
                        <svg viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3>BAI HR</h3>
                    <p class="product-tagline">Complete HR management & employee engagement</p>
                    <ul class="product-features-list">
                        <li><i class="fas fa-circle"></i> Attendance & leave management</li>
                        <li><i class="fas fa-circle"></i> Payroll & expense tracking</li>
                        <li><i class="fas fa-circle"></i> Performance reviews & surveys</li>
                        <li><i class="fas fa-circle"></i> Recruitment & onboarding</li>
                    </ul>
                    <a href="{{ route('register') }}" class="product-link">Try HR free <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>

                <!-- Knowledge Base -->
                <div class="product-card reveal" data-color="sky">
                    <span class="product-badge product-badge-available">Available</span>
                    <div class="product-icon sky">
                        <svg viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3>Knowledge Base</h3>
                    <p class="product-tagline">Internal wiki, SOPs & team documentation</p>
                    <ul class="product-features-list">
                        <li><i class="fas fa-circle"></i> Rich article editor & categories</li>
                        <li><i class="fas fa-circle"></i> Full-text search & attachments</li>
                        <li><i class="fas fa-circle"></i> Revision history & restore</li>
                        <li><i class="fas fa-circle"></i> Team permissions & sharing</li>
                    </ul>
                    <a href="{{ route('register') }}" class="product-link">Try Knowledge Base free <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>

            <!-- Coming Soon Products -->
            <div class="products-coming-soon">
                <div class="coming-soon-card reveal">
                    <div class="coming-soon-icon" style="background: linear-gradient(135deg, rgba(14,165,233,0.3), rgba(14,165,233,0.1));">
                        <svg viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="product-badge product-badge-soon" style="margin-bottom: 0.75rem;">Coming Soon</span>
                    <h4>BAI Docs</h4>
                    <p>Collaborative documents & real-time editing</p>
                </div>
                <div class="coming-soon-card reveal">
                    <div class="coming-soon-icon" style="background: linear-gradient(135deg, rgba(124,58,237,0.3), rgba(124,58,237,0.1));">
                        <svg viewBox="0 0 24 24"><path d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <span class="product-badge product-badge-soon" style="margin-bottom: 0.75rem;">Coming Soon</span>
                    <h4>BAI Desk</h4>
                    <p>Customer support & smart ticketing</p>
                </div>
                <div class="coming-soon-card reveal">
                    <div class="coming-soon-icon" style="background: linear-gradient(135deg, rgba(16,185,129,0.3), rgba(16,185,129,0.1));">
                        <svg viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="product-badge product-badge-soon" style="margin-bottom: 0.75rem;">Coming Soon</span>
                    <h4>BAI CRM</h4>
                    <p>Contacts, pipelines & revenue automation</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why BAI Section -->
    <section class="why-bai" id="features">
        <div class="section-container">
            <div class="why-bai-container">
                <div class="why-bai-content reveal">
                    <span class="section-tag">Why BAI</span>
                    <h2>One Platform,<br>Zero Fragmentation</h2>
                    <p>Stop switching between dozens of disconnected tools. BAI gives every department
                       their own workspace while keeping your entire organization connected.</p>
                    <ul class="feature-list">
                        <li>
                            <span class="check"><i class="fas fa-check" aria-hidden="true"></i></span>
                            <span>Organization-based multi-tenancy with role-based access control</span>
                        </li>
                        <li>
                            <span class="check"><i class="fas fa-check" aria-hidden="true"></i></span>
                            <span>Per-product subscriptions &mdash; only pay for what you use</span>
                        </li>
                        <li>
                            <span class="check"><i class="fas fa-check" aria-hidden="true"></i></span>
                            <span>Real-time collaboration with live updates & notifications</span>
                        </li>
                        <li>
                            <span class="check"><i class="fas fa-check" aria-hidden="true"></i></span>
                            <span>Generous free tier for every product &mdash; no credit card needed</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-large">
                        Start Building for Free
                    </a>
                </div>
                <div class="integrations-visual reveal">
                    <div class="integration-tile">
                        <i class="fas fa-columns" style="background: linear-gradient(135deg, #6366f1, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <span>Boards</span>
                    </div>
                    <div class="integration-tile">
                        <i class="fas fa-tasks" style="background: linear-gradient(135deg, #f59e0b, #fbbf24); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <span>Sprints</span>
                    </div>
                    <div class="integration-tile">
                        <i class="fas fa-clock" style="background: linear-gradient(135deg, #f59e0b, #fbbf24); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <span>Timesheets</span>
                    </div>
                    <div class="integration-tile">
                        <i class="fas fa-bullseye" style="background: linear-gradient(135deg, #14b8a6, #2dd4bf); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <span>Goals</span>
                    </div>
                    <div class="integration-tile">
                        <i class="fas fa-id-badge" style="background: linear-gradient(135deg, #f43f5e, #fb7185); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <span>Payroll</span>
                    </div>
                    <div class="integration-tile">
                        <i class="fas fa-chart-bar" style="background: linear-gradient(135deg, #f43f5e, #fb7185); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <span>Reviews</span>
                    </div>
                    <div class="integration-tile">
                        <i class="fas fa-book" style="background: linear-gradient(135deg, #0ea5e9, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <span>Wiki</span>
                    </div>
                    <div class="integration-tile">
                        <i class="fas fa-shield-alt" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <span>Permissions</span>
                    </div>
                    <div class="integration-tile">
                        <i class="fas fa-bolt" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        <span>Automations</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="section-container">
            <div class="section-header reveal">
                <span class="section-tag">Simple Pricing</span>
                <h2 class="section-title">Start Free, Scale as You Grow</h2>
                <p class="section-description">
                    Every product includes a generous free tier. Upgrade individual products when you need more power.
                </p>
            </div>
            <div class="pricing-grid">
                <!-- Free Plan -->
                <div class="pricing-card reveal">
                    <div class="pricing-plan">Free</div>
                    <p class="pricing-description">Perfect for small teams getting started with any BAI product</p>
                    <div class="pricing-price">
                        <div class="pricing-amount">$0</div>
                        <div class="pricing-period">forever</div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> <span>Up to 5 boards &amp; 3 projects</span></li>
                        <li><i class="fas fa-check"></i> <span>Up to 10 team members</span></li>
                        <li><i class="fas fa-check"></i> <span>25 employees in HR module</span></li>
                        <li><i class="fas fa-check"></i> <span>100 knowledge base articles</span></li>
                        <li><i class="fas fa-check"></i> <span>Real-time chat &amp; calendar views</span></li>
                        <li><i class="fas fa-check"></i> <span>Attendance &amp; leave tracking</span></li>
                    </ul>
                    <a href="{{ route('register') }}" class="pricing-button pricing-button-secondary">Get Started Free</a>
                </div>

                <!-- Pro Plan -->
                <div class="pricing-card featured reveal">
                    <div class="pricing-badge">Most Popular</div>
                    <div class="pricing-plan">Pro</div>
                    <p class="pricing-description">For growing teams that need advanced features &amp; higher limits</p>
                    <div class="pricing-price">
                        <div class="pricing-amount">Pro</div>
                        <div class="pricing-period">per product / month</div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> <span>Up to 50 boards &amp; projects</span></li>
                        <li><i class="fas fa-check"></i> <span>Up to 100 team members</span></li>
                        <li><i class="fas fa-check"></i> <span>Custom fields &amp; automations</span></li>
                        <li><i class="fas fa-check"></i> <span>Sprints, timesheets &amp; billing</span></li>
                        <li><i class="fas fa-check"></i> <span>Payroll, performance &amp; recruitment</span></li>
                        <li><i class="fas fa-check"></i> <span>Goals, portfolios &amp; reporting</span></li>
                        <li><i class="fas fa-check"></i> <span>5,000 articles &amp; 5 GB storage</span></li>
                        <li><i class="fas fa-check"></i> <span>Templates &amp; approval workflows</span></li>
                    </ul>
                    <a href="{{ route('register') }}" class="pricing-button pricing-button-primary">Upgrade to Pro</a>
                </div>

                <!-- Enterprise Plan -->
                <div class="pricing-card reveal">
                    <div class="pricing-plan">Enterprise</div>
                    <p class="pricing-description">Unlimited everything for large organizations with custom needs</p>
                    <div class="pricing-price">
                        <div class="pricing-amount">Custom</div>
                        <div class="pricing-period">contact us</div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> <span>Unlimited boards, projects &amp; members</span></li>
                        <li><i class="fas fa-check"></i> <span>Unlimited employees &amp; articles</span></li>
                        <li><i class="fas fa-check"></i> <span>Unlimited storage</span></li>
                        <li><i class="fas fa-check"></i> <span>All Pro features included</span></li>
                        <li><i class="fas fa-check"></i> <span>Priority support &amp; SLA</span></li>
                        <li><i class="fas fa-check"></i> <span>Dedicated account manager</span></li>
                        <li><i class="fas fa-check"></i> <span>Custom integrations &amp; API access</span></li>
                        <li><i class="fas fa-check"></i> <span>On-premise deployment option</span></li>
                    </ul>
                    <a href="{{ route('register') }}" class="pricing-button pricing-button-secondary">Contact Sales</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="section-container">
            <div class="section-header reveal">
                <span class="section-tag">Testimonials</span>
                <h2 class="section-title">Trusted by Teams Everywhere</h2>
                <p class="section-description">
                    Hear from organizations that brought their teams together with BAI.
                </p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card reveal">
                    <p class="testimonial-content">
                        "We replaced four separate tools with BAI. Projects, boards, and HR in one place
                        saved us hours of context-switching every week. The free tier alone was better than what we were paying for."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">RK</div>
                        <div class="author-info">
                            <h4>Rahul Kumar</h4>
                            <p>CTO, Digital Ventures</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card reveal">
                    <p class="testimonial-content">
                        "BAI HR transformed how we manage our 200-person team. Attendance, payroll,
                        and performance reviews all in one dashboard. The onboarding flow alone saved our HR team 15 hours a month."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">PS</div>
                        <div class="author-info">
                            <h4>Priya Sharma</h4>
                            <p>Head of People, ScaleUp Inc.</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card reveal">
                    <p class="testimonial-content">
                        "The Knowledge Base and Opportunity modules are exactly what we needed. Our docs are finally organized,
                        and the goal-tracking gives leadership the visibility they've been asking for."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">AM</div>
                        <div class="author-info">
                            <h4>Amit Mehta</h4>
                            <p>COO, Innovate Labs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-card reveal">
            <h2>Ready to Unify Your Workspace?</h2>
            <p>Start with any product for free. No credit card required.</p>
            <a href="{{ route('register') }}" class="btn btn-large">
                <i class="fas fa-rocket" aria-hidden="true"></i>
                Get Started Free
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="{{ route('home') }}" class="logo">
                        <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="footer-logo-img">
                    </a>
                    <p>Business Automation &amp; Insights &mdash; boards, projects, HR, knowledge, and more in one platform so you can run your business and know it better.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h4>Products</h4>
                    <ul>
                        <li><a href="{{ route('dashboard') }}">BAI Board</a></li>
                        <li><a href="{{ route('projects.index') }}">BAI Projects</a></li>
                        <li><a href="{{ route('opportunity.home') }}">Opportunity</a></li>
                        <li><a href="{{ route('hr.dashboard') }}">BAI HR</a></li>
                        <li><a href="{{ route('knowledge.index') }}">Knowledge Base</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Account</h4>
                    <ul>
                        <li><a href="{{ route('login') }}">Sign In</a></li>
                        <li><a href="{{ route('register') }}">Get Started</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} BAI &mdash; Business Automation &amp; Insights. All rights reserved.</p>
                <p>Crafted with <i class="fas fa-heart" style="color: #f43f5e;" aria-hidden="true"></i> for modern teams</p>
            </div>
        </div>
    </footer>

    <script>
        // Custom Cursor
        const cursor = document.getElementById('cursor');
        const cursorFollower = document.getElementById('cursor-follower');
        let mouseX = 0, mouseY = 0;
        let followerX = 0, followerY = 0;

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
            cursor.style.left = mouseX + 'px';
            cursor.style.top = mouseY + 'px';
        });

        function animateFollower() {
            followerX += (mouseX - followerX) * 0.15;
            followerY += (mouseY - followerY) * 0.15;
            cursorFollower.style.left = followerX + 'px';
            cursorFollower.style.top = followerY + 'px';
            requestAnimationFrame(animateFollower);
        }
        animateFollower();

        const hoverElements = document.querySelectorAll('a, button, .product-card, .integration-tile, .testimonial-card, .stat-card, .coming-soon-card');
        hoverElements.forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
        });

        // Generate Particles
        const particlesContainer = document.getElementById('particles');
        for (let i = 0; i < 50; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 15 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            particle.style.opacity = Math.random() * 0.5 + 0.1;
            particle.style.width = (Math.random() * 4 + 2) + 'px';
            particle.style.height = particle.style.width;
            const colors = ['#6366f1', '#8b5cf6', '#06b6d4', '#f43f5e', '#f59e0b', '#14b8a6'];
            particle.style.background = colors[Math.floor(Math.random() * colors.length)];
            particlesContainer.appendChild(particle);
        }

        // 3D Hero Effect
        const hero3d = document.getElementById('hero3d');
        document.addEventListener('mousemove', (e) => {
            if (!hero3d) return;
            const rect = hero3d.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            const rotateX = (e.clientY - centerY) / 30;
            const rotateY = (e.clientX - centerX) / 30;
            hero3d.style.transform = `rotateX(${-rotateX}deg) rotateY(${rotateY}deg)`;
        });

        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });

        // Scroll reveal animations
        const reveals = document.querySelectorAll('.reveal');
        function revealOnScroll() {
            reveals.forEach(el => {
                const windowHeight = window.innerHeight;
                const elementTop = el.getBoundingClientRect().top;
                if (elementTop < windowHeight - 150) {
                    el.classList.add('active');
                }
            });
        }
        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll();

        // Counter animation
        const counters = document.querySelectorAll('.stat-number');
        let counterAnimated = false;

        function animateCounters() {
            if (counterAnimated) return;
            counters.forEach(counter => {
                const rect = counter.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    counterAnimated = true;
                    const target = parseInt(counter.getAttribute('data-target'));
                    const suffix = counter.getAttribute('data-suffix') || '+';
                    const increment = Math.max(target / 80, 0.1);
                    let current = 0;
                    const updateCounter = () => {
                        current += increment;
                        if (current < target) {
                            counter.textContent = Math.ceil(current) + suffix;
                            requestAnimationFrame(updateCounter);
                        } else {
                            counter.textContent = target + suffix;
                        }
                    };
                    updateCounter();
                }
            });
        }
        window.addEventListener('scroll', animateCounters);
        animateCounters();

        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        const mobileMenuClose = document.getElementById('mobileMenuClose');

        function openMobileMenu() {
            mobileMenu.classList.add('active');
            mobileMenuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeMobileMenu() {
            mobileMenu.classList.remove('active');
            mobileMenuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        mobileMenuBtn?.addEventListener('click', openMobileMenu);
        mobileMenuClose?.addEventListener('click', closeMobileMenu);
        mobileMenuOverlay?.addEventListener('click', closeMobileMenu);

        document.querySelectorAll('.mobile-nav-links a').forEach(link => {
            link.addEventListener('click', () => closeMobileMenu());
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#' || !href) return;
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    window.scrollTo({ top: target.offsetTop - 80, behavior: 'smooth' });
                }
            });
        });

        // Tilt effect for product cards
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 10;
                const rotateY = (centerX - x) / 10;
                card.style.transform = `translateY(-15px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    </script>
</body>
</html>
