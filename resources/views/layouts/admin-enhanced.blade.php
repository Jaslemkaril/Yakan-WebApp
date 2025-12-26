<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Yakan Admin') - Enhanced Admin Panel</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .sidebar-item {
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 4px 8px;
        }

        .sidebar-item:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            transform: translateX(4px);
        }

        .sidebar-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        /* Main Content */
        .main-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            margin: 16px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        /* Card Styles */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            box-shadow: 0 4px 16px 0 rgba(31, 38, 135, 0.2);
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        /* Search Bar */
        .search-bar {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }

        .search-bar:focus {
            background: white;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.2);
        }

        /* Animations */
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

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -256px;
                top: 0;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease;
            }

            .sidebar.open {
                left: 0;
            }

            .main-content {
                margin: 8px;
                border-radius: 8px;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="sidebar w-64 flex-shrink-0" id="sidebar">
            <div class="p-6">
                <!-- Logo -->
                <div class="flex items-center mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-yin-yang text-white text-xl"></i>
                    </div>
                    <span class="ml-3 text-xl font-bold text-gray-800">Yakan Admin</span>
                </div>

                <!-- Navigation -->
                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center px-4 py-3 text-gray-700 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home w-5 mr-3"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.products.index') }}" class="sidebar-item flex items-center px-4 py-3 text-gray-700 {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fas fa-box w-5 mr-3"></i>
                        <span>Products</span>
                    </a>

                    <a href="{{ route('admin.regular.index') }}" class="sidebar-item flex items-center px-4 py-3 text-gray-700 {{ request()->routeIs('admin.regular.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-bag w-5 mr-3"></i>
                        <span>Orders</span>
                        <span class="notification-badge">3</span>
                    </a>

                    <a href="{{ route('admin.custom-orders.index') }}" class="sidebar-item flex items-center px-4 py-3 text-gray-700 {{ request()->routeIs('admin.custom-orders.*') ? 'active' : '' }}">
                        <i class="fas fa-paint-brush w-5 mr-3"></i>
                        <span>Custom Orders</span>
                    </a>

                    <a href="#" class="sidebar-item flex items-center px-4 py-3 text-gray-700">
                        <i class="fas fa-users w-5 mr-3"></i>
                        <span>Customers</span>
                    </a>

                    <a href="#" class="sidebar-item flex items-center px-4 py-3 text-gray-700">
                        <i class="fas fa-chart-line w-5 mr-3"></i>
                        <span>Analytics</span>
                    </a>

                    <a href="#" class="sidebar-item flex items-center px-4 py-3 text-gray-700">
                        <i class="fas fa-landmark w-5 mr-3"></i>
                        <span>Cultural Heritage</span>
                    </a>

                    <a href="#" class="sidebar-item flex items-center px-4 py-3 text-gray-700">
                        <i class="fas fa-cog w-5 mr-3"></i>
                        <span>Settings</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Mobile Menu Toggle -->
                    <button class="md:hidden" onclick="toggleSidebar()">
                        <i class="fas fa-bars text-gray-600"></i>
                    </button>

                    <!-- Search Bar -->
                    <div class="flex-1 max-w-md mx-4">
                        <div class="relative">
                            <input type="text" placeholder="Search products, orders, customers..." 
                                   class="search-bar w-full pl-10 pr-4 py-2 text-sm focus:outline-none">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Right Actions -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-600 hover:text-gray-800 transition-colors">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">5</span>
                        </button>

                        <!-- Messages -->
                        <button class="relative p-2 text-gray-600 hover:text-gray-800 transition-colors">
                            <i class="fas fa-envelope"></i>
                            <span class="notification-badge">2</span>
                        </button>

                        <!-- User Menu -->
                        <div class="relative">
                            <button onclick="toggleUserMenu()" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Settings
                                </a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="main-content flex-1 overflow-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle Sidebar (Mobile)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        // Toggle User Menu
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userMenuButton = event.target.closest('button[onclick="toggleUserMenu()"]');
            
            if (!userMenuButton && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });

        // Add smooth transitions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on load
            const cards = document.querySelectorAll('.glass-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Search functionality
        const searchInput = document.querySelector('.search-bar input');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase();
                // Implement search logic here
                console.log('Searching for:', query);
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
