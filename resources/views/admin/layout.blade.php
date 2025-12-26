<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="flex h-screen bg-gray-100">

    <!-- Sidebar -->
    <aside class="w-64 bg-red-600 text-white p-6 flex flex-col">
        <h2 class="text-2xl font-bold mb-8">Admin</h2>
        <nav class="flex flex-col space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:bg-red-700 p-2 rounded">Dashboard</a>
            <a href="{{ route('admin.regular.index') }}" class="hover:bg-red-700 p-2 rounded">Orders</a>
            <a href="{{ route('admin.custom-orders.index') }}" class="hover:bg-red-700 p-2 rounded">Custom Orders</a>
            <a href="{{ route('admin.products.index') }}" class="hover:bg-red-700 p-2 rounded">Products</a>
            <a href="{{ route('admin.users.index') }}" class="hover:bg-red-700 p-2 rounded">Users</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mt-4 hover:bg-red-700 p-2 rounded w-full text-left">Logout</button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 p-6 overflow-auto">
        <header class="mb-6">
            <h1 class="text-3xl font-bold">@yield('title', 'Admin Dashboard')</h1>
        </header>

        <main>
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
