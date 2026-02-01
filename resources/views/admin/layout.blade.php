<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Static Assets (Vite build disabled for Expo frontend) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js for mobile menu toggle - Load before body -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="flex h-screen bg-gray-100" x-data="{ sidebarOpen: false }">

    <!-- Mobile Menu Button -->
    <button @click="sidebarOpen = true" class="md:hidden fixed top-4 left-4 z-50 bg-[#800000] text-white p-3 rounded-lg shadow-lg min-w-[44px] min-h-[44px] flex items-center justify-center hover:bg-[#600000] transition-colors">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-30" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <!-- Sidebar -->
    <aside class="fixed md:static inset-y-0 left-0 z-40 w-64 bg-[#800000] text-white p-6 flex flex-col transform md:transform-none transition-transform duration-300 ease-in-out" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        <!-- Close button for mobile -->
        <button @click="sidebarOpen = false" class="md:hidden absolute top-4 right-4 text-white p-2 min-w-[44px] min-h-[44px] flex items-center justify-center rounded hover:bg-[#600000]">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <h2 class="text-2xl font-bold mb-8">Admin</h2>
        <nav class="flex flex-col space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:bg-[#600000] p-3 rounded min-h-[44px] flex items-center">
                <i class="fas fa-chart-line mr-3"></i> Dashboard
            </a>
            <a href="{{ route('admin.regular.index') }}" class="hover:bg-[#600000] p-3 rounded min-h-[44px] flex items-center">
                <i class="fas fa-shopping-cart mr-3"></i> Orders
            </a>
            <a href="{{ route('admin.custom-orders.index') }}" class="hover:bg-[#600000] p-3 rounded min-h-[44px] flex items-center">
                <i class="fas fa-palette mr-3"></i> Custom Orders
            </a>
            <a href="{{ route('admin.products.index') }}" class="hover:bg-[#600000] p-3 rounded min-h-[44px] flex items-center">
                <i class="fas fa-box mr-3"></i> Products
            </a>
            <a href="{{ route('admin.users.index') }}" class="hover:bg-[#600000] p-3 rounded min-h-[44px] flex items-center">
                <i class="fas fa-users mr-3"></i> Users
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mt-4 hover:bg-[#600000] p-3 rounded w-full text-left min-h-[44px] flex items-center">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 overflow-auto">
        <!-- Header with Search and Profile -->
        <header class="bg-white shadow-sm p-4 md:p-6 mb-4 md:mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h1 class="text-xl md:text-3xl font-bold ml-16 md:ml-0">@yield('title', 'Admin Dashboard')</h1>
                
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Search Bar -->
                    <div class="flex-1 sm:flex-initial sm:min-w-[250px] md:min-w-[300px]">
                        <form action="{{ route('admin.products.index') }}" method="GET">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="search" 
                                    placeholder="Search..."
                                    value="{{ request('search') }}"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent text-sm"
                                >
                                <button type="submit" class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                    <i class="fas fa-search text-gray-400"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Admin Profile -->
                    <div class="flex items-center gap-2 sm:gap-3" x-data="{ profileOpen: false }">
                        <div class="relative">
                            <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#800000] flex items-center justify-center">
                                    <span class="text-white text-sm sm:text-base font-semibold">A</span>
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-semibold text-gray-900">Admin</p>
                                    <p class="text-xs text-gray-500">Administrator</p>
                                </div>
                                <i class="fas fa-chevron-down text-gray-500 text-xs hidden sm:block"></i>
                            </button>
                            
                            <!-- Dropdown -->
                            <div x-show="profileOpen" @click.away="profileOpen = false" x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="transform scale-95 opacity-0"
                                x-transition:enter-end="transform scale-100 opacity-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform scale-100 opacity-100"
                                x-transition:leave-end="transform scale-95 opacity-0"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                                <a href="{{ route('welcome') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-home mr-2"></i>View Site
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 md:p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
