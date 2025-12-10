@extends('layouts.app')

@section('title', 'Search Results - Yakan')

@push('styles')
<style>
    .highlight {
        background-color: #fef3c7;
        color: #92400e;
        font-weight: 600;
        padding: 2px 4px;
        border-radius: 3px;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                @if(!empty($query))
                    Search Results for "<span class="highlight text-2xl">{{ $query }}</span>"
                @else
                    All Products
                @endif
            </h1>
            <p class="text-gray-600">
                Found <span class="font-semibold text-gray-900">{{ $products->total() }}</span> {{ Str::plural('product', $products->total()) }}
                @if(!empty($query))
                    matching your search
                @endif
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Filters Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Filters</h2>
                        @if(request('category') || request('min_price') || request('max_price') || request('sort') != 'relevance')
                            <span class="bg-maroon-100 text-maroon-800 text-xs px-2 py-1 rounded-full" style="background-color: rgba(128, 0, 0, 0.1); color: #800000;">
                                Active
                            </span>
                        @endif
                    </div>
                    
                    <form action="{{ route('products.search') }}" method="GET" id="filterForm">
                        <!-- Keep search query -->
                        <input type="hidden" name="q" value="{{ $query }}">
                        
                        <!-- Category Filter -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Category</h3>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="category" value="" {{ empty(request('category')) ? 'checked' : '' }} onchange="this.form.submit()" class="mr-2 text-maroon-600">
                                    <span class="text-sm text-gray-700">All Categories</span>
                                </label>
                                @foreach($categories as $cat)
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="category" value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'checked' : '' }} onchange="this.form.submit()" class="mr-2 text-maroon-600">
                                        <span class="text-sm text-gray-700">{{ $cat->name }} ({{ $cat->products_count }})</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Price Range</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-600">Min Price</label>
                                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="₱0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-600">Max Price</label>
                                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="₱10000" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <button type="submit" class="w-full py-2 bg-maroon-600 text-white rounded-lg hover:bg-maroon-700 transition-colors text-sm" style="background-color: #800000;">
                                    Apply
                                </button>
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Sort By</h3>
                            <select name="sort" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>Relevance</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name: Z to A</option>
                            </select>
                        </div>

                        <!-- Clear Filters -->
                        <a href="{{ route('products.search') }}?q={{ $query }}" class="block w-full text-center py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                            Clear Filters
                        </a>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="lg:col-span-3">
                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                                <a href="{{ route('products.show', $product) }}">
                                    <div class="relative h-64 bg-gray-200">
                                        @if($product->image)
                                            <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        @if($product->stock <= 5 && $product->stock > 0)
                                            <span class="absolute top-2 right-2 bg-orange-500 text-white text-xs px-2 py-1 rounded-full">
                                                Only {{ $product->stock }} left
                                            </span>
                                        @elseif($product->stock == 0)
                                            <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                                Out of Stock
                                            </span>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        @if($product->category)
                                            <p class="text-xs text-gray-500 mb-1">{{ $product->category->name }}</p>
                                        @endif
                                        <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">
                                            @if(!empty($query))
                                                {!! preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="highlight">$1</span>', $product->name) !!}
                                            @else
                                                {{ $product->name }}
                                            @endif
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                            @if(!empty($query))
                                                {!! preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="highlight">$1</span>', Str::limit($product->description, 80)) !!}
                                            @else
                                                {{ Str::limit($product->description, 80) }}
                                            @endif
                                        </p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-2xl font-bold" style="color: #800000;">₱{{ number_format($product->price, 2) }}</span>
                                            @if($product->stock > 0)
                                                <button class="px-4 py-2 rounded-lg text-white text-sm font-semibold hover:opacity-90 transition-opacity" style="background-color: #800000;">
                                                    View
                                                </button>
                                            @else
                                                <button disabled class="px-4 py-2 bg-gray-300 rounded-lg text-gray-600 text-sm font-semibold cursor-not-allowed">
                                                    Unavailable
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @else
                    <!-- No Results -->
                    <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                        <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-600 mb-6">
                            @if(!empty($query))
                                We couldn't find any products matching "{{ $query }}"
                            @else
                                No products available at the moment
                            @endif
                        </p>
                        <a href="{{ route('products.index') }}" class="inline-block px-6 py-3 rounded-lg text-white font-semibold hover:opacity-90 transition-opacity" style="background-color: #800000;">
                            Browse All Products
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
