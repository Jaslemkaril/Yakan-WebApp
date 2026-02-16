@extends('layouts.admin')

@section('title', 'Edit Inventory Record')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center text-maroon-600 hover:text-maroon-800 font-bold mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Inventory
            </a>
            <h1 class="text-3xl font-black text-gray-900">Edit Inventory Record</h1>
            <p class="text-gray-600 mt-2">Update inventory information for {{ $inventory->product->name }}</p>
        </div>

        <!-- Product Info Card -->
        <div class="bg-maroon-50 border border-maroon-200 rounded-xl p-4 mb-6">
            <div class="flex items-center">
                @if($inventory->product->image)
                    <img class="h-12 w-12 rounded-lg object-cover" src="{{ $inventory->product->image_src }}" alt="">
                @else
                    <div class="h-12 w-12 rounded-lg bg-maroon-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-maroon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                @endif
                <div class="ml-4">
                    <div class="text-lg font-black text-maroon-800">{{ $inventory->product->name }}</div>
                    <div class="text-sm text-maroon-600">{{ $inventory->product->category->name ?? 'No Category' }}</div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <form method="POST" action="{{ route('admin.inventory.update', $inventory) }}" class="p-6 space-y-6">
                @csrf
                @method('PATCH')
                
                <!-- Stock Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2">Current Quantity *</label>
                        <input type="number" name="quantity" value="{{ $inventory->quantity }}" min="0" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500">
                        @error('quantity')
                            <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2">Min Stock Level *</label>
                        <input type="number" name="min_stock_level" value="{{ $inventory->min_stock_level }}" min="1" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500">
                        @error('min_stock_level')
                            <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2">Max Stock Level *</label>
                        <input type="number" name="max_stock_level" value="{{ $inventory->max_stock_level }}" min="1" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500">
                        @error('max_stock_level')
                            <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2">Cost Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500 font-bold">$</span>
                            <input type="number" name="cost_price" step="0.01" min="0" value="{{ $inventory->cost_price }}" placeholder="0.00"
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500">
                        </div>
                        @error('cost_price')
                            <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2">Selling Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500 font-bold">$</span>
                            <input type="number" name="selling_price" step="0.01" min="0" value="{{ $inventory->selling_price }}" placeholder="0.00"
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500">
                        </div>
                        @error('selling_price')
                            <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Supplier Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2">Supplier</label>
                        <input type="text" name="supplier" value="{{ $inventory->supplier }}" maxlength="255"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500">
                        @error('supplier')
                            <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2">Storage Location</label>
                        <input type="text" name="location" value="{{ $inventory->location }}" maxlength="255"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500">
                        @error('location')
                            <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-black text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" rows="3" maxlength="1000"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500">{{ $inventory->notes }}</textarea>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Status -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-black text-gray-700 mb-3">Current Status</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-bold text-gray-600">Stock Status:</span>
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black {{ $inventory->stock_status_color }}">
                                {{ $inventory->stock_status }}
                            </span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-600">Low Stock Alert:</span>
                            <span class="ml-2">{{ $inventory->low_stock_alert ? 'Active' : 'Inactive' }}</span>
                        </div>
                        @if($inventory->last_restocked_at)
                            <div class="col-span-2">
                                <span class="font-bold text-gray-600">Last Restocked:</span>
                                <span class="ml-2">{{ $inventory->last_restocked_at->format('M d, Y h:i A') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" class="flex-1 px-6 py-3 bg-maroon-600 hover:bg-maroon-700 text-white font-black rounded-lg shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Inventory Record
                    </button>
                    <a href="{{ route('admin.inventory.index') }}" class="flex-1 px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition-all text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
