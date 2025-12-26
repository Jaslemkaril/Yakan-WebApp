<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Yakan E-commerce Admin</title>
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVJkEZSMUkrQ6usKu8zIvxUsvypLcXdAawO/PzWJNSQsizuX7937ekip6qq3R4gKbjwQZLiqy+EQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Simple CSS instead of Vite -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Enhanced scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(128, 0, 0, 0.3);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(128, 0, 0, 0.5);
        }
        
        /* Smooth transitions */
        .nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #fff;
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(4px);
        }
        
        /* Active state indicator */
        .nav-link-active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #fff;
            color: #fff;
            font-weight: 600;
        }

        .nav-link-active::before {
            opacity: 1;
        }
        
                
        /* Animated gradients */
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .animated-gradient {
            background: linear-gradient(-45deg, #800000, #600000, #a00000, #700000);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        /* Fade in animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Progress bar animations */
        @keyframes progressAnimate {
            from { width: 0%; }
            to { width: var(--progress-width); }
        }
        
        .progress-animate {
            animation: progressAnimate 1s ease-out forwards;
        }
        
        /* Basic layout styles */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            color: #333;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #800000 0%, #600000 100%);
            color: white;
            padding: 20px 0;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
        }
        
        .grid {
            display: grid;
            gap: 20px;
        }
        
        .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
        .grid-cols-5 { grid-template-columns: repeat(5, 1fr); }
        
        @media (max-width: 1200px) {
            .grid-cols-4 { grid-template-columns: repeat(3, 1fr); }
            .grid-cols-5 { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 768px) {
            .grid-cols-4, .grid-cols-5 { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 480px) {
            .grid-cols-4, .grid-cols-5 { grid-template-columns: 1fr; }
        }
        
        /* Card hover effects */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(128, 0, 0, 0.15);
        }

        /* Stat card styles */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid #800000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(128, 0, 0, 0.12);
        }

        .stat-card h3 {
            font-size: 14px;
            color: #666;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: 700;
            color: #800000;
        }

        .stat-card .subtitle {
            font-size: 12px;
            color: #999;
            margin-top: 8px;
        }
        
        /* Responsive Sidebar Styles */
        .sidebar-overlay {
            @apply fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        
        .sidebar-overlay.active {
            @apply block;
            opacity: 1;
        }
        
        .sidebar-collapsed {
            width: 4rem !important;
        }
        
        .sidebar-collapsed .sidebar-text {
            @apply hidden;
        }
        
        .sidebar-collapsed .sidebar-logo {
            @apply justify-center;
        }
        
        .sidebar-collapsed .sidebar-logo-text {
            @apply hidden;
        }
        
        /* Enhanced Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar-mobile {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            }
            
            .sidebar-mobile.open {
                transform: translateX(0);
            }
            
            /* Touch-friendly menu items */
            .menu-item {
                min-height: 3.5rem;
                padding: 1rem;
            }
            
            /* Mobile search adjustments */
            .mobile-search {
                display: block;
            }
            
            .desktop-search {
                display: none;
            }
        }
        
        /* Tablet Responsive */
        @media (min-width: 768px) and (max-width: 1024px) {
            .sidebar-mobile {
                width: 16rem;
            }
            
            .sidebar-collapsed {
                width: 3.5rem !important;
            }
        }
        
        /* Desktop Responsive */
        @media (min-width: 1024px) {
            .sidebar-mobile {
                position: static;
                transform: none !important;
            }
            
            .sidebar-overlay {
                display: none !important;
            }
        }
        
        /* Mobile menu animations */
        .menu-item {
            transition: all 0.2s ease-in-out;
        }
        
        .menu-item:hover {
            transform: translateX(4px);
        }
        
        /* Touch feedback for mobile */
        @media (max-width: 768px) {
            .menu-item:active {
                background-color: rgba(59, 130, 246, 0.1);
                transform: scale(0.98);
            }
        }
        
        /* Improved mobile header */
        @media (max-width: 768px) {
            .mobile-header {
                padding: 1rem;
            }
            
            .mobile-menu-toggle {
                padding: 0.75rem;
                border-radius: 0.5rem;
            }
        }
        
        /* Icon animations */
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .animate-spin-slow {
            animation: spin-slow 3s linear infinite;
        }
        
        @keyframes bounce-gentle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
        /* Card hover effects */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Responsive Sidebar Styles */
        .sidebar-overlay {
            @apply fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        
        .sidebar-overlay.active {
            @apply block;
            opacity: 1;
        }
        
        .sidebar-collapsed {
            width: 4rem !important;
        }
        
        .sidebar-collapsed .sidebar-text {
            @apply hidden;
        }
        
        .sidebar-collapsed .sidebar-logo {
            @apply justify-center;
        }
        
        .sidebar-collapsed .sidebar-logo-text {
            @apply hidden;
        }
        
        /* Enhanced Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar-mobile {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            }
            
            .sidebar-mobile.open {
                transform: translateX(0);
            }
            
            /* Touch-friendly menu items */
            .menu-item {
                min-height: 3.5rem;
                padding: 1rem;
            }
            
            /* Mobile search adjustments */
            .mobile-search {
                display: block;
            }
            
            .desktop-search {
                display: none;
            }
        }
        
        /* Tablet Responsive */
        @media (min-width: 768px) and (max-width: 1024px) {
            .sidebar-mobile {
                width: 16rem;
            }
            
            .sidebar-collapsed {
                width: 3.5rem !important;
            }
        }
        
        /* Desktop Responsive */
        @media (min-width: 1024px) {
            .sidebar-mobile {
                position: static;
                transform: none !important;
            }
            
            .sidebar-overlay {
                display: none !important;
            }
        }
        
        /* Mobile menu animations */
        .menu-item {
            transition: all 0.2s ease-in-out;
        }
        
        .menu-item:hover {
            transform: translateX(4px);
        }
        
        /* Touch feedback for mobile */
        @media (max-width: 768px) {
            .menu-item:active {
                background-color: rgba(59, 130, 246, 0.1);
                transform: scale(0.98);
            }
        }
        
        /* Improved mobile header */
        @media (max-width: 768px) {
            .mobile-header {
                padding: 1rem;
            }
            
            .mobile-menu-toggle {
                padding: 0.75rem;
                border-radius: 0.5rem;
            }
        }
        
        /* Icon animations */
        @keyframes spin-slow {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .animate-spin-slow {
            animation: spin-slow 3s linear infinite;
        }
        
        @keyframes bounce-gentle {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-3px);
            }
        }
        
        .animate-bounce-gentle {
            animation: bounce-gentle 2s ease-in-out infinite;
        }
        
        /* Icon hover effects */
        .icon-hover {
            transition: all 0.3s ease;
        }
        
        .icon-hover:hover {
            transform: scale(1.1);
            filter: brightness(1.2);
        }
        
        /* Backdrop blur for mobile */
        .mobile-backdrop {
            backdrop-filter: blur(4px);
        }

        /* Button styles */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: #800000;
            color: white;
        }

        .btn-primary:hover {
            background: #600000;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e8e8e8;
            border-color: #999;
        }

        /* Enhanced Input Focus States */
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #800000 !important;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1) !important;
        }

        /* Smooth Transitions */
        * {
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        /* Tooltip Styles */
        [title] {
            position: relative;
        }

        /* Loading State */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Success/Error Messages */
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            animation: slideInDown 0.3s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        /* Table enhancements */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
        }

        table th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        table tbody tr {
            transition: all 0.2s ease;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Header styles */
        .page-header {
            margin-bottom: 30px;
            animation: slideInLeft 0.5s ease-out;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #666;
            font-size: 14px;
        }

        /* Filter section */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .filter-section h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Input styles */
        input[type="text"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #800000;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }

        /* Loading animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Smooth page transitions */
        .page-transition {
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .slide-in-left {
            animation: slideInLeft 0.5s ease-out;
        }
    </style>
</head>
<body class="font-sans antialiased" x-data="{ 
    sidebarOpen: false, 
    sidebarCollapsed: false, 
    darkMode: false,
    isMobile: window.innerWidth < 768,
    isTablet: window.innerWidth >= 768 && window.innerWidth < 1024,
    init() {
        this.checkScreenSize();
        window.addEventListener('resize', () => this.checkScreenSize());
        
        // Auto-collapse sidebar on small screens
        if (window.innerWidth < 1024) {
            this.sidebarCollapsed = true;
        }
        
        // Close sidebar on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.sidebarOpen) {
                this.sidebarOpen = false;
            }
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (this.isMobile && this.sidebarOpen) {
                const sidebar = document.querySelector('aside');
                const menuToggle = e.target.closest('button');
                
                if (!sidebar.contains(e.target) && !menuToggle) {
                    this.sidebarOpen = false;
                }
            }
        });
    },
    checkScreenSize() {
        this.isMobile = window.innerWidth < 768;
        this.isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
        
        // Auto-adjust sidebar based on screen size
        if (window.innerWidth < 768) {
            this.sidebarOpen = false;
        } else if (window.innerWidth >= 1024) {
            this.sidebarOpen = false; // Desktop doesn't need overlay
        }
    },
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
    },
    toggleCollapse() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
    }
}">
    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" 
         :class="{ 'active': sidebarOpen }" 
         @click="sidebarOpen = false"></div>
    
    <div class="flex min-h-screen">
        <!-- Enhanced Responsive Sidebar -->
        <aside class="sidebar-mobile w-72 bg-gradient-to-b shadow-2xl border-r flex flex-col fixed md:static inset-y-0 left-0 z-40 transition-all duration-300 ease-in-out" 
               style="background: linear-gradient(135deg, #800000 0%, #600000 100%); border-right-color: #600000;" 
               :class="{ 
                   'open': sidebarOpen,
                   'sidebar-collapsed': sidebarCollapsed && !sidebarOpen,
                   '-translate-x-full': !sidebarOpen && window.innerWidth < 768,
                   'translate-x-0': sidebarOpen || window.innerWidth >= 768
               }">
            <!-- Logo/Brand Section -->
            <div class="sidebar-logo p-6 border-b border-gray-200 bg-[#800000]">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm flex-shrink-0">
                        <i class="fas fa-store text-white text-xl"></i>
                    </div>
                    <div class="sidebar-logo-text">
                        <h1 class="text-xl font-bold text-white">Yakan Admin</h1>
                        <p class="text-xs text-white/80">E-commerce Platform</p>
                    </div>
                </div>
            </div>

           <!-- Navigation -->
