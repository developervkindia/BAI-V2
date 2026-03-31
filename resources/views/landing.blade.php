<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAI — Business Automation &amp; Insight | Run your business. Know it better.</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark);
            color: var(--white);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Custom Cursor */
        .cursor {
            width: 20px;
            height: 20px;
            border: 2px solid var(--primary);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.1s ease, width 0.3s, height 0.3s, background 0.3s;
            transform: translate(-50%, -50%);
        }

        .cursor-follower {
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9998;
            transition: transform 0.05s ease;
            transform: translate(-50%, -50%);
        }

        .cursor.hover {
            width: 50px;
            height: 50px;
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--purple);
        }

        /* Animated Background */
        .bg-animated {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.5;
            animation: orbFloat 20s ease-in-out infinite;
        }

        .bg-orb-1 {
            width: 600px;
            height: 600px;
            background: var(--primary);
            top: -200px;
            left: -200px;
            animation-delay: 0s;
        }

        .bg-orb-2 {
            width: 500px;
            height: 500px;
            background: var(--purple);
            top: 50%;
            right: -150px;
            animation-delay: -5s;
        }

        .bg-orb-3 {
            width: 400px;
            height: 400px;
            background: var(--cyan);
            bottom: -100px;
            left: 30%;
            animation-delay: -10s;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, 50px) scale(1.1); }
            50% { transform: translate(0, 100px) scale(0.9); }
            75% { transform: translate(-50px, 50px) scale(1.05); }
        }

        /* Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.3;
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
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
            background-size: 80px 80px;
            pointer-events: none;
            z-index: 1;
            mask-image: radial-gradient(ellipse at center, black 0%, transparent 70%);
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 2rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        nav.scrolled {
            background: rgba(10, 10, 15, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 0.75rem 2rem;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--white);
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #312E81, #4338CA, #7C3AED);
        }

        .logo-icon::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
            transform: rotate(45deg);
            animation: logoShine 3s ease-in-out infinite;
        }

        @keyframes logoShine {
            0%, 100% { transform: translateX(-100%) rotate(45deg); }
            50% { transform: translateX(100%) rotate(45deg); }
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 700;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--gray-light);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 0;
        }

        .nav-links a::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient-1);
            transition: width 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--white);
        }

        .nav-links a:hover::before {
            width: 100%;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.85rem 1.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--gradient-1);
            color: var(--white);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: var(--glow-primary);
        }

        .btn-large {
            padding: 1.1rem 2.25rem;
            font-size: 1.05rem;
            border-radius: 14px;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 8rem 2rem 4rem;
            position: relative;
            z-index: 2;
        }

        .hero-container {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-content {
            animation: fadeInLeft 1s ease;
        }

        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 0.6rem 1.25rem;
            border-radius: 50px;
            font-size: 0.9rem;
            color: var(--primary);
            margin-bottom: 2rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
            50% { box-shadow: 0 0 0 10px rgba(99, 102, 241, 0); }
        }

        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .hero h1 .gradient-text {
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }

        .hero h1 .gradient-text::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-1);
            border-radius: 2px;
            animation: underlineGrow 1s ease 0.5s backwards;
        }

        @keyframes underlineGrow {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        .hero-description {
            font-size: 1.2rem;
            color: var(--gray-light);
            margin-bottom: 2.5rem;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* 3D Hero Visual */
        .hero-visual {
            position: relative;
            perspective: 1000px;
            animation: fadeInRight 1s ease 0.3s backwards;
        }

        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .hero-3d-container {
            position: relative;
            width: 100%;
            height: 500px;
            transform-style: preserve-3d;
            transition: transform 0.1s ease;
        }

        .floating-card {
            position: absolute;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
        }

        .floating-card:hover {
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: var(--glow-primary);
        }

        .card-1 {
            top: 10%;
            left: 10%;
            width: 200px;
            animation: float1 6s ease-in-out infinite;
            z-index: 3;
        }

        .card-2 {
            top: 30%;
            right: 5%;
            width: 180px;
            animation: float2 7s ease-in-out infinite;
            z-index: 2;
        }

        .card-3 {
            bottom: 15%;
            left: 20%;
            width: 220px;
            animation: float3 8s ease-in-out infinite;
            z-index: 1;
        }

        .card-4 {
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 280px;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(99, 102, 241, 0.15));
            animation: float4 5s ease-in-out infinite;
            z-index: 4;
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
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -55%) scale(1.02); }
        }

        .floating-card .card-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        .floating-card h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .floating-card p {
            font-size: 0.85rem;
            color: var(--gray-light);
        }

        /* 3D Sphere */
        .hero-sphere {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(99, 102, 241, 0.3), transparent 70%);
            box-shadow: inset 0 0 60px rgba(99, 102, 241, 0.3), 0 0 100px rgba(99, 102, 241, 0.2);
            animation: sphereRotate 20s linear infinite;
            z-index: 0;
        }

        .hero-sphere::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            height: 90%;
            border: 1px dashed rgba(99, 102, 241, 0.3);
            border-radius: 50%;
            animation: sphereRotate 15s linear infinite reverse;
        }

        .hero-sphere::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotateX(60deg);
            width: 100%;
            height: 100%;
            border: 1px dashed rgba(139, 92, 246, 0.3);
            border-radius: 50%;
            animation: sphereRotate 25s linear infinite;
        }

        @keyframes sphereRotate {
            from { transform: translate(-50%, -50%) rotateY(0deg); }
            to { transform: translate(-50%, -50%) rotateY(360deg); }
        }

        /* Stats Section */
        .stats-section {
            padding: 4rem 2rem;
            position: relative;
            z-index: 2;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .stat-card {
            background: rgba(22, 22, 31, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-1);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray-light);
            font-size: 1rem;
        }

        /* Services Section */
        .services {
            padding: 8rem 2rem;
            position: relative;
            z-index: 2;
        }

        .section-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .section-tag {
            display: inline-block;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            padding: 0.6rem 1.25rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .section-description {
            color: var(--gray-light);
            font-size: 1.15rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .service-card {
            background: rgba(22, 22, 31, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 2.5rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .service-card:hover {
            transform: translateY(-15px) rotateX(5deg);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), var(--glow-primary);
        }

        .service-card:hover::before {
            opacity: 1;
        }

        .service-icon {
            width: 70px;
            height: 70px;
            background: var(--gradient-1);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            position: relative;
            transition: transform 0.4s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.1) rotateY(10deg);
        }

        .service-icon::after {
            content: '';
            position: absolute;
            inset: -5px;
            background: var(--gradient-1);
            border-radius: 22px;
            opacity: 0;
            z-index: -1;
            filter: blur(15px);
            transition: opacity 0.4s ease;
        }

        .service-card:hover .service-icon::after {
            opacity: 0.5;
        }

        .service-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
        }

        .service-card p {
            color: var(--gray-light);
            line-height: 1.8;
            position: relative;
        }

        .service-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .service-link i {
            transition: transform 0.3s ease;
        }

        .service-link:hover {
            color: var(--purple);
        }

        .service-link:hover i {
            transform: translateX(5px);
        }

        /* Interactive 3D Section */
        .interactive-section {
            padding: 8rem 2rem;
            position: relative;
            z-index: 2;
            overflow: hidden;
        }

        .interactive-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6rem;
            align-items: center;
        }

        .interactive-content h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .interactive-content p {
            color: var(--gray-light);
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .feature-list {
            list-style: none;
            margin-bottom: 2rem;
        }

        .feature-list li {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
            padding: 1rem;
            background: rgba(22, 22, 31, 0.6);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .feature-list li:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.2);
            transform: translateX(10px);
        }

        .feature-list .check {
            width: 28px;
            height: 28px;
            background: var(--gradient-1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .feature-list span {
            color: var(--gray-light);
            font-size: 1rem;
        }

        /* 3D Cube */
        .cube-container {
            perspective: 1000px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 400px;
        }

        .cube {
            width: 200px;
            height: 200px;
            position: relative;
            transform-style: preserve-3d;
            animation: cubeRotate 20s linear infinite;
        }

        .cube:hover {
            animation-play-state: paused;
        }

        .cube-face {
            position: absolute;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2));
            border: 2px solid rgba(99, 102, 241, 0.3);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            backdrop-filter: blur(10px);
        }

        .cube-face.front { transform: translateZ(100px); }
        .cube-face.back { transform: rotateY(180deg) translateZ(100px); }
        .cube-face.right { transform: rotateY(90deg) translateZ(100px); }
        .cube-face.left { transform: rotateY(-90deg) translateZ(100px); }
        .cube-face.top { transform: rotateX(90deg) translateZ(100px); }
        .cube-face.bottom { transform: rotateX(-90deg) translateZ(100px); }

        @keyframes cubeRotate {
            0% { transform: rotateX(0deg) rotateY(0deg); }
            100% { transform: rotateX(360deg) rotateY(360deg); }
        }

        /* Tech Stack */
        .tech-stack {
            padding: 6rem 2rem;
            position: relative;
            z-index: 2;
            background: linear-gradient(180deg, transparent, rgba(99, 102, 241, 0.03), transparent);
        }

        .tech-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            margin-top: 4rem;
        }

        .tech-item {
            width: 120px;
            height: 120px;
            background: rgba(22, 22, 31, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .tech-item:hover {
            transform: translateY(-10px) scale(1.05);
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: var(--glow-primary);
        }

        .tech-item i {
            font-size: 2.5rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .tech-item span {
            font-size: 0.85rem;
            color: var(--gray-light);
        }

        /* Pricing Section */
        .pricing {
            padding: 8rem 2rem;
            position: relative;
            z-index: 2;
            background: linear-gradient(180deg, transparent, rgba(99, 102, 241, 0.03), transparent);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 4rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .pricing-card {
            background: rgba(22, 22, 31, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 2.5rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
        }

        .pricing-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-1);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .pricing-card:hover {
            transform: translateY(-15px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), var(--glow-primary);
        }

        .pricing-card:hover::before {
            transform: scaleX(1);
        }

        .pricing-card.featured {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(139, 92, 246, 0.15));
            border-color: rgba(99, 102, 241, 0.3);
            transform: scale(1.05);
        }

        .pricing-card.featured::before {
            transform: scaleX(1);
        }

        .pricing-badge {
            display: inline-block;
            background: var(--gradient-1);
            color: var(--white);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.5rem;
        }

        .pricing-plan {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .pricing-description {
            color: var(--gray-light);
            font-size: 0.95rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .pricing-price {
            margin-bottom: 2rem;
        }

        .pricing-amount {
            font-size: 3.5rem;
            font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .pricing-period {
            color: var(--gray-light);
            font-size: 1rem;
        }

        .pricing-features {
            list-style: none;
            margin-bottom: 2.5rem;
        }

        .pricing-features li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
            color: var(--gray-light);
            font-size: 0.95rem;
        }

        .pricing-features li i {
            color: var(--primary);
            font-size: 1rem;
            margin-top: 0.2rem;
            flex-shrink: 0;
        }

        .pricing-button {
            width: 100%;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-block;
            text-align: center;
            border: none;
        }

        .pricing-button-primary {
            background: var(--gradient-1);
            color: var(--white);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }

        .pricing-button-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: var(--glow-primary);
        }

        .pricing-button-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .pricing-button-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Testimonials */
        .testimonials {
            padding: 8rem 2rem;
            position: relative;
            z-index: 2;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .testimonial-card {
            background: rgba(22, 22, 31, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 2.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 1.5rem;
            right: 2rem;
            font-size: 5rem;
            font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            opacity: 0.2;
            line-height: 1;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .testimonial-content {
            font-size: 1.1rem;
            color: var(--gray-light);
            line-height: 1.9;
            margin-bottom: 2rem;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 55px;
            height: 55px;
            background: var(--gradient-1);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .author-info h4 {
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }

        .author-info p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* CTA Section */
        .cta {
            padding: 8rem 2rem;
            position: relative;
            z-index: 2;
        }

        .cta-card {
            max-width: 1100px;
            margin: 0 auto;
            background: var(--gradient-1);
            border-radius: 40px;
            padding: 5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-card::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: ctaGlow 10s linear infinite;
        }

        @keyframes ctaGlow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .cta-card h2 {
            font-size: clamp(2rem, 4vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
        }

        .cta-card p {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .cta-card .btn {
            background: var(--white);
            color: var(--primary-dark);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            font-size: 1.1rem;
            padding: 1.25rem 2.5rem;
        }

        .cta-card .btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        /* Footer */
        footer {
            padding: 5rem 2rem 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            z-index: 2;
            background: rgba(10, 10, 15, 0.8);
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr repeat(3, 1fr);
            gap: 4rem;
            margin-bottom: 4rem;
        }

        .footer-brand .logo {
            margin-bottom: 1.5rem;
        }

        .footer-brand p {
            color: var(--gray-light);
            font-size: 1rem;
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-light);
            text-decoration: none;
            transition: all 0.4s ease;
            font-size: 1.1rem;
        }

        .social-links a:hover {
            background: var(--gradient-1);
            border-color: transparent;
            color: var(--white);
            transform: translateY(-5px);
            box-shadow: var(--glow-primary);
        }

        .footer-column h4 {
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column li {
            margin-bottom: 1rem;
        }

        .footer-column a {
            color: var(--gray-light);
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-column a:hover {
            color: var(--white);
            transform: translateX(5px);
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--gray);
            font-size: 0.95rem;
        }

        /* Scroll Animations */
        .reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 1001;
            padding: 0.5rem;
            transition: transform 0.3s ease;
        }

        .mobile-menu-btn:hover {
            transform: scale(1.1);
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            max-width: 320px;
            height: 100vh;
            background: rgba(10, 10, 15, 0.98);
            backdrop-filter: blur(20px);
            z-index: 2000;
            transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 5rem 2rem 2rem;
            overflow-y: auto;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
        }

        .mobile-menu.active {
            right: 0;
        }

        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .mobile-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .mobile-menu-close {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .mobile-nav-links {
            list-style: none;
            margin-top: 2rem;
        }

        .mobile-nav-links li {
            margin-bottom: 1rem;
        }

        .mobile-nav-links a {
            display: block;
            color: var(--gray-light);
            text-decoration: none;
            font-size: 1.1rem;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .mobile-nav-links a:hover,
        .mobile-nav-links a.active {
            color: var(--white);
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .mobile-nav-buttons {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .mobile-nav-buttons .btn {
            width: 100%;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            nav { padding: 1rem 1.5rem; }
            .services-grid, .testimonials-grid { grid-template-columns: repeat(2, 1fr); }
            .pricing-grid { grid-template-columns: 1fr; max-width: 500px; }
            .pricing-card.featured { transform: scale(1); }
            .hero { padding: 7rem 1.5rem 3rem; }
            .services, .pricing, .testimonials, .cta, .interactive-section { padding: 6rem 1.5rem; }
        }

        @media (max-width: 1024px) {
            .hero-container, .interactive-container { grid-template-columns: 1fr; text-align: center; gap: 3rem; }
            .hero-visual { order: -1; }
            .hero-3d-container { height: 350px; }
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
            .stats-container { grid-template-columns: repeat(2, 1fr); }
            .cube-container { margin-top: 3rem; }
            .nav-buttons { gap: 0.75rem; }
            .nav-buttons .btn { padding: 0.75rem 1.25rem; font-size: 0.9rem; }
        }

        @media (max-width: 768px) {
            nav { padding: 1rem; }
            .nav-container { padding: 0; }
            .logo-text { font-size: 1.2rem; }
            .logo-icon { width: 36px; height: 36px; }
            .nav-links { display: none; }
            .nav-buttons { display: none; }
            .mobile-menu-btn { display: block; }
            .hero { padding: 6rem 1rem 2rem; min-height: auto; }
            .hero-container { gap: 2rem; }
            .hero h1 { font-size: clamp(2rem, 8vw, 3rem); margin-bottom: 1rem; }
            .hero-description { font-size: 1rem; margin-bottom: 2rem; }
            .hero-buttons { flex-direction: column; width: 100%; gap: 0.75rem; }
            .hero-buttons .btn { width: 100%; justify-content: center; }
            .hero-3d-container { height: 280px; }
            .floating-card { padding: 1rem; }
            .floating-card.card-1, .floating-card.card-2, .floating-card.card-3 { width: 140px; }
            .floating-card.card-4 { width: 180px; }
            .floating-card h4 { font-size: 0.85rem; }
            .floating-card p { font-size: 0.75rem; }
            .services-grid, .testimonials-grid { grid-template-columns: 1fr; gap: 1.5rem; }
            .stats-container { grid-template-columns: 1fr; gap: 1rem; }
            .stat-card { padding: 1.5rem; }
            .stat-number { font-size: 2.5rem; }
            .services, .pricing, .testimonials, .cta, .interactive-section, .tech-stack { padding: 4rem 1rem; }
            .section-header { margin-bottom: 3rem; }
            .section-title { font-size: clamp(1.75rem, 6vw, 2.5rem); }
            .section-description { font-size: 1rem; }
            .service-card { padding: 2rem; }
            .service-icon { width: 60px; height: 60px; font-size: 1.5rem; }
            .pricing-grid { gap: 1.5rem; }
            .pricing-card { padding: 2rem; }
            .pricing-amount { font-size: 2.5rem; }
            .interactive-container { gap: 3rem; }
            .cube-container { height: 300px; margin-top: 2rem; }
            .cube { width: 150px; height: 150px; }
            .cube-face { width: 150px; height: 150px; font-size: 2rem; }
            .cube-face.front, .cube-face.back, .cube-face.right, .cube-face.left, .cube-face.top, .cube-face.bottom { transform: translateZ(75px); }
            .cube-face.back { transform: rotateY(180deg) translateZ(75px); }
            .cube-face.right { transform: rotateY(90deg) translateZ(75px); }
            .cube-face.left { transform: rotateY(-90deg) translateZ(75px); }
            .cube-face.top { transform: rotateX(90deg) translateZ(75px); }
            .cube-face.bottom { transform: rotateX(-90deg) translateZ(75px); }
            .tech-grid { gap: 1rem; }
            .tech-item { width: 100px; height: 100px; }
            .tech-item i { font-size: 2rem; }
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
            .hero h1 { font-size: clamp(1.75rem, 10vw, 2.25rem); line-height: 1.2; }
            .hero-description { font-size: 0.95rem; }
            .hero-badge { font-size: 0.8rem; padding: 0.5rem 1rem; }
            .services, .pricing, .testimonials, .cta, .interactive-section, .tech-stack { padding: 3rem 0.75rem; }
            .section-header { margin-bottom: 2rem; }
            .service-card, .pricing-card, .testimonial-card { padding: 1.5rem; }
            .stat-card { padding: 1.25rem; }
            .stat-number { font-size: 2rem; }
            .pricing-amount { font-size: 2rem; }
            .hero-3d-container { height: 220px; }
            .floating-card.card-1, .floating-card.card-2, .floating-card.card-3 { width: 110px; }
            .floating-card.card-4 { width: 150px; }
            .floating-card .card-icon { width: 40px; height: 40px; font-size: 1rem; }
            .tech-item { width: 85px; height: 85px; }
            .tech-item i { font-size: 1.75rem; }
            .tech-item span { font-size: 0.75rem; }
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
                <div class="logo-icon">
                    <svg width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="14" cy="7.5" r="2.5" fill="white"/>
                        <circle cx="7.5" cy="20.5" r="2.5" fill="white"/>
                        <circle cx="20.5" cy="20.5" r="2.5" fill="white"/>
                        <line x1="14" y1="10" x2="8.8" y2="19" stroke="white" stroke-opacity="0.7" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="14" y1="10" x2="19.2" y2="19" stroke="white" stroke-opacity="0.7" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="10" y1="20.5" x2="18" y2="20.5" stroke="white" stroke-opacity="0.7" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="logo-text">BAI</span>
            </a>
            <ul class="nav-links">
                <li><a href="#services">Services</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#tech">Technologies</a></li>
                <li><a href="#testimonials">Testimonials</a></li>
            </ul>
            <div class="nav-buttons">
                <a href="{{ route('login') }}" class="btn btn-ghost">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
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
            <li><a href="#services">Services</a></li>
            <li><a href="#pricing">Pricing</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#tech">Technologies</a></li>
            <li><a href="#testimonials">Testimonials</a></li>
        </ul>
        <div class="mobile-nav-buttons">
            <a href="{{ route('login') }}" class="btn btn-ghost">Sign In</a>
            <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-bolt" aria-hidden="true"></i>
                    <span>Business Automation &amp; Insight</span>
                </div>
                <h1>
                    BAI helps you run<br>
                    your business and<br>
                    <span class="gradient-text">know it better.</span>
                </h1>
                <p class="hero-description">
                    Automation, billing, and analytics in one platform—designed to give you clarity,
                    control, and confident decisions every day.
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket" aria-hidden="true"></i>
                        Start Your Journey
                    </a>
                    <a href="#services" class="btn btn-ghost btn-large">
                        <i class="fas fa-play-circle" aria-hidden="true"></i>
                        Explore Services
                    </a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-3d-container" id="hero3d">
                    <div class="hero-sphere"></div>
                    <div class="floating-card card-1">
                        <div class="card-icon"><i class="fas fa-brain" aria-hidden="true"></i></div>
                        <h4>AI Solutions</h4>
                        <p>Machine Learning &amp; NLP</p>
                    </div>
                    <div class="floating-card card-2">
                        <div class="card-icon"><i class="fas fa-cloud" aria-hidden="true"></i></div>
                        <h4>Cloud Native</h4>
                        <p>Scalable Infrastructure</p>
                    </div>
                    <div class="floating-card card-3">
                        <div class="card-icon"><i class="fas fa-shield-alt" aria-hidden="true"></i></div>
                        <h4>Enterprise Security</h4>
                        <p>SOC 2 Compliant</p>
                    </div>
                    <div class="floating-card card-4">
                        <div class="card-icon"><i class="fas fa-code" aria-hidden="true"></i></div>
                        <h4>Custom Development</h4>
                        <p>Tailored for your needs</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-card reveal">
                <div class="stat-number" data-target="500">0</div>
                <div class="stat-label">Projects Delivered</div>
            </div>
            <div class="stat-card reveal">
                <div class="stat-number" data-target="150">0</div>
                <div class="stat-label">Happy Clients</div>
            </div>
            <div class="stat-card reveal">
                <div class="stat-number" data-target="50">0</div>
                <div class="stat-label">Expert Developers</div>
            </div>
            <div class="stat-card reveal">
                <div class="stat-number" data-target="99">0</div>
                <div class="stat-label">Client Satisfaction %</div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="section-container">
            <div class="section-header reveal">
                <span class="section-tag">Our Services</span>
                <h2 class="section-title">Comprehensive Software Solutions</h2>
                <p class="section-description">
                    We deliver end-to-end software services that transform businesses
                    and drive digital innovation.
                </p>
            </div>
            <div class="services-grid">
                <div class="service-card reveal">
                    <div class="service-icon"><i class="fas fa-brain" aria-hidden="true"></i></div>
                    <h3>Artificial Intelligence</h3>
                    <p>Leverage the power of AI with custom ML models, natural language processing, computer vision, and intelligent automation.</p>
                    <a href="#" class="service-link">Learn more <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="service-card reveal">
                    <div class="service-icon"><i class="fas fa-building" aria-hidden="true"></i></div>
                    <h3>ERP Solutions</h3>
                    <p>Streamline operations with custom ERP systems integrating finance, HR, inventory, CRM, and supply chain management.</p>
                    <a href="#" class="service-link">Learn more <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="service-card reveal">
                    <div class="service-icon"><i class="fas fa-cloud" aria-hidden="true"></i></div>
                    <h3>SaaS Development</h3>
                    <p>Build scalable, cloud-native SaaS applications with modern architecture, multi-tenancy, and seamless integrations.</p>
                    <a href="#" class="service-link">Learn more <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="service-card reveal">
                    <div class="service-icon"><i class="fas fa-mobile-alt" aria-hidden="true"></i></div>
                    <h3>Mobile Applications</h3>
                    <p>Create stunning iOS and Android apps with intuitive UX, real-time features, and cross-platform compatibility.</p>
                    <a href="#" class="service-link">Learn more <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="service-card reveal">
                    <div class="service-icon"><i class="fas fa-cogs" aria-hidden="true"></i></div>
                    <h3>Custom Software</h3>
                    <p>Get tailor-made solutions designed specifically for your unique business requirements and workflows.</p>
                    <a href="#" class="service-link">Learn more <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="service-card reveal">
                    <div class="service-icon"><i class="fas fa-chart-line" aria-hidden="true"></i></div>
                    <h3>Data Analytics</h3>
                    <p>Transform raw data into actionable insights with advanced analytics, visualization, and business intelligence.</p>
                    <a href="#" class="service-link">Learn more <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="section-container">
            <div class="section-header reveal">
                <span class="section-tag">Pricing Plans</span>
                <h2 class="section-title">Choose Your Plan</h2>
                <p class="section-description">
                    Select the perfect plan for your business needs. All plans include our core features
                    with varying levels of support and customization.
                </p>
            </div>
            <div class="pricing-grid">
                <!-- Basic Plan -->
                <div class="pricing-card reveal">
                    <div class="pricing-plan">Basic</div>
                    <p class="pricing-description">Perfect for small teams getting started</p>
                    <div class="pricing-price">
                        <div class="pricing-amount">$29</div>
                        <div class="pricing-period">per month</div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> <span>Up to 10 team members</span></li>
                        <li><i class="fas fa-check"></i> <span>Basic project management</span></li>
                        <li><i class="fas fa-check"></i> <span>5GB storage space</span></li>
                        <li><i class="fas fa-check"></i> <span>Email support</span></li>
                        <li><i class="fas fa-check"></i> <span>Basic analytics</span></li>
                        <li><i class="fas fa-check"></i> <span>Mobile app access</span></li>
                    </ul>
                    <a href="{{ route('register') }}" class="pricing-button pricing-button-secondary">Get Started</a>
                </div>

                <!-- Pro Plan -->
                <div class="pricing-card featured reveal">
                    <div class="pricing-badge">Most Popular</div>
                    <div class="pricing-plan">Pro</div>
                    <p class="pricing-description">Ideal for growing businesses with advanced needs</p>
                    <div class="pricing-price">
                        <div class="pricing-amount">$79</div>
                        <div class="pricing-period">per month</div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> <span>Up to 50 team members</span></li>
                        <li><i class="fas fa-check"></i> <span>Advanced project management</span></li>
                        <li><i class="fas fa-check"></i> <span>100GB storage space</span></li>
                        <li><i class="fas fa-check"></i> <span>Priority support</span></li>
                        <li><i class="fas fa-check"></i> <span>Advanced analytics &amp; reports</span></li>
                        <li><i class="fas fa-check"></i> <span>API access</span></li>
                        <li><i class="fas fa-check"></i> <span>Custom integrations</span></li>
                        <li><i class="fas fa-check"></i> <span>Team collaboration tools</span></li>
                    </ul>
                    <a href="{{ route('register') }}" class="pricing-button pricing-button-primary">Get Started</a>
                </div>

                <!-- Enterprise Plan -->
                <div class="pricing-card reveal">
                    <div class="pricing-plan">Enterprise</div>
                    <p class="pricing-description">For large organizations requiring custom solutions</p>
                    <div class="pricing-price">
                        <div class="pricing-amount">Custom</div>
                        <div class="pricing-period">contact us</div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> <span>Unlimited team members</span></li>
                        <li><i class="fas fa-check"></i> <span>Enterprise project management</span></li>
                        <li><i class="fas fa-check"></i> <span>Unlimited storage</span></li>
                        <li><i class="fas fa-check"></i> <span>24/7 dedicated support</span></li>
                        <li><i class="fas fa-check"></i> <span>Custom analytics &amp; BI</span></li>
                        <li><i class="fas fa-check"></i> <span>Full API access</span></li>
                        <li><i class="fas fa-check"></i> <span>Custom integrations &amp; development</span></li>
                        <li><i class="fas fa-check"></i> <span>Dedicated account manager</span></li>
                        <li><i class="fas fa-check"></i> <span>SLA guarantee</span></li>
                        <li><i class="fas fa-check"></i> <span>On-premise deployment option</span></li>
                    </ul>
                    <a href="{{ route('register') }}" class="pricing-button pricing-button-secondary">Contact Sales</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive 3D Section -->
    <section class="interactive-section" id="features">
        <div class="section-container">
            <div class="interactive-container">
                <div class="interactive-content reveal">
                    <span class="section-tag">Why Choose Us</span>
                    <h2>Built for Scale,<br>Designed for Success</h2>
                    <p>Our solutions are architected with enterprise-grade standards,
                       ensuring reliability, security, and performance at every level.</p>
                    <ul class="feature-list">
                        <li>
                            <span class="check"><i class="fas fa-check" aria-hidden="true"></i></span>
                            <span>Agile development methodology for faster delivery</span>
                        </li>
                        <li>
                            <span class="check"><i class="fas fa-check" aria-hidden="true"></i></span>
                            <span>24/7 support and maintenance services</span>
                        </li>
                        <li>
                            <span class="check"><i class="fas fa-check" aria-hidden="true"></i></span>
                            <span>Cloud-native architecture for unlimited scalability</span>
                        </li>
                        <li>
                            <span class="check"><i class="fas fa-check" aria-hidden="true"></i></span>
                            <span>Enterprise-grade security and compliance</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-large">
                        Get a Free Consultation
                    </a>
                </div>
                <div class="cube-container reveal">
                    <div class="cube" id="cube">
                        <div class="cube-face front"><i class="fas fa-brain" aria-hidden="true"></i></div>
                        <div class="cube-face back"><i class="fas fa-cloud" aria-hidden="true"></i></div>
                        <div class="cube-face right"><i class="fas fa-mobile-alt" aria-hidden="true"></i></div>
                        <div class="cube-face left"><i class="fas fa-cogs" aria-hidden="true"></i></div>
                        <div class="cube-face top"><i class="fas fa-shield-alt" aria-hidden="true"></i></div>
                        <div class="cube-face bottom"><i class="fas fa-chart-line" aria-hidden="true"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Stack -->
    <section class="tech-stack" id="tech">
        <div class="section-container">
            <div class="section-header reveal">
                <span class="section-tag">Technologies</span>
                <h2 class="section-title">Cutting-Edge Tech Stack</h2>
                <p class="section-description">
                    We leverage the latest technologies to build robust and scalable solutions.
                </p>
            </div>
            <div class="tech-grid">
                <div class="tech-item reveal"><i class="fab fa-react"></i><span>React</span></div>
                <div class="tech-item reveal"><i class="fab fa-node-js"></i><span>Node.js</span></div>
                <div class="tech-item reveal"><i class="fab fa-python"></i><span>Python</span></div>
                <div class="tech-item reveal"><i class="fab fa-aws"></i><span>AWS</span></div>
                <div class="tech-item reveal"><i class="fab fa-docker"></i><span>Docker</span></div>
                <div class="tech-item reveal"><i class="fas fa-database" aria-hidden="true"></i><span>MongoDB</span></div>
                <div class="tech-item reveal"><i class="fab fa-laravel"></i><span>Laravel</span></div>
                <div class="tech-item reveal"><i class="fab fa-vuejs"></i><span>Vue.js</span></div>
                <div class="tech-item reveal"><i class="fas fa-fire" aria-hidden="true"></i><span>Firebase</span></div>
                <div class="tech-item reveal"><i class="fab fa-angular"></i><span>Angular</span></div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="section-container">
            <div class="section-header reveal">
                <span class="section-tag">Testimonials</span>
                <h2 class="section-title">What Our Clients Say</h2>
                <p class="section-description">
                    Hear from businesses that have transformed their operations with our solutions.
                </p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card reveal">
                    <p class="testimonial-content">
                        "BAI delivered our ERP system ahead of schedule. Their team's
                        expertise and dedication transformed our entire operation. The ROI was visible within weeks!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">RK</div>
                        <div class="author-info">
                            <h4>Rahul Kumar</h4>
                            <p>CEO, TechManufacturing Ltd.</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card reveal">
                    <p class="testimonial-content">
                        "The AI solution they built has automated 70% of our customer service.
                        Their technical expertise is unmatched. Highly recommended for any AI projects!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">PS</div>
                        <div class="author-info">
                            <h4>Priya Sharma</h4>
                            <p>CTO, FinServe Solutions</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card reveal">
                    <p class="testimonial-content">
                        "Working with BAI on our SaaS platform was a game-changer.
                        Their technical expertise and project management are truly world-class!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">AM</div>
                        <div class="author-info">
                            <h4>Amit Mehta</h4>
                            <p>Founder, CloudStartup Inc.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-card reveal">
            <h2>Ready to Transform Your Business?</h2>
            <p>Let's discuss how our software solutions can accelerate your growth.</p>
            <a href="{{ route('register') }}" class="btn btn-large">
                <i class="fas fa-calendar-check" aria-hidden="true"></i>
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
                        <div class="logo-icon">
                            <svg width="22" height="22" viewBox="0 0 28 28" fill="none">
                                <circle cx="14" cy="7.5" r="2.5" fill="white"/>
                                <circle cx="7.5" cy="20.5" r="2.5" fill="white"/>
                                <circle cx="20.5" cy="20.5" r="2.5" fill="white"/>
                                <line x1="14" y1="10" x2="8.8" y2="19" stroke="white" stroke-opacity="0.7" stroke-width="1.5" stroke-linecap="round"/>
                                <line x1="14" y1="10" x2="19.2" y2="19" stroke="white" stroke-opacity="0.7" stroke-width="1.5" stroke-linecap="round"/>
                                <line x1="10" y1="20.5" x2="18" y2="20.5" stroke="white" stroke-opacity="0.7" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="logo-text">BAI</span>
                    </a>
                    <p>Business Automation &amp; Insight — automation, billing, and analytics in one place so you can run your business and know it better.</p>
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
                        <li><a href="#">BAI Docs</a></li>
                        <li><a href="#">BAI Desk</a></li>
                        <li><a href="#">BAI CRM</a></li>
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
                <p>&copy; {{ date('Y') }} BAI — Business Automation &amp; Insight. All rights reserved.</p>
                <p>Crafted with <i class="fas fa-heart" style="color: #f43f5e;" aria-hidden="true"></i> for digital excellence</p>
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

        const hoverElements = document.querySelectorAll('a, button, .service-card, .tech-item, .testimonial-card, .stat-card');
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
            const colors = ['#6366f1', '#8b5cf6', '#06b6d4', '#f43f5e'];
            particle.style.background = colors[Math.floor(Math.random() * colors.length)];
            particlesContainer.appendChild(particle);
        }

        // 3D Hero Effect - Mouse tracking
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

        // 3D Cube - Mouse tracking
        const cube = document.getElementById('cube');
        let cubeRotateX = 0, cubeRotateY = 0;
        let targetRotateX = 0, targetRotateY = 0;
        let autoRotate = true;

        cube.addEventListener('mouseenter', () => autoRotate = false);
        cube.addEventListener('mouseleave', () => autoRotate = true);

        document.addEventListener('mousemove', (e) => {
            if (autoRotate || !cube) return;
            const rect = cube.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            targetRotateX = (e.clientY - centerY) / 5;
            targetRotateY = (e.clientX - centerX) / 5;
        });

        function animateCube() {
            if (!autoRotate) {
                cubeRotateX += (targetRotateX - cubeRotateX) * 0.1;
                cubeRotateY += (targetRotateY - cubeRotateY) * 0.1;
                cube.style.animation = 'none';
                cube.style.transform = `rotateX(${cubeRotateX}deg) rotateY(${cubeRotateY}deg)`;
            } else {
                cube.style.animation = 'cubeRotate 20s linear infinite';
            }
            requestAnimationFrame(animateCube);
        }
        animateCube();

        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Scroll reveal animations
        const reveals = document.querySelectorAll('.reveal');
        function revealOnScroll() {
            reveals.forEach(el => {
                const windowHeight = window.innerHeight;
                const elementTop = el.getBoundingClientRect().top;
                const revealPoint = 150;
                if (elementTop < windowHeight - revealPoint) {
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
                    const increment = target / 100;
                    let current = 0;
                    const updateCounter = () => {
                        current += increment;
                        if (current < target) {
                            counter.textContent = Math.ceil(current) + '+';
                            requestAnimationFrame(updateCounter);
                        } else {
                            counter.textContent = target + '+';
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
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#' || !href) return;
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({ top: offsetTop, behavior: 'smooth' });
                }
            });
        });

        // Tilt effect for service cards
        const cards = document.querySelectorAll('.service-card');
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
                card.style.transform = 'translateY(0) rotateX(0) rotateY(0)';
            });
        });
    </script>
</body>
</html>
