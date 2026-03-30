@extends('layouts.admin')

@section('title', 'Inventory Details')

@section('content')
@php $authQ = request('auth_token') ? '?auth_token=' . request('auth_token') : ''; @endphp
<div class="space-y-6">
    <div class="bg-gradient-to-r from-red-700 to-[#800000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <a href="{{ route('admin.inventory.index') . $authQ }}" class="inline-flex items-center text-red-100 hover:text-white text-sm mb-3 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
                </a>
                <h1 class="text-3xl font-bold">Inventory Details</h1>
                <p class="text-red-100 mt-1">{{ $inventory->product->name ?? 'Product' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.inventory.edit', $inventory) . $authQ }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white text-[#800000] font-semibold hover:bg-gray-100 transition-colors">
                    <i class="fas fa-edit"></i>Edit Record
                </a>
                <a href="{{ route('admin.products.edit', $inventory->product_id) . $authQ }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/15 border border-white/30 text-white hover:bg-white/25 transition-colors">
                    <i class="fas fa-tag"></i>Edit Product
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Current Quantity</p>
            <p class="text-3xl font-bold mt-1 {{ $inventory->isLowStock() ? 'text-red-600' : 'text-gray-900' }}">{{ $inventory->quantity }}</p>
            <p class="text-sm text-gray-500 mt-2">units available</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Stock Threshold</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $inventory->min_stock_level }} / {{ $inventory->max_stock_level }}</p>
            <p class="text-sm text-gray-500 mt-2">min / max</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Status</p>
            <span class="inline-flex items-center px-3 py-1 mt-2 rounded-full text-sm font-semibold {{ $inventory->stock_status_color }}">
                {{ $inventory->stock_status }}
            </span>
            @if($inventory->last_restocked_at)
                <p class="text-sm text-gray-500 mt-2">Last restocked: {{ $inventory->last_restocked_at->format('M d, Y h:i A') }}</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Product & Inventory Info</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Product Name</p>
                <p class="font-semibold text-gray-900">{{ $inventory->product->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Category</p>
                <p class="font-semibold text-gray-900">{{ $inventory->product->category->name ?? 'No Category' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Cost Price</p>
                <p class="font-semibold text-gray-900">&#8369;{{ number_format($inventory->cost_price ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-500">Selling Price</p>
                <p class="font-semibold text-gray-900">&#8369;{{ number_format($inventory->selling_price ?? ($inventory->product->price ?? 0), 2) }}</p>
            </div>
            <div>
                <p class="text-gray-500">Total Sold</p>
                <p class="font-semibold text-gray-900">{{ $inventory->total_sold ?? 0 }}</p>
            </div>
            <div>
                <p class="text-gray-500">Total Revenue</p>
                <p class="font-semibold text-gray-900">&#8369;{{ number_format($inventory->total_revenue ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-500">Supplier</p>
                <p class="font-semibold text-gray-900">{{ $inventory->supplier ?: 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Location</p>
                <p class="font-semibold text-gray-900">{{ $inventory->location ?: 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-gray-500">Notes</p>
                <p class="font-semibold text-gray-900 whitespace-pre-wrap">{{ $inventory->notes ?: 'No notes added.' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
