@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    {{-- Header --}}
    <div class="mb-12 animate-fade-in-up">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-gradient mb-4">My Account</h1>
        <p class="text-xl text-gray-600">Welcome back, {{ isset($user) && $user ? (str_contains($user->name, ' ') ? explode(' ', $user->name)[0] : $user->name) : 'User' }}! Manage your profile and orders.</p>
    </div>

    {{-- Full-Width Search Bar --}}
    <div class="mb-8 animate-fade-in-up">
        <div class="w-full relative">
            <form action="{{ route('search') }}" method="GET" class="relative">
                <div class="relative">
                    <input 
                        type="text" 
                        name="q" 
                        placeholder="Search products, orders, or anything..." 
                        class="w-full px-6 py-4 pl-14 text-lg bg-white border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-[#800000] focus:ring-4 focus:ring-[#800000]/10 transition-all duration-300 shadow-sm hover:shadow-md"
                        id="dashboardSearchInput"
                    >
                    <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button 
                        type="submit" 
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 px-6 py-2 bg-gradient-to-r from-[#800000] to-[#4b0000] text-white font-semibold rounded-xl hover:from-[#4b0000] hover:to-[#2c0000] transition-all duration-300 shadow-md hover:shadow-lg"
                    >
                        Search
                    </button>
                </div>
            </form>
            
            {{-- Live Search Dropdown --}}
            <div id="dashboardLiveSearchResults" class="hidden absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 max-h-96 overflow-y-auto">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8 animate-fade-in-up">
            {{-- Account Overview --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden card">
                <div class="hero-gradient px-8 py-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Account Overview
                    </h2>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="group">
                            <label class="block text-sm font-semibold text-gray-500 mb-2">Full Name</label>
                            <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <p class="font-medium text-gray-900">{{ isset($user) && $user ? $user->name : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="group">
                            <label class="block text-sm font-semibold text-gray-500 mb-2">Email Address</label>
                            <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                </svg>
                                <p class="font-medium text-gray-900">{{ isset($user) && $user ? $user->email : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-[#800000] to-[#4b0000] text-white font-semibold rounded-xl hover:from-[#4b0000] hover:to-[#2c0000] transform hover:scale-105 transition-all shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden card">
                <div class="hero-gradient px-8 py-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Quick Actions
                    </h2>
                </div>
                <div class="p-8">
                    @if(isset($user) && $user && $user->role === 'admin')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <a href="{{ route('admin.dashboard') }}" class="group bg-gradient-to-br from-[#fff5f5] to-[#ffe5e5] border border-[#f4c7c7] rounded-2xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-[#800000] to-[#4b0000] rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">Admin Dashboard</h3>
                                        <p class="text-gray-600 text-sm">System overview</p>
                                    </div>
                                </div>
                                <div class="flex items-center text-[#800000] font-semibold">
                                    <span>Manage System</span>
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                            <a href="{{ route('admin.regular.index') }}" class="group bg-gradient-to-br from-[#fef2f2] to-[#fde8e8] border border-[#f4c7c7] rounded-2xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-[#800000] to-[#4b0000] rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">Manage Orders</h3>
                                        <p class="text-gray-600 text-sm">All customer orders</p>
                                    </div>
                                </div>
                                <div class="flex items-center text-[#800000] font-semibold">
                                    <span>View Orders</span>
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <a href="{{ route('custom_orders.index') }}" class="group bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-[#800000] to-[#4b0000] rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">Custom Orders</h3>
                                        <p class="text-gray-600 text-sm">Requests & status</p>
                                    </div>
                                </div>
                                <div class="flex items-center text-[#800000] font-semibold">
                                    <span>View Requests</span>
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                            <a href="{{ route('custom_orders.create') }}" class="group bg-gradient-to-br from-[#fff5f5] to-[#ffe5e5] border border-[#f4c7c7] rounded-2xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-[#800000] to-[#4b0000] rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">Create Order</h3>
                                        <p class="text-gray-600 text-sm">Custom product</p>
                                    </div>
                                </div>
                                <div class="flex items-center text-[#800000] font-semibold">
                                    <span>Create Now</span>
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                            <a href="{{ route('cart.index') }}" class="group bg-gradient-to-br from-[#fff5f5] to-[#fdeaea] border border-[#f4c7c7] rounded-2xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-[#800000] to-[#4b0000] rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">My Cart</h3>
                                        <p class="text-gray-600 text-sm">Checkout items</p>
                                    </div>
                                </div>
                                <div class="flex items-center text-[#800000] font-semibold">
                                    <span>View Cart</span>
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                            <a href="{{ route('track-order.index') }}" class="group bg-gradient-to-br from-[#fff5f5] to-[#ffe5e5] border border-[#f4c7c7] rounded-2xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-[#800000] to-[#4b0000] rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">Track Order</h3>
                                        <p class="text-gray-600 text-sm">By tracking no.</p>
                                    </div>
                                </div>
                                <div class="flex items-center text-[#800000] font-semibold">
                                    <span>Track Now</span>
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-8 animate-fade-in-up">
            {{-- My Orders Card - Moved to top right -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden card">
                <div class="hero-gradient px-8 py-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        My Orders
                    </h2>
                </div>
                <div class="p-8">
                    <p class="text-gray-600 mb-6">View and track all your orders in one place.</p>
                    <a href="{{ route('orders.index') }}" class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-[#800000] to-[#4b0000] text-white font-semibold rounded-xl hover:from-[#4b0000] hover:to-[#2c0000] transform hover:scale-105 transition-all shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        View All Orders
                    </a>
                </div>
            </div>
            {{-- Support Card --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden card">
                <div class="hero-gradient px-8 py-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 109.75 9.75 9.75 9.75 0 00-9.75-9.75z"/>
                        </svg>
                        Support
                    </h2>
                </div>
                <div class="p-8">
                    <p class="text-gray-600 mb-6">Need help? Our support team is here to assist you with any questions or concerns.</p>
                    <a href="{{ route('contact') }}" class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-[#800000] to-[#4b0000] text-white font-semibold rounded-xl hover:from-[#4b0000] hover:to-[#2c0000] transform hover:scale-105 transition-all shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Contact Support
                    </a>
                </div>
            </div>

            {{-- Security Card --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden card">
                <div class="hero-gradient px-8 py-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Security
                    </h2>
                </div>
                <div class="p-8">
                    <p class="text-gray-600 mb-6">Keep your account secure by regularly updating your password and reviewing your account activity.</p>
                    <a href="{{ route('password.request') }}" class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-[#800000] to-[#4b0000] text-white font-semibold rounded-xl hover:from-[#4b0000] hover:to-[#2c0000] transform hover:scale-105 transition-all shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Reset Password
                    </a>
                </div>
            </div>

            {{-- Logout Card --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden card">
                <div class="hero-gradient px-8 py-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </h2>
                </div>
                <div class="p-8">
                    <p class="text-gray-600 mb-6">Sign out of your account securely. You can always log back in anytime.</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-[#800000] to-[#4b0000] text-white font-semibold rounded-xl hover:from-[#4b0000] hover:to-[#2c0000] transform hover:scale-105 transition-all shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard live search functionality - DISABLED for performance
let dashboardSearchTimeout;
const dashboardSearchInput = document.getElementById('dashboardSearchInput');
const dashboardLiveSearchResults = document.getElementById('dashboardLiveSearchResults');

// Temporarily disable live search to improve page load performance
if (dashboardSearchInput) {
    // Remove the live search functionality for now
    // Users can still use the regular search form
    console.log('Live search disabled for performance optimization');
    
    // Hide the live search results container
    if (dashboardLiveSearchResults) {
        dashboardLiveSearchResults.style.display = 'none';
    }
}
</script>
@endsection