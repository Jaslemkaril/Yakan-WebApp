<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yakan - Premium Products & Custom Orders</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    @auth
    <script>
    function notificationDropdown() {
        return {
            open: false,
            notifications: @json(auth()->user()->notifications()->unread()->orderBy('created_at', 'desc')->take(5)->get()),
            unreadCount: {{ auth()->user()->unread_notification_count }},
            
            loadNotifications() {
                fetch('/notifications/recent')
                    .then(response => response.json())
                    .then(data => {
                        this.notifications = data.notifications;
                        this.unreadCount = data.unread_count;
                    });
            },
            
            markAsRead(notificationId) {
                fetch(`/notifications/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.unreadCount = data.unread_count;
                        this.loadNotifications();
                        this.updateHeaderBadge();
                    }
                });
            },
            
            markAllAsRead() {
                fetch('/notifications/mark-all-read', {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.notifications = [];
                        this.unreadCount = 0;
                        this.updateHeaderBadge();
                    }
                });
            },
            
            updateHeaderBadge() {
                const badge = document.getElementById('notification-badge');
                if (badge) {
                    if (this.unreadCount > 0) {
                        badge.textContent = this.unreadCount;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            }
        }
    }
    </script>
    @endauth
    
    <!-- Tailwind CSS -->
    <!-- Vite build disabled for Expo frontend -->
    
    @stack('styles')
    
    <!-- Prevent Alpine.js flash of unstyled content -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <style>
        /* Modern Design System - Maroon Theme */
        :root {
            --primary: 128, 0, 0;        /* Maroon */
            --primary-dark: 96, 0, 0;    /* Dark Maroon */
            --secondary: 255, 255, 255;  /* White */
            --accent: 160, 0, 0;         /* Light Maroon */
            --success: 34, 197, 94;
            --warning: 250, 204, 21;
            --error: 220, 38, 38;
            --neutral: 243, 244, 246;
            --dark: 17, 24, 39;
        }

        * { 
            font-family: 'Inter', sans-serif;
        }

        /* Apply transitions only to interactive elements, not all elements */
        a, button, input, select, textarea,
        .nav-link, .btn-primary, .btn-secondary, .card, .product-card,
        [class*="hover:"], [x-data] > div {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Exclude page-load elements from transitions to prevent glitches */
        nav, nav *, .cart-badge, #notification-badge,
        #wishlist-count-badge, footer, footer * {
            transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, opacity 0.3s ease, transform 0.3s ease !important;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        /* Chat Pages Override - CRITICAL */
        html.chat-page-html {
            background: linear-gradient(135deg, #800000 0%, #600000 100%) !important;
        }

        body.chat-page {
            background: linear-gradient(135deg, #800000 0%, #600000 100%) !important;
            min-height: 100vh !important;
        }

        body.chat-page main {
            background: linear-gradient(135deg, #800000 0%, #600000 100%) !important;
            min-height: 100vh !important;
        }

        body.chat-page nav {
            background: rgba(255, 255, 255, 0.95) !important;
        }

        body.chat-page footer {
            background: linear-gradient(135deg, #600000 0%, #400000 100%) !important;
        }

        main.chat-page-main {
            background: linear-gradient(135deg, #800000 0%, #600000 100%) !important;
            min-height: 100vh !important;
        }

        /* Glass Morphism Effects */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .glass-dark {
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Modern Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-slide-in-left {
            animation: slideInLeft 0.5s ease-out;
        }

        .animate-pulse-slow {
            animation: pulse 2s infinite;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        /* Modern Buttons */
        .btn-primary {
            background: linear-gradient(135deg, rgb(var(--primary)) 0%, rgb(var(--primary-dark)) 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(var(--primary), 0.3);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(var(--primary), 0.4);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-secondary {
            background: white;
            color: rgb(var(--primary));
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            border: 2px solid rgb(var(--primary));
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background: rgb(var(--primary));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(var(--primary), 0.3);
        }

        /* Modern Cards */
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, rgb(var(--primary)), rgb(var(--accent)));
        }

        /* ── Navigation Links ── */
        .nav-link {
            position: relative;
            padding: 6px 8px;
            border-radius: 8px;
            transition: all 0.25s ease;
            font-weight: 500;
            color: #374151;
            white-space: nowrap;
        }
        @media (min-width: 1024px) { .nav-link { padding: 8px 12px; } }
        @media (min-width: 1280px) { .nav-link { padding: 8px 16px; } }
        .nav-link:hover { background: rgba(128,0,0,0.08); color: #800000; }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0px;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #800000, #b30000);
            transition: all 0.25s ease;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        .nav-link:hover::after { width: 70%; }

        /* ── Mobile Nav Links ── */
        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            transition: background 0.18s, color 0.18s;
            text-decoration: none;
        }
        .mobile-nav-link:hover {
            background: rgba(128,0,0,0.08);
            color: #800000;
        }

        /* ── Search Bar ── */
        .search-wrap { position: relative; width: 100%; }

        .search-input {
            width: 100%;
            padding: 9px 42px 9px 42px;
            border: 2px solid #e5e7eb;
            border-radius: 999px;
            font-size: 14px;
            background: #f9fafb;
            color: #111827;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .search-input::placeholder { color: #9ca3af; }
        .search-input:focus {
            border-color: #800000;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(128,0,0,0.10);
        }
        .search-input:focus ~ .search-kbd { opacity: 0; pointer-events: none; }

        .search-icon-btn {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            border-radius: 50%;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            transition: color 0.2s, background 0.2s;
        }
        .search-input:focus + .search-icon-btn,
        .search-wrap:focus-within .search-icon-btn { color: #800000; }

        .search-clear-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
            align-items: center;
            justify-content: center;
            width: 22px; height: 22px;
            border-radius: 50%;
            background: #e5e7eb;
            border: none;
            cursor: pointer;
            color: #6b7280;
            font-size: 12px;
            transition: background 0.15s, color 0.15s;
        }
        .search-clear-btn:hover { background: #800000; color: #fff; }
        .search-clear-btn.visible { display: flex; }

        .search-kbd {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 3px;
            pointer-events: none;
            opacity: 1;
            transition: opacity 0.2s;
        }
        .search-kbd kbd {
            font-size: 10px;
            font-family: inherit;
            color: #9ca3af;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 1px 5px;
            line-height: 16px;
        }

        /* Mobile search overlay */
        .mobile-search-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            z-index: 999;
            display: none;
            align-items: flex-start;
            padding-top: 70px;
        }
        .mobile-search-overlay.active { display: flex; }
        .mobile-search-box {
            width: calc(100% - 32px);
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            animation: searchSlideDown 0.2s ease;
        }
        @keyframes searchSlideDown {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Modern Form Elements */
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: rgb(var(--primary));
            box-shadow: 0 0 0 3px rgba(var(--primary), 0.1);
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: rgb(var(--dark));
        }

        /* Cart Badge Animation */
        .cart-badge {
            animation: pulse 2s infinite;
            font-weight: bold;
            font-size: 0.75rem;
            min-width: 1.25rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            border: 2px solid white;
        }

        /* Dropdown Animation */
        .dropdown-enter {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Product Card Hover Effects */
        .product-card {
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(var(--primary), 0.1) 0%, rgba(var(--accent), 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover::before {
            opacity: 1;
        }

        /* Loading States */
        .loading {
            position: relative;
            overflow: hidden;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--accent)));
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, rgb(var(--primary-dark)), rgb(var(--accent)));
        }

        /* Responsive Typography */
        .text-gradient {
            background: linear-gradient(135deg, rgb(var(--primary)) 0%, rgb(var(--accent)) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Hero Section Styles */
        .hero-gradient {
            background: linear-gradient(135deg, rgb(var(--primary)) 0%, rgb(var(--accent)) 100%);
        }

        /* Floating Elements */
        .floating-element {
            position: fixed;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.3;
            pointer-events: none;
            z-index: 0;
        }

        .floating-1 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--accent)));
            top: -150px;
            right: -150px;
            animation: float 6s ease-in-out infinite;
        }

        .floating-2 {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, rgb(var(--accent)), rgb(var(--success)));
            bottom: -100px;
            left: -100px;
            animation: float 8s ease-in-out infinite reverse;
        }

        .floating-3 {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, rgb(var(--success)), rgb(var(--warning)));
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: float 10s ease-in-out infinite;
        }
    </style>
</head>
<body class="antialiased" style="opacity: 0;">
    <!-- ===== Page Loading Screen ===== -->
    <div id="pageLoader" style="position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;background:#ffffff;transition:opacity 0.4s ease, visibility 0.4s ease;">
        <div style="text-align:center;">
            <!-- Yakan Logo Spinner -->
            <div style="position:relative;width:80px;height:80px;margin:0 auto 24px;">
                <!-- Outer spinning ring -->
                <div style="position:absolute;inset:0;border:3px solid #f3e8e8;border-top:3px solid #800000;border-right:3px solid #a00000;border-radius:50%;animation:loaderSpin 1s cubic-bezier(0.5,0,0.5,1) infinite;"></div>
                <!-- Inner spinning ring (opposite direction) -->
                <div style="position:absolute;inset:8px;border:2px solid #f3e8e8;border-bottom:2px solid #800000;border-left:2px solid #a00000;border-radius:50%;animation:loaderSpin 0.8s cubic-bezier(0.5,0,0.5,1) infinite reverse;"></div>
                <!-- Center logo -->
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#800000,#a00000);border-radius:10px;display:flex;align-items:center;justify-content:center;animation:loaderPulse 1.5s ease-in-out infinite;">
                        <span style="color:white;font-weight:800;font-size:18px;font-family:Inter,sans-serif;">Y</span>
                    </div>
                </div>
            </div>
            <!-- Loading text -->
            <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
                <span style="font-family:Inter,sans-serif;font-weight:700;font-size:14px;color:#800000;letter-spacing:2px;">LOADING</span>
                <span style="display:inline-flex;gap:3px;" id="loaderDots">
                    <span style="width:4px;height:4px;background:#800000;border-radius:50%;animation:loaderDot 1.2s ease-in-out infinite;animation-delay:0s;"></span>
                    <span style="width:4px;height:4px;background:#800000;border-radius:50%;animation:loaderDot 1.2s ease-in-out infinite;animation-delay:0.2s;"></span>
                    <span style="width:4px;height:4px;background:#800000;border-radius:50%;animation:loaderDot 1.2s ease-in-out infinite;animation-delay:0.4s;"></span>
                </span>
            </div>
            <!-- Progress bar -->
            <div style="width:120px;height:3px;background:#f3e8e8;border-radius:3px;margin:12px auto 0;overflow:hidden;">
                <div id="loaderProgress" style="height:100%;width:0%;background:linear-gradient(90deg,#800000,#c02020);border-radius:3px;transition:width 0.3s ease;"></div>
            </div>
        </div>
    </div>

    <style>
        @keyframes loaderSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes loaderPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(0.9); opacity: 0.8; }
        }
        @keyframes loaderDot {
            0%, 80%, 100% { opacity: 0.3; transform: translateY(0); }
            40% { opacity: 1; transform: translateY(-3px); }
        }
        /* Page content fade-in after load */
        @keyframes pageReveal {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        body.page-loaded {
            opacity: 1 !important;
            animation: pageReveal 0.4s ease-out;
        }
        /* Navigation link loading indicator */
        .nav-loading-indicator {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #800000, #c02020, #800000);
            background-size: 200% 100%;
            animation: navBarShimmer 1.5s linear infinite;
            z-index: 99998;
            transition: width 0.4s ease;
            box-shadow: 0 0 8px rgba(128, 0, 0, 0.4);
        }
        @keyframes navBarShimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>

    <script>
    (function() {
        // ===== Page Load Handler =====
        var loader = document.getElementById('pageLoader');
        var progress = document.getElementById('loaderProgress');
        var loaded = false;

        // Simulate progress
        var pct = 0;
        var progressInterval = setInterval(function() {
            if (pct < 90) {
                pct += Math.random() * 15 + 5;
                if (pct > 90) pct = 90;
                if (progress) progress.style.width = pct + '%';
            }
        }, 150);

        function hideLoader() {
            if (loaded) return;
            loaded = true;
            clearInterval(progressInterval);
            if (progress) progress.style.width = '100%';
            
            setTimeout(function() {
                document.body.classList.add('page-loaded');
                if (loader) {
                    loader.style.opacity = '0';
                    loader.style.visibility = 'hidden';
                    setTimeout(function() { loader.style.display = 'none'; }, 400);
                }
            }, 200);
        }

        // Hide loader when page is fully loaded
        window.addEventListener('load', hideLoader);
        // Fallback: hide after 4s max
        setTimeout(hideLoader, 4000);

        // ===== Navigation Loading Bar =====
        document.addEventListener('DOMContentLoaded', function() {
            // Create the top loading bar element
            var navBar = document.createElement('div');
            navBar.className = 'nav-loading-indicator';
            navBar.id = 'navLoadingBar';
            navBar.style.display = 'none';
            document.body.appendChild(navBar);

            // Intercept navigation clicks
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a[href]');
                if (!link) return;

                var href = link.getAttribute('href');
                // Skip: anchors, javascript:, external, new tabs, special protocols
                if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
                    href.startsWith('mailto:') || href.startsWith('tel:') ||
                    link.target === '_blank' || link.hasAttribute('download') ||
                    e.ctrlKey || e.metaKey || e.shiftKey) return;

                // Skip external links
                try {
                    var url = new URL(href, window.location.origin);
                    if (url.origin !== window.location.origin) return;
                } catch(ex) { return; }

                // Show the navigation loading bar
                navBar.style.display = 'block';
                navBar.style.width = '0%';
                // Force reflow
                navBar.offsetWidth;
                // Animate to 30% quickly, then slow crawl
                navBar.style.width = '30%';
                setTimeout(function() { navBar.style.width = '70%'; }, 300);
                setTimeout(function() { navBar.style.width = '85%'; }, 800);
            });

            // Handle form submissions too
            document.addEventListener('submit', function(e) {
                var form = e.target;
                if (form.method && form.method.toUpperCase() === 'GET') {
                    navBar.style.display = 'block';
                    navBar.style.width = '0%';
                    navBar.offsetWidth;
                    navBar.style.width = '40%';
                    setTimeout(function() { navBar.style.width = '80%'; }, 500);
                }
            });

            // Before page unload, push bar to 95%
            window.addEventListener('beforeunload', function() {
                if (navBar.style.display !== 'none') {
                    navBar.style.width = '95%';
                }
            });
        });
    })();
    </script>
    <!-- Floating Background Elements -->
    @unless(request()->routeIs('chats.*'))
    <div class="floating-element floating-1"></div>
    <div class="floating-element floating-2"></div>
    <div class="floating-element floating-3"></div>
    @endunless

    <!-- Navigation -->
    <nav x-data="{ mobileMenu: false, userMenu: false }" class="bg-white/80 backdrop-blur-lg shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('welcome') }}" class="flex items-center space-x-2 sm:space-x-3 group">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-maroon-600 to-maroon-700 rounded-xl flex items-center justify-center transform group-hover:rotate-12 transition-transform" style="background: linear-gradient(to bottom right, #800000, #600000);">
                            <span class="text-white font-bold text-lg sm:text-xl">Y</span>
                        </div>
                        <span class="text-xl sm:text-2xl font-bold text-gradient">Yakan</span>
                    </a>
                </div>

                <!-- ── Desktop Search Bar ── -->
                <div class="hidden md:flex flex-1 max-w-md lg:max-w-xl mx-4 lg:mx-6">
                    <form action="{{ route('products.search') }}" method="GET" class="w-full" id="searchForm">
                        <div class="search-wrap">
                            {{-- Submit / search icon --}}
                            <button type="submit" class="search-icon-btn" tabindex="-1" aria-label="Search">
                                <svg id="searchSpinner" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>

                            <input
                                type="text"
                                name="q"
                                id="searchInput"
                                placeholder="Search products, patterns…"
                                value="{{ request('q') }}"
                                class="search-input"
                                autocomplete="off"
                                spellcheck="false"
                            >

                            {{-- Clear button --}}
                            <button type="button" class="search-clear-btn {{ request('q') ? 'visible' : '' }}" id="searchClear" aria-label="Clear search" tabindex="-1">✕</button>

                            {{-- Keyboard shortcut hint (hidden when typing) --}}
                            <div class="search-kbd" id="searchKbd">
                                <kbd>/</kbd>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-2 xl:space-x-4">
                    <a href="{{ route('welcome') }}" class="nav-link whitespace-nowrap text-sm xl:text-base">Home</a>
                    <a href="{{ route('products.index') }}" class="nav-link whitespace-nowrap text-sm xl:text-base">Products</a>
                    <a href="{{ route('custom_orders.index') }}" class="nav-link whitespace-nowrap text-sm xl:text-base">Custom Orders</a>
                    <a href="{{ route('cultural-heritage.index') }}" class="nav-link whitespace-nowrap text-sm xl:text-base">Cultural Heritage</a>
                    <a href="{{ route('track-order.index') }}" class="nav-link whitespace-nowrap text-sm xl:text-base">Track Order</a>
                    @auth
                        <a href="{{ route('chats.index') }}" class="nav-link whitespace-nowrap flex items-center gap-1.5 relative text-sm lg:text-base">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span class="hidden lg:inline">Support</span>
                            @if($unreadChatCount > 0)
                                <span style="position: absolute; top: -8px; right: -8px; background-color: #800000; color: white; font-size: 10px; font-weight: bold; padding: 2px 5px; border-radius: 10px; min-width: 18px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3); z-index: 10;">
                                    {{ $unreadChatCount > 9 ? '9+' : $unreadChatCount }}
                                </span>
                            @endif
                        </a>
                    @endauth
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-3">
                    <!-- Wishlist Icon -->
                    @auth
                        <a href="{{ route('wishlist.index') }}" class="p-1.5 lg:p-2 rounded-lg hover:bg-gray-100 transition-colors relative" title="My Wishlist">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b3a56;" onmouseover="this.style.color='#7a3350'" onmouseout="this.style.color='#8b3a56'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            @php
                                $wishlist = \App\Models\Wishlist::where('user_id', auth()->user()->id)->first();
                                $wishlistCount = $wishlist ? $wishlist->items()->count() : 0;
                            @endphp
                            <span id="wishlist-count-badge" class="absolute -top-1 -right-1 text-white text-xs font-bold rounded-full flex items-center justify-center w-5 h-5 {{ $wishlistCount > 0 ? '' : 'hidden' }}" style="background-color: #800000;">
                                {{ $wishlistCount > 99 ? '99+' : $wishlistCount }}
                            </span>
                        </a>
                    @endauth

                    @auth
                        <!-- Cart -->
                        <a href="{{ route('cart.index') }}" class="relative group">
                            <div class="p-1.5 lg:p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-gray-700 group-hover:text-maroon-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="--tw-text-opacity: 1;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                @php
                                    // Get cart count efficiently with caching - sum of all quantities
                                    $cartCount = 0;
                                    try {
                                        $cacheKey = 'cart_count_' . auth()->user()->id;
                                        $cartCount = \Cache::remember($cacheKey, 300, function () {
                                            return \App\Models\Cart::where('user_id', auth()->user()->id)->sum('quantity');
                                        });
                                    } catch (\Exception $e) {
                                        // Fallback to 0 if query fails
                                        $cartCount = 0;
                                    }
                                @endphp
                                <span id="cart-count-badge" class="cart-badge absolute -top-1 -right-1 text-white text-xs font-bold rounded-full flex items-center justify-center w-5 h-5 {{ $cartCount > 0 ? '' : 'hidden' }}" style="background-color: #800000;">
                                    {{ $cartCount > 99 ? '99+' : $cartCount }}
                                </span>
                            </div>
                        </a>

                        <!-- Notifications -->
                        <x-notification-dropdown />

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-1 lg:space-x-2 p-1.5 lg:p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-7 h-7 lg:w-8 lg:h-8 rounded-full object-cover border-2 border-maroon-600" loading="lazy">
                                @else
                                    <div class="w-7 h-7 lg:w-8 lg:h-8 rounded-full flex items-center justify-center" style="background: linear-gradient(to bottom right, #800000, #600000);">
                                        <span class="text-white text-xs lg:text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <svg class="w-3 h-3 lg:w-4 lg:h-4 text-gray-600 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform scale-95 opacity-0" x-transition:enter-end="transform scale-100 opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform scale-100 opacity-100" x-transition:leave-end="transform scale-95 opacity-0" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Wishlist</a>
                                <a href="{{ route('addresses.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Saved Addresses</a>
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Orders</a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Dashboard</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50" style="color: #800000;">Logout</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Login/Register Buttons -->
                        <a href="{{ route('login') }}" class="px-3 py-1.5 lg:px-4 lg:py-2 text-xs sm:text-sm lg:text-base font-semibold border-2 rounded-lg transition-all hover:bg-gray-50 whitespace-nowrap" style="border-color: #800000; color: #800000;">Login</a>
                        <a href="{{ route('register') }}" class="px-3 py-1.5 lg:px-4 lg:py-2 text-xs sm:text-sm lg:text-base font-semibold rounded-lg text-white transition-all hover:opacity-90 whitespace-nowrap" style="background-color: #800000;">Sign Up</a>
                    @endauth

                    <!-- Mobile Search Toggle -->
                    <button id="mobileSearchToggle" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Search">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Menu">
                        <svg x-show="!mobileMenu" class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileMenu" class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ── Mobile Menu ── -->
            <div x-show="mobileMenu"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="lg:hidden border-t border-gray-100 bg-white"
                 style="display:none;">

                {{-- Nav Links Grid --}}
                <div class="px-4 py-3 grid grid-cols-2 gap-1">
                    <a href="{{ route('welcome') }}" class="mobile-nav-link">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Home
                    </a>
                    <a href="{{ route('products.index') }}" class="mobile-nav-link">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Products
                    </a>
                    <a href="{{ route('custom_orders.index') }}" class="mobile-nav-link">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Custom Orders
                    </a>
                    <a href="{{ route('cultural-heritage.index') }}" class="mobile-nav-link">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Heritage
                    </a>
                    <a href="{{ route('track-order.index') }}" class="mobile-nav-link">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Track Order
                    </a>
                    @auth
                        <a href="{{ route('chats.index') }}" class="mobile-nav-link relative">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            Support
                            @if($unreadChatCount > 0)
                                <span class="ml-auto text-xs font-bold text-white px-1.5 py-0.5 rounded-full" style="background:#800000;font-size:10px;">{{ $unreadChatCount > 9 ? '9+' : $unreadChatCount }}</span>
                            @endif
                        </a>
                    @endauth
                </div>

                @auth
                {{-- Auth quick actions --}}
                <div class="mx-4 mb-3 p-3 bg-gray-50 rounded-xl flex items-center gap-3">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="" class="w-10 h-10 rounded-full object-cover border-2" style="border-color:#800000;">
                    @else
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold" style="background:linear-gradient(135deg,#800000,#5a0000);">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors" style="border-color:#800000;color:#800000;">
                            Logout
                        </button>
                    </form>
                </div>
                @else
                <div class="mx-4 mb-3 flex gap-2">
                    <a href="{{ route('login') }}" class="flex-1 text-center py-2.5 text-sm font-semibold rounded-xl border-2 transition-all" style="border-color:#800000;color:#800000;">Login</a>
                    <a href="{{ route('register') }}" class="flex-1 text-center py-2.5 text-sm font-semibold rounded-xl text-white transition-all" style="background:#800000;">Sign Up</a>
                </div>
                @endauth
            </div>

        </div>
    </nav>

    {{-- ── Mobile Search Overlay (outside nav so fixed positioning works correctly) ── --}}
    <div id="mobileSearchOverlay" class="mobile-search-overlay" onclick="if(event.target===this)closeMobileSearch()">
        <div class="mobile-search-box">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-700">Search</p>
                <button type="button" onclick="closeMobileSearch()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition-colors text-xs">✕</button>
            </div>
            <form action="{{ route('products.search') }}" method="GET" id="mobileSearchForm">
                <div class="search-wrap">
                    <button type="submit" class="search-icon-btn" aria-label="Search">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                    <input
                        type="text"
                        name="q"
                        id="mobileSearchInput"
                        placeholder="Search products, patterns…"
                        value="{{ request('q') }}"
                        class="search-input"
                        autocomplete="off"
                        spellcheck="false"
                    >
                    <button type="button" class="search-clear-btn {{ request('q') ? 'visible' : '' }}" id="mobileSearchClear" aria-label="Clear" tabindex="-1">✕</button>
                </div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="text-xs text-gray-400 font-medium mr-1 self-center">Popular:</span>
                    @foreach(['Malong', 'Patadyong', 'Headband', 'Tote Bag'] as $tag)
                        <button type="button"
                                onclick="document.getElementById('mobileSearchInput').value='{{ $tag }}'; document.getElementById('mobileSearchForm').submit();"
                                class="text-xs px-3 py-1 rounded-full border transition-colors"
                                style="border-color:#800000;color:#800000;"
                                onmouseover="this.style.background='#800000';this.style.color='white'"
                                onmouseout="this.style.background='';this.style.color='#800000'">
                            {{ $tag }}
                        </button>
                    @endforeach
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <main class="relative z-10">
        @yield('content')
    </main>

    <!-- Modern Footer -->
    <footer class="bg-gray-900 text-white {{ request()->routeIs('chats.*') ? 'mt-0' : 'mt-20' }} relative overflow-hidden">
        <div class="absolute inset-0" style="background: linear-gradient(to bottom right, rgba(128, 0, 0, 0.2), rgba(96, 0, 0, 0.2));"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(to bottom right, #800000, #600000);">
                            <span class="text-white font-bold text-xl">Y</span>
                        </div>
                        <span class="text-2xl font-bold">Yakan</span>
                    </div>
                    <p class="text-gray-400">Premium quality products and custom orders tailored to your needs.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('products.index') }}" class="text-gray-400 hover:text-white transition-colors">Products</a></li>
                        <li><a href="{{ route('custom_orders.index') }}" class="text-gray-400 hover:text-white transition-colors">Custom Orders</a></li>
                        <li><a href="{{ route('track-order.index') }}" class="text-gray-400 hover:text-white transition-colors">Track Order</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Customer Service</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Shipping Info</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Returns</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Size Guide</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Stay Updated</h3>
                    <p class="text-gray-400 mb-4">Subscribe to get special offers and updates</p>
                    <form class="space-y-3">
                        <input type="email" placeholder="Your email" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none" style="border-color: #800000;">
                        <button type="submit" class="w-full btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Yakan. All rights reserved. Made with ❤️</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
    
    <!-- Fix for double-click issues -->
    <script>
        // Prevent Alpine.js conflicts with our click handlers
        document.addEventListener('alpine:initialized', function() {
            // Re-initialize our event handlers after Alpine loads
            if (window.initProductCards) {
                window.initProductCards();
            }
        });
        
        // Global double-click prevention
        document.addEventListener('dblclick', function(e) {
            // Prevent default double-click behavior on interactive elements
            if (e.target.closest('.product-card, .btn, button, a')) {
                e.preventDefault();
                return false;
            }
        }, true);

        // Global Add to Cart Handler with AJAX
        function updateCartCount(count) {
            const badge = document.getElementById('cart-count-badge');
            if (badge) {
                badge.textContent = count > 99 ? '99+' : count;
                if (count > 0) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        }

        // Handle all Add to Cart forms
        document.addEventListener('submit', function(e) {
            const form = e.target;
            
            // Check if this is an add to cart form
            if (form.action && form.action.includes('/cart/add/')) {
                // Don't intercept if it's a "Buy Now" action
                const buyNowInput = form.querySelector('input[name="buy_now"]');
                if (buyNowInput && buyNowInput.value === '1') {
                    return; // Let it submit normally for checkout redirect
                }

                e.preventDefault();
                
                const button = form.querySelector('button[type="submit"]');
                const originalText = button ? button.innerHTML : '';
                
                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                }

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type') || '';
                    if (!response.ok) {
                        // If redirected to login or got HTML, surface a helpful message
                        if (response.status === 401 || response.status === 419 || contentType.includes('text/html')) {
                            throw new Error('Please login to add items to your cart.');
                        }
                        throw new Error('Failed to add to cart.');
                    }
                    if (!contentType.includes('application/json')) {
                        throw new Error('Unexpected response from server.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update cart count
                        updateCartCount(data.cart_count);
                        
                        // Show success message
                        showToast(data.message, 'success');
                        
                        // Reset button
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = originalText;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast(error.message || 'Failed to add to cart. Please try again.', 'error');
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = originalText;
                    }
                });
            }
        });

        // Toast notification function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 text-white`;
            toast.style.backgroundColor = type === 'success' ? '#22c55e' : '#800000';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ── Search Enhancement ──
        function initSearch(formId, inputId, clearId, kbdId) {
            const form  = document.getElementById(formId);
            const input = document.getElementById(inputId);
            const clear = document.getElementById(clearId);
            const kbd   = document.getElementById(kbdId);
            if (!form || !input) return;

            // Show/hide clear button and kbd hint
            function onInput() {
                const hasVal = input.value.trim().length > 0;
                if (clear) clear.classList.toggle('visible', hasVal);
                if (kbd)   kbd.style.opacity = hasVal ? '0' : '1';
            }
            input.addEventListener('input', onInput);
            onInput();

            // Clear
            if (clear) clear.addEventListener('click', () => {
                input.value = ''; onInput(); input.focus();
            });

            // Empty → all products
            form.addEventListener('submit', function(e) {
                if (!input.value.trim()) {
                    e.preventDefault();
                    window.location.href = '{{ route("products.index") }}';
                }
            });
        }

        initSearch('searchForm',       'searchInput',       'searchClear',       'searchKbd');
        initSearch('mobileSearchForm', 'mobileSearchInput', 'mobileSearchClear', null);

        // Keyboard shortcut: press '/' to focus desktop search
        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                const si = document.getElementById('searchInput');
                if (si) si.focus();
            }
            if (e.key === 'Escape') {
                const si = document.getElementById('searchInput');
                if (si && document.activeElement === si) si.blur();
                closeMobileSearch();
            }
        });

        // ── Mobile search overlay ──
        function openMobileSearch() {
            const overlay = document.getElementById('mobileSearchOverlay');
            if (overlay) {
                overlay.classList.add('active');
                setTimeout(() => { const i = document.getElementById('mobileSearchInput'); if(i) i.focus(); }, 120);
            }
        }
        function closeMobileSearch() {
            const overlay = document.getElementById('mobileSearchOverlay');
            if (overlay) overlay.classList.remove('active');
        }
        const mst = document.getElementById('mobileSearchToggle');
        if (mst) mst.addEventListener('click', openMobileSearch);
    </script>
    <script>
        // Fallback for broken product images
        document.addEventListener('error', function(e) {
            if (e.target.tagName === 'IMG' && !e.target.dataset.fallback) {
                e.target.dataset.fallback = '1';
                e.target.src = '{{ asset("images/no-image.svg") }}';
            }
        }, true);
    </script>
</body>
</html>