<nav class="flex-1 p-4 space-y-1 overflow-y-auto custom-scrollbar">
    <a href="{{ route('admin.dashboard') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg group {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}" style="color: white;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
        <svg class="w-5 h-5 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(255,255,255,0.7);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4m0 0l4 4m-4-4v4"/></svg>
        <span class="sidebar-text font-medium" style="color: rgba(255,255,255,0.9);">Dashboard</span>
    </a>

    <a href="{{ route('admin.regular.index') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg group {{ request()->routeIs('admin.regular.*') ? 'nav-link-active' : '' }}" style="color: white; position: relative;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
        <svg class="w-5 h-5 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(255,255,255,0.7);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        <span class="sidebar-text font-medium" style="color: rgba(255,255,255,0.9);">Orders</span>
        @if($pendingOrdersCount > 0)
            <span style="position: absolute; top: -8px; right: -8px; background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%); color: white; font-size: 11px; font-weight: bold; padding: 2px 6px; border-radius: 12px; min-width: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3); z-index: 10; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                {{ $pendingOrdersCount > 9 ? '9+' : $pendingOrdersCount }}
            </span>
        @endif
    </a>

    <a href="{{ route('admin.patterns.index') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg group {{ request()->routeIs('admin.patterns.*') ? 'nav-link-active' : '' }}" style="color: white;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
        <svg class="w-5 h-5 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(255,255,255,0.7);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
        <span class="sidebar-text font-medium" style="color: rgba(255,255,255,0.9);">Patterns</span>
    </a>

    <a href="{{ route('admin.custom_orders.index') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg group {{ request()->routeIs('admin.custom_orders.*') ? 'nav-link-active' : '' }}" style="color: white;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
        <svg class="w-5 h-5 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(255,255,255,0.7);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
        <span class="sidebar-text font-medium" style="color: rgba(255,255,255,0.9);">Custom Orders</span>
    </a>

    <a href="{{ route('admin.products.index') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg group {{ request()->routeIs('admin.products.*') ? 'nav-link-active' : '' }}" style="color: white;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
        <svg class="w-5 h-5 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(255,255,255,0.7);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m0 0v10l8 4"/></svg>
        <span class="sidebar-text font-medium" style="color: rgba(255,255,255,0.9);">Products</span>
    </a>

    <a href="{{ route('admin.coupons.index') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg group {{ request()->routeIs('admin.coupons.*') ? 'nav-link-active' : '' }}" style="color: white;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
        <svg class="w-5 h-5 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(255,255,255,0.7);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
        <span class="sidebar-text font-medium" style="color: rgba(255,255,255,0.9);">Coupons</span>
    </a>

    <a href="{{ route('admin.cultural-heritage.index') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg group {{ request()->routeIs('admin.cultural-heritage.*') ? 'nav-link-active' : '' }}" style="color: white;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
        <svg class="w-5 h-5 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(255,255,255,0.7);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"/></svg>
        <span class="sidebar-text font-medium" style="color: rgba(255,255,255,0.9);">Cultural Heritage</span>
    </a>

    <a href="{{ route('admin.users.index') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg group {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}" style="color: white;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
        <svg class="w-5 h-5 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(255,255,255,0.7);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        <span class="sidebar-text font-medium" style="color: rgba(255,255,255,0.9);">Users</span>
    </a>

    <a href="{{ route('admin.chats.index') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg group {{ request()->routeIs('admin.chats.*') ? 'nav-link-active' : '' }}" style="color: white; position: relative;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
        <svg class="w-5 h-5 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(255,255,255,0.7);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <span class="sidebar-text font-medium" style="color: rgba(255,255,255,0.9);">Chat</span>
        @php
            $unreadChatsCount = \App\Models\Chat::whereHas('messages', function ($query) {
                $query->where('sender_type', 'user')->where('is_read', false);
            })->count();
        @endphp
        @if($unreadChatsCount > 0)
            <span style="position: absolute; top: -8px; right: -8px; background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%); color: white; font-size: 11px; font-weight: bold; padding: 2px 6px; border-radius: 12px; min-width: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3); z-index: 10; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                {{ $unreadChatsCount > 9 ? '9+' : $unreadChatsCount }}
            </span>
        @endif
    </a>

    <!-- <a href="{{ route('admin.analytics') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.analytics') ? 'nav-link-active' : '' }}">
        <i class="fas fa-chart-line w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors flex-shrink-0"></i>
        <span class="sidebar-text font-medium text-gray-700 group-hover:text-gray-900">Analytics</span>
    </a> -->
    
    <!-- Settings placeholder - can be added later -->
    <div class="pt-4 mt-4 border-t border-gray-200">
        <div class="px-4 py-2">
            <span class="sidebar-text text-xs font-semibold text-gray-400 uppercase tracking-wider">System</span>
        </div>
        <!-- <a href="{{ route('admin.settings.general') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.settings.*') ? 'nav-link-active' : '' }}">
            <i class="fas fa-cog text-gray-400 group-hover:text-gray-600 w-5"></i>
            <span class="sidebar-text font-medium text-gray-500 group-hover:text-gray-700">Settings</span>
        </a> -->
    </div>

    <!-- Divider -->
    <div class="pt-4 pb-2">
        <div class="h-px bg-gray-200"></div>
    </div>

    <!-- Quick Actions -->
    <div class="px-4 py-2 mt-4">
        <span class="sidebar-text text-xs font-semibold text-gray-400 uppercase tracking-wider">Quick Actions</span>
    </div>
    <a href="{{ route('admin.products.create') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-100 group">
        <i class="fas fa-plus-circle text-gray-400 group-hover:text-green-600 w-4"></i>
        <span class="sidebar-text text-sm font-medium text-gray-600 group-hover:text-gray-900">Add Product</span>
    </a>
    <!-- <a href="{{ route('admin.inventory.create') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-100 group">
            <i class="fas fa-plus w-5 h-5 text-gray-400 group-hover:text-green-500 transition-colors flex-shrink-0"></i>
            <span class="sidebar-text font-medium text-gray-700 group-hover:text-gray-900">Add Inventory</span>
        </a> -->
    <a href="{{ route('admin.users.create') }}" class="menu-item nav-link flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-100 group">
        <i class="fas fa-user-plus text-gray-400 group-hover:text-blue-600 w-4"></i>
        <span class="sidebar-text text-sm font-medium text-gray-600 group-hover:text-gray-900">Add User</span>
    </a>
