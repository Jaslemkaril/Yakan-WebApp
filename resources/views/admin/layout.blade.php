<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Static Assets (Vite build disabled for Expo frontend) -->
    @stack('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js for mobile menu toggle -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="flex h-screen bg-gray-100" x-data="{ sidebarOpen: false }">

    <!-- Mobile Menu Button -->
    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden fixed top-4 left-4 z-50 bg-red-600 text-white p-3 rounded-lg shadow-lg">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-30" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <!-- Sidebar -->
    <aside class="fixed md:static inset-y-0 left-0 z-40 w-64 bg-red-600 text-white p-6 flex flex-col transform md:transform-none transition-transform duration-300 ease-in-out" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        <!-- Close button for mobile -->
        <button @click="sidebarOpen = false" class="md:hidden absolute top-4 right-4 text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <h2 class="text-2xl font-bold mb-8">Admin</h2>
        <nav class="flex flex-col space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:bg-red-700 p-3 rounded min-h-[44px] flex items-center">Dashboard</a>
            <a href="{{ route('admin.regular.index') }}" class="hover:bg-red-700 p-3 rounded min-h-[44px] flex items-center">Orders</a>
            <a href="{{ route('admin.custom-orders.index') }}" class="hover:bg-red-700 p-3 rounded min-h-[44px] flex items-center">Custom Orders</a>
            <a href="{{ route('admin.products.index') }}" class="hover:bg-red-700 p-3 rounded min-h-[44px] flex items-center">Products</a>
            <a href="{{ route('admin.users.index') }}" class="hover:bg-red-700 p-3 rounded min-h-[44px] flex items-center">Users</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mt-4 hover:bg-red-700 p-3 rounded w-full text-left min-h-[44px]">Logout</button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 overflow-auto">
        <!-- Mobile Header with padding for hamburger button -->
        <header class="bg-white shadow-sm p-4 md:p-6 mb-4 md:mb-6">
            <h1 class="text-xl md:text-3xl font-bold ml-12 md:ml-0">@yield('title', 'Admin Dashboard')</h1>
        </header>

        <main class="p-4 md:p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
