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
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Inter', sans-serif;
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

        /* Navigation Enhancements */
        .nav-link {
            position: relative;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(var(--primary), 0.1);
            color: rgb(var(--primary));
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, rgb(var(--primary)), rgb(var(--accent)));
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 80%;
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
<body class="antialiased">
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
                    <a href="{{ route('welcome') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 bg-gradient-to-br from-maroon-600 to-maroon-700 rounded-xl flex items-center justify-center transform group-hover:rotate-12 transition-transform" style="background: linear-gradient(to bottom right, #800000, #600000);">
                            <span class="text-white font-bold text-xl">Y</span>
                        </div>
                        <span class="text-2xl font-bold text-gradient">Yakan</span>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="hidden md:flex flex-1 max-w-2xl mx-8">
                    <form action="{{ route('products.search') }}" method="GET" class="w-full" id="searchForm">
                        <div class="relative">
                            <input 
                                type="text" 
                                name="q" 
                                id="searchInput"
                                placeholder="Search products..." 
                                value="{{ request('q') }}"
                                class="w-full pl-12 pr-4 py-2.5 border-2 border-gray-200 rounded-full focus:outline-none focus:border-maroon-600 transition-all"
                                style="border-color: #e5e7eb;"
                                autocomplete="off"
                            >
                            <button type="submit" class="absolute left-4 top-1/2 transform -translate-y-1/2 hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-gray-400 hover:text-maroon-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #9ca3af;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('welcome') }}" class="nav-link whitespace-nowrap">Home</a>
                    <a href="{{ route('products.index') }}" class="nav-link whitespace-nowrap">Products</a>
                    <a href="{{ route('custom_orders.index') }}" class="nav-link whitespace-nowrap">Custom Orders</a>
                    <a href="{{ route('cultural-heritage.index') }}" class="nav-link whitespace-nowrap">Cultural Heritage</a>
                    <a href="{{ route('track-order.index') }}" class="nav-link whitespace-nowrap">Track Order</a>
                    @auth
                        <a href="{{ route('chats.index') }}" class="nav-link whitespace-nowrap flex items-center gap-2 relative">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Support
                            @if($unreadChatCount > 0)
                                <span style="position: absolute; top: -8px; right: -8px; background-color: #800000; color: white; font-size: 10px; font-weight: bold; padding: 2px 5px; border-radius: 10px; min-width: 18px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3); z-index: 10;">
                                    {{ $unreadChatCount > 9 ? '9+' : $unreadChatCount }}
                                </span>
                            @endif
                        </a>
                    @endauth
                </div>

                <!-- Wishlist Icon -->
                @auth
                    <a href="{{ route('wishlist.index') }}" class="p-2 rounded-lg hover:bg-gray-100 transition-colors relative" title="My Wishlist">
                        <svg class="w-6 h-6 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b3a56;" onmouseover="this.style.color='#7a3350'" onmouseout="this.style.color='#8b3a56'">
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

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- Cart -->
                        <a href="{{ route('cart.index') }}" class="relative group">
                            <div class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <svg class="w-6 h-6 text-gray-700 group-hover:text-maroon-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="--tw-text-opacity: 1;">
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
                            <button @click="open = !open" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover border-2 border-maroon-600" loading="lazy">
                                @else
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: linear-gradient(to bottom right, #800000, #600000);">
                                        <span class="text-white text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <a href="{{ route('login') }}" class="btn-secondary">Login</a>
                        <a href="{{ route('register') }}" class="btn-primary">Sign Up</a>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform scale-95 opacity-0" x-transition:enter-end="transform scale-100 opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform scale-100 opacity-100" x-transition:leave-end="transform scale-95 opacity-0" class="md:hidden py-4 border-t border-gray-100">
                <!-- Mobile Search -->
                <div class="px-4 mb-4">
                    <form action="{{ route('products.search') }}" method="GET" id="mobileSearchForm">
                        <div class="relative">
                            <input 
                                type="text" 
                                name="q" 
                                id="mobileSearchInput"
                                placeholder="Search products..." 
                                value="{{ request('q') }}"
                                class="w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-full focus:outline-none focus:border-maroon-600"
                                autocomplete="off"
                            >
                            <button type="submit" class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="flex flex-col space-y-3 px-4">
                    <a href="{{ route('welcome') }}" class="nav-link">Home</a>
                    <a href="{{ route('products.index') }}" class="nav-link">Products</a>
                    <a href="{{ route('custom_orders.index') }}" class="nav-link">Custom Orders</a>
                    <a href="{{ route('cultural-heritage.index') }}" class="nav-link">Cultural Heritage</a>
                    <a href="{{ route('track-order.index') }}" class="nav-link">Track Order</a>
                    @auth
                        <a href="{{ route('wishlist.index') }}" class="nav-link">Wishlist</a>
                        <a href="{{ route('chats.index') }}" class="nav-link flex items-center gap-2 relative">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Support Chat
                            @if($unreadChatCount > 0)
                                <span style="position: absolute; top: -8px; right: -8px; background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%); color: white; font-size: 10px; font-weight: bold; padding: 2px 5px; border-radius: 10px; min-width: 18px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3); z-index: 10;">
                                    {{ $unreadChatCount > 9 ? '9+' : $unreadChatCount }}
                                </span>
                            @endif
                        </a>
                    @endauth
                    @guest
                        <a href="{{ route('login') }}" class="nav-link">Login</a>
                        <a href="{{ route('register') }}" class="nav-link">Sign Up</a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

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

        // Search form validation and feedback
        function initSearchForm(formId, inputId) {
            const searchForm = document.getElementById(formId);
            const searchInput = document.getElementById(inputId);
            
            if (searchForm && searchInput) {
                searchForm.addEventListener('submit', function(e) {
                    const query = searchInput.value.trim();
                    
                    // Allow empty search to show all products
                    if (query === '') {
                        // Redirect to all products
                        e.preventDefault();
                        window.location.href = '{{ route("products.index") }}';
                        return;
                    }
                    
                    // Show loading state
                    const submitBtn = searchForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const svg = submitBtn.querySelector('svg');
                        if (svg) {
                            svg.classList.add('animate-spin');
                        }
                    }
                    
                    console.log('Searching for:', query);
                    console.log('Form action:', searchForm.action);
                });
                
                // Add Enter key support
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchForm.submit();
                    }
                });
            }
        }
        
        // Initialize both desktop and mobile search
        initSearchForm('searchForm', 'searchInput');
        initSearchForm('mobileSearchForm', 'mobileSearchInput');
    </script>
</body>
</html>