</nav>

            <!-- User Profile & Logout Section -->
<div class="p-4 border-t border-gray-200">
    <!-- Desktop Collapse Toggle -->
    <button @click="sidebarCollapsed = !sidebarCollapsed" 
            class="hidden md:flex items-center justify-center w-full p-2 mb-3 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            title="Toggle Sidebar">
        <i class="fas fa-chevron-left" :class="{ 'fa-chevron-right': sidebarCollapsed, 'fa-chevron-left': !sidebarCollapsed }"></i>
    </button>
    
    <div class="bg-gray-50 rounded-lg p-3 mb-3">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-white">AD</span>
            </div>
            <div class="sidebar-text flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()?->name ?? 'Admin User' }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()?->email ?? 'admin@example.com' }}</p>
            </div>
        </div>
    </div>
    
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 transition-all group">
            <i class="fas fa-sign-out-alt w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
            <span class="font-medium">Logout</span>
        </button>
    </form>
</div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden transition-all duration-300 ease-in-out"
              :class="{ 'ml-0': sidebarCollapsed && window.innerWidth >= 768, 'md:ml-0': !sidebarCollapsed && window.innerWidth >= 768 }">
            <!-- Top Header Bar -->
            <header class="bg-white shadow-sm border-b-4 mobile-header transition-all duration-300" style="border-bottom-color: #800000;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <!-- Mobile Menu Toggle -->
                        <button class="mobile-menu-toggle md:hidden inline-flex items-center p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" 
                                @click="toggleSidebar()"
                                :class="{ 'text-blue-600': sidebarOpen }">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        
                        <!-- Tablet Sidebar Toggle -->
                        <button class="hidden md:inline-flex lg:hidden items-center p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" 
                                @click="toggleCollapse()"
                                :class="{ 'text-blue-600': sidebarCollapsed }">
                            <i class="fas fa-bars w-5 h-5"></i>
                        </button>
                        
                        <!-- Desktop Sidebar Toggle -->
                        <button class="hidden lg:inline-flex items-center p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" 
                                @click="toggleCollapse()"
                                :class="{ 'text-blue-600': sidebarCollapsed }">
                            <i class="fas fa-bars w-5 h-5"></i>
                        </button>
                        
                        <div class="min-w-0 flex-1">
                            <h1 class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900 truncate">@yield('title', 'Dashboard')</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <!-- Search Bar (Desktop) -->
                        <div class="desktop-search hidden lg:flex items-center">
                            <div class="relative">
                                <input type="text" 
                                       placeholder="Search..." 
                                       class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <!-- Mobile Search Button -->
                        <button class="mobile-search lg:hidden p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                @click="$refs.mobileSearchModal.show()">
                            <i class="fas fa-search w-5 h-5"></i>
                        </button>
                        
                        <!-- Notification Bell -->
                        @include('components.admin-notification-dropdown')
                        
                        <!-- User Menu (Mobile) -->
                        <div class="md:hidden">
                            <button class="flex items-center space-x-2 p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                    @click="$refs.mobileUserMenu.toggle()">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold text-white">AD</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="flex-1 overflow-auto p-6">
                <!-- Session Messages -->
                @if(session('success'))
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg mb-6 shadow-sm flex items-start">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium">Success</p>
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg mb-6 shadow-sm flex items-start">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium">Error</p>
                            <p class="text-sm">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Page Content -->
                <div>
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
    
    <!-- Mobile Search Modal -->
    <div x-ref="mobileSearchModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden lg:hidden" @click="$event.target === $el && $el.hide()">
        <div class="bg-white p-4" @click.stop>
            <div class="flex items-center space-x-3 mb-4">
                <button @click="$refs.mobileSearchModal.hide()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-600"></i>
                </button>
                <h3 class="text-lg font-semibold">Search</h3>
            </div>
            <div class="relative">
                <input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
    </div>
    
    <!-- Mobile User Menu -->
    <div x-ref="mobileUserMenu" class="fixed top-16 right-4 bg-white rounded-lg shadow-lg border border-gray-200 p-2 z-50 hidden md:hidden" @click.away="$el.hide()">
        <div class="py-2">
            <div class="px-4 py-2 border-b border-gray-200">
                <p class="text-sm font-medium text-gray-900">{{ auth()->user()?->name ?? 'Admin User' }}</p>
                <p class="text-xs text-gray-500">{{ auth()->user()?->email ?? 'admin@example.com' }}</p>
            </div>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">Profile</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">Settings</a>
            <form method="POST" action="{{ route('admin.logout') }}" class="block">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">Logout</button>
            </form>
        </div>
    </div>

    <!-- Responsive Sidebar JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle window resize events for manual adjustments if needed
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    // Any manual resize adjustments can go here
                    console.log('Window resized to:', window.innerWidth);
                }, 250);
            });
            
            // Initialize mobile modals
            const mobileSearchModal = document.querySelector('[x-ref="mobileSearchModal"]');
            const mobileUserMenu = document.querySelector('[x-ref="mobileUserMenu"]');
            
            if (mobileSearchModal) {
                mobileSearchModal.hide = function() {
                    this.classList.add('hidden');
                };
                mobileSearchModal.show = function() {
                    this.classList.remove('hidden');
                };
            }
            
            if (mobileUserMenu) {
                mobileUserMenu.hide = function() {
                    this.classList.add('hidden');
                };
                mobileUserMenu.show = function() {
                    this.classList.remove('hidden');
                };
                mobileUserMenu.toggle = function() {
                    this.classList.toggle('hidden');
                };
            }
        });
    </script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>