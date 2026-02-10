@extends('layouts.admin')

@section('title', 'Products Report')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#800000] to-[#a52a2a] rounded-2xl p-6 sm:p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold mb-2">Products Report</h1>
                <p class="text-red-100 text-sm sm:text-lg">Product performance and inventory overview</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-white text-sm font-medium transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $totalProducts }}</h3>
            <p class="text-gray-600 text-sm font-medium">Total Products</p>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 text-red-600">{{ $outOfStockCount }}</h3>
            <p class="text-gray-600 text-sm font-medium">Out of Stock</p>
        </div>

        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $totalProducts - $outOfStockCount }}</h3>
            <p class="text-gray-600 text-sm font-medium">In Stock</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Top Selling Products -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Products</h3>
            @if($topProducts->count() > 0)
                <div class="space-y-3">
                    @foreach($topProducts as $index => $item)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <span class="w-6 h-6 flex items-center justify-center bg-[#800000] text-white text-xs font-bold rounded-full">{{ $index + 1 }}</span>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $item->product->name ?? 'Unknown Product' }}</p>
                                <p class="text-xs text-gray-500">₱{{ number_format($item->revenue ?? 0, 2) }} revenue</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-[#800000]">{{ $item->sold }} sold</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p class="font-medium">No sales data yet</p>
                </div>
            @endif
        </div>

        <!-- Out of Stock Items -->
        <div class="bg-white rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Out of Stock Items</h3>
            @if($outOfStockItems->count() > 0)
                <div class="space-y-3">
                    @foreach($outOfStockItems as $product)
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
                        <div>
                            <p class="font-medium text-gray-800 text-sm">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500">₱{{ number_format($product->price ?? 0, 2) }}</p>
                        </div>
                        <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded-full">Out of Stock</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-medium text-green-600">All products are in stock!</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
