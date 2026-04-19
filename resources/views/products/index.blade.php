@extends('layouts.app')

@section('title', 'Products - Yakan')

@push('styles')
<style>
    .products-hero {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        position: relative;
        overflow: hidden;
    }

    .products-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        cursor: pointer;
    }
    
    @media (min-width: 768px) {
        .product-card {
            border-radius: 20px;
        }
    }

    .product-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
    }

    .product-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #800000, #600000);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .product-card:hover::before {
        transform: scaleX(1);
    }

    .product-image {
        height: 200px;
        width: 100%;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        position: relative;
        overflow: hidden;
        border-radius: 16px 16px 0 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
    }
    
    @media (min-width: 640px) {
        .product-image {
            height: 240px;
            padding: 16px;
        }
    }
    
    @media (min-width: 768px) {
        .product-image {
            height: 280px;
            padding: 20px;
        }
    }

    .product-image::before {
        content: '📦';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 4rem;
        opacity: 0.3;
        transition: opacity 0.3s ease;
    }

    .product-card:hover .product-image::before {
        opacity: 0.1;
    }

    .product-image img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        position: relative;
        transition: transform 0.4s ease;
    }

    .product-card:hover .product-image img {
        transform: scale(1.1);
    }

    .product-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: transparent;
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        padding: 0.9rem;
    }

    .product-card:hover .product-overlay {
        opacity: 1;
    }

    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        width: auto;
    }

    .quick-action-btn {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 9999px;
        cursor: pointer;
        transition: all 0.2s ease;
        backdrop-filter: blur(10px);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #111827;
    }

    .quick-action-btn:hover {
        background: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .quick-action-btn.primary {
        background: rgba(255, 255, 255, 0.95);
        color: #111827;
    }

    .quick-action-btn.primary:hover {
        background: white;
    }

    .product-badge {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 0.25rem 0.5rem;
        border-radius: 2rem;
        font-size: 0.625rem;
        font-weight: 600;
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }
    
    @media (min-width: 640px) {
        .product-badge {
            top: 0.75rem;
            right: 0.75rem;
            padding: 0.25rem 0.625rem;
            font-size: 0.7rem;
        }
    }
    
    @media (min-width: 768px) {
        .product-badge {
            top: 1rem;
            right: 1rem;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
        }
    }

    .product-badge.hot {
        background: linear-gradient(135deg, #dc2626, #ea580c);
        color: white;
        border: none;
    }

    .product-badge.new {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
    }

    .price-section {
        position: relative;
    }

    .price-section::before {
        content: '';
        position: absolute;
        top: -0.5rem;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #800000, transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .product-card:hover .price-section::before {
        opacity: 1;
    }

    .category-pill {
        background: linear-gradient(135deg, #800000, #600000);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3);
    }

    .category-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(128, 0, 0, 0.4);
    }

    .category-pill.active {
        background: linear-gradient(135deg, #1f2937, #374151);
        box-shadow: 0 4px 15px rgba(31, 41, 55, 0.3);
    }

    .search-box {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        padding: 12px 20px;
        font-size: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .search-box:focus {
        outline: none;
        border-color: #800000;
        box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
    }

    .filter-section {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 100px;
    }

    .price-slider {
        background: linear-gradient(90deg, #800000, #600000);
        height: 6px;
        border-radius: 3px;
        position: relative;
    }

    .loading-skeleton {
        background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="products-hero py-16 relative">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center animate-fade-in-up">
                <h1 class="text-5xl lg:text-6xl font-bold text-white mb-6">Premium Products</h1>
                <p class="text-xl text-white max-w-3xl mx-auto">
                    Discover our carefully curated collection of high-quality products. From everyday essentials to unique finds, we have something for everyone.
                </p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Filters Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Filters</h2>
                        @if(request('category') || request('min_price') || request('max_price') || request('sort') != 'newest')
                            <span class="bg-maroon-100 text-maroon-800 text-xs px-2 py-1 rounded-full" style="background-color: rgba(128, 0, 0, 0.1); color: #800000;">
                                Active
                            </span>
                        @endif
                    </div>
                    
                    <form action="{{ route('products.index') }}" method="GET" id="filterForm">
                        <!-- Category Filter -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Category</h3>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="category" value="" {{ empty(request('category')) ? 'checked' : '' }} onchange="this.form.submit()" class="mr-2 text-maroon-600">
                                    <span class="text-sm text-gray-700">All Categories</span>
                                </label>
                                @if(isset($categories))
                                    @foreach($categories as $cat)
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="category" value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'checked' : '' }} onchange="this.form.submit()" class="mr-2 text-maroon-600">
                                            <span class="text-sm text-gray-700">{{ $cat->name }} ({{ $cat->products_count }})</span>
                                        </label>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Price Range</h3>
                            @php
                                $actualMin = isset($priceStats) ? (int) floor($priceStats->min_p) : 0;
                                $actualMax = isset($priceStats) ? (int) ceil($priceStats->max_p) : 10000;
                            @endphp
                            <div class="text-xs text-gray-400 mb-2">Range: ₱{{ number_format($actualMin) }} – ₱{{ number_format($actualMax) }}</div>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-600">Min Price</label>
                                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="₱{{ $actualMin }}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-600">Max Price</label>
                                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="₱{{ number_format($actualMax) }}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
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
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name: Z to A</option>
                            </select>
                        </div>

                        <!-- Clear Filters -->
                        <a href="{{ route('products.index') }}" class="block w-full text-center py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                            Clear Filters
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- Products Grid -->
            <main class="lg:col-span-3">
                <!-- Active Filters -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <span class="category-pill active">
                        {{ $selectedCategory->name ?? 'All Products' }}
                    </span>
                    <span class="text-gray-500 text-sm self-center">({{ $products->total() }} products)</span>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
                    @foreach($products as $index => $product)
                        <div class="product-card animate-fade-in-up" style="animation-delay: {{ $index * 0.1 }}s">
                            <div class="product-image">
                                @if($product->hasImage())
                                    <img src="{{ $product->image_src }}" alt="{{ $product->name }}" 
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200\'><div class=\'text-center\'><div class=\'text-6xl mb-2\'>🧵</div><div class=\'text-sm text-gray-500 font-semibold\'>{{ $product->name }}</div></div></div>';">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                        <div class="text-center">
                                            <div class="text-6xl mb-2">🧵</div>
                                            <div class="text-sm text-gray-500 font-semibold">{{ $product->name }}</div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Product Badge -->
                                @php $availableStock = $product->available_stock; @endphp
                                @if($product->is_bundle)
                                    <span class="product-badge new">Bundle</span>
                                @elseif($availableStock == 0)
                                    <span class="product-badge">Sold Out</span>
                                @elseif($availableStock <= 5)
                                    <span class="product-badge hot">Low Stock</span>
                                @elseif($product->created_at && \Carbon\Carbon::parse($product->created_at)->diffInDays(now()) <= 7)
                                    <span class="product-badge new">New</span>
                                @endif
                                
                                <!-- Hover Overlay with Quick Actions -->
                                <div class="product-overlay">
                                    <div class="quick-actions">
                                        <button id="wishlist-btn-{{ $product->id }}" class="quick-action-btn" onclick="event.stopPropagation(); toggleWishlist({{ $product->id }})" title="Save to wishlist" aria-label="Save to wishlist">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                        </button>
                                        @if($product->isInStock())
                                            <button class="quick-action-btn primary" onclick="event.stopPropagation(); quickAddToCart({{ $product->id }})" title="Add to cart" aria-label="Add to cart">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h9l3-6H5.4M7 13l-1 2h10"/>
                                                    <circle cx="9" cy="18" r="1.5" stroke-width="1.8"/>
                                                    <circle cx="16" cy="18" r="1.5" stroke-width="1.8"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 6h4m-2-2v4"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button class="quick-action-btn" disabled style="opacity: 0.5; cursor: not-allowed; background: #6b7280; color: #fff;" title="Out of stock" aria-label="Out of stock">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-3 sm:p-4 md:p-6">
                                <div class="flex items-start justify-between mb-2 md:mb-3">
                                    <h3 class="text-sm sm:text-base md:text-xl font-bold text-gray-900 transition-colors line-clamp-2" style="cursor: pointer;" onmouseover="this.style.color='#800000'" onmouseout="this.style.color=''">{{ $product->name }}</h3>
                                    @if($product->category)
                                        <span class="text-xs px-2 py-1 rounded-full ml-2 flex-shrink-0" style="background-color: rgba(128, 0, 0, 0.1); color: #800000;">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                </div>
                                
                                <p class="text-gray-600 mb-2 md:mb-4 line-clamp-2 text-xs sm:text-sm leading-relaxed hidden sm:block">
                                    {{ $product->description ?? 'Premium quality product with exceptional features and craftsmanship.' }}
                                </p>

                                @if($product->is_bundle)
                                    <p class="text-xs font-semibold mb-2 md:mb-4" style="color: #800000;">
                                        Includes {{ $product->bundleItems->count() }} {{ \Illuminate\Support\Str::plural('item', $product->bundleItems->count()) }}
                                    </p>
                                @endif
                                
                                <!-- Rating -->
                                <div class="flex items-center mb-2 md:mb-4">
                                    @php
                                        $reviewCount = (int) ($product->reviews_count ?? 0);
                                        $averageRating = (float) ($product->average_rating ?? 0);
                                    @endphp
                                    <div class="flex text-yellow-400 mr-1 md:mr-2 text-xs sm:text-sm" title="{{ number_format($averageRating, 1) }} out of 5 stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($reviewCount > 0 && $i <= floor($averageRating))
                                                ★
                                            @else
                                                ☆
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-xs sm:text-sm text-gray-500 hidden sm:inline">({{ $reviewCount }} {{ Str::plural('review', $reviewCount) }})</span>
                                </div>
                                
                                <!-- Price Section -->
                                <div class="price-section flex items-center justify-between mb-3 md:mb-4">
                                    <div>
                                        @php
                                            $basePrice = (float) ($product->price ?? 0);
                                            $displayPrice = (float) $product->getDiscountedPrice($basePrice);
                                            $discountType = strtolower((string) ($product->discount_type ?? ''));
                                            $discountValue = (float) ($product->discount_value ?? 0);
                                            $hasConfiguredDiscount = in_array($discountType, ['percent', 'fixed'], true) && $discountValue > 0;
                                        @endphp
                                        <div class="text-lg sm:text-xl md:text-2xl font-bold" style="color: #800000;">₱{{ number_format($displayPrice, 2) }}</div>
                                        @if($displayPrice < $basePrice)
                                            <div class="text-xs sm:text-sm text-gray-500 line-through">₱{{ number_format($basePrice, 2) }}</div>
                                        @endif
                                        @if($hasConfiguredDiscount && ($product->discount_starts_at || $product->discount_ends_at))
                                            <div class="js-discount-countdown text-[11px] font-semibold mt-1" style="color: #9f1239;"
                                                 data-discount-start="{{ optional($product->discount_starts_at)->toIso8601String() }}"
                                                 data-discount-end="{{ optional($product->discount_ends_at)->toIso8601String() }}">
                                                --
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Stock Indicator -->
                                    <div class="text-right">
                                        @php
                                            $stockCount = $product->available_stock;
                                        @endphp
                                        @if($stockCount > 0)
                                            <div class="text-xs text-green-600 font-semibold">
                                                {{ $stockCount }} in stock
                                            </div>
                                        @else
                                            <div class="text-xs font-semibold" style="color: #800000;">
                                                Out of stock
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex">
                                    <a href="{{ route('products.show', $product->id) }}" class="ml-auto px-4 sm:px-5 md:px-6 py-1.5 sm:py-2 text-sm font-semibold rounded-lg text-white text-center transition-all hover:opacity-90" style="background-color: #800000;" onclick="event.stopPropagation()">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="mt-12 flex justify-center">
                        {{ $products->links() }}
                    </div>
                @endif

                <!-- Empty State -->
                @if($products->isEmpty())
                    <div class="text-center py-16">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-600 mb-8">Try adjusting your filters or search terms</p>
                        <button class="btn-secondary">Clear Filters</button>
                    </div>
                @endif
            </main>
        </div>
    </div>

<script>
// Wishlist product IDs from server
const wishlistProductIds = @json($wishlistProductIds ?? []);

// Initialize wishlist hearts on page load
document.addEventListener('DOMContentLoaded', function() {
    wishlistProductIds.forEach(productId => {
        updateWishlistHeart(productId, true);
    });
});

// Update heart appearance
function updateWishlistHeart(productId, inWishlist) {
    const button = document.getElementById(`wishlist-btn-${productId}`);
    if (!button) return;
    
    const svg = button.querySelector('svg');
    const path = svg.querySelector('path');
    
    if (inWishlist) {
        button.classList.add('in-wishlist');
        button.style.color = '#800000';
        svg.setAttribute('fill', '#800000');
        svg.setAttribute('stroke', '#800000');
    } else {
        button.classList.remove('in-wishlist');
        button.style.color = '';
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
    }
}

// Product interaction functions
function toggleWishlist(productId) {
    event.stopPropagation();
    
    const button = event.target.closest('button');
    button.disabled = true;
    
    const action = button.classList.contains('in-wishlist') ? 'remove' : 'add';
    const route = action === 'add' ? '{{ route("wishlist.add") }}' : '{{ route("wishlist.remove") }}';
    
    fetch(route, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            type: 'product',
            id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const inWishlist = action === 'add';
            updateWishlistHeart(productId, inWishlist);
            
            // Update the wishlistProductIds array
            if (inWishlist) {
                wishlistProductIds.push(productId);
            } else {
                const index = wishlistProductIds.indexOf(productId);
                if (index > -1) wishlistProductIds.splice(index, 1);
            }
            
            showNotification(data.message);
        } else {
            showNotification(data.message || 'Error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating wishlist:', error);
        showNotification('Error updating wishlist', 'error');
    })
    .finally(() => {
        button.disabled = false;
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function quickAddToCart(productId) {
    console.log('Quick add to cart:', productId);
    
    // Add loading state
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="w-4 h-4 inline animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Adding...';
    button.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        button.innerHTML = originalText;
        button.disabled = false;
        showNotification('Session error. Please refresh the page.', 'error');
        return;
    }
    
    fetch(`/cart/add/${productId}`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ quantity: 1 })
    })
    .then(response => {
        // Try to parse as JSON regardless of content-type header
        // (Railway's PHP dev server may return text/html for JSON responses)
        if (response.redirected && response.url.includes('/login')) {
            throw new Error('LOGIN_REQUIRED');
        }
        
        return response.text().then(text => {
            try {
                const data = JSON.parse(text);
                return { data, status: response.status, ok: response.ok };
            } catch (e) {
                // Truly not JSON — probably an HTML login page
                if (response.status === 401 || text.includes('login')) {
                    throw new Error('LOGIN_REQUIRED');
                }
                throw new Error('INVALID_RESPONSE');
            }
        });
    })
    .then(({ data, status, ok }) => {
        if (ok && data.success) {
            button.innerHTML = '✓ Added';
            button.classList.add('bg-green-600');
            updateCartBadge(data.cart_count);
            showNotification(data.message || 'Product added to cart!', 'success');
        } else {
            button.innerHTML = '✗ Failed';
            let errorMsg = data.message || data.error || 'Could not add to cart';
            if (status === 401) {
                errorMsg = 'Please login to add items to your cart.';
            } else if (status === 419) {
                errorMsg = 'Session expired. Please refresh the page and try again.';
            }
            showNotification(errorMsg, 'error');
        }
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
            button.classList.remove('bg-green-600');
        }, 2000);
    })
    .catch(error => {
        console.error('Add to cart error:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        if (error.message === 'LOGIN_REQUIRED') {
            showNotification('Please login to add items to your cart.', 'error');
            setTimeout(() => { window.location.href = '/login'; }, 1500);
        } else {
            showNotification('Error adding to cart. Please refresh and try again.', 'error');
        }
    });
}

function quickView(productId) {
    // Navigate to product page with auth token
    const authToken = localStorage.getItem('yakan_auth_token');
    let url = '/products/' + productId;
    if (authToken) url += '?auth_token=' + encodeURIComponent(authToken);
    window.location.href = url;
}

function updateCartBadge(count) {
    // Update cart badge count
    const cartBadge = document.querySelector('.cart-badge');
    if (cartBadge) {
        if (count !== undefined) {
            cartBadge.textContent = count;
        } else {
            const currentCount = parseInt(cartBadge.textContent) || 0;
            cartBadge.textContent = currentCount + 1;
        }
        cartBadge.style.display = 'flex';
        cartBadge.classList.remove('hidden');
        
        // Add pulse animation
        cartBadge.classList.add('animate-pulse');
        setTimeout(() => {
            cartBadge.classList.remove('animate-pulse');
        }, 1000);
    }
}

function formatDiscountCountdown(seconds) {
    const safeSeconds = Math.max(0, seconds);
    const days = Math.floor(safeSeconds / 86400);
    const hours = Math.floor((safeSeconds % 86400) / 3600);
    const minutes = Math.floor((safeSeconds % 3600) / 60);
    const secs = safeSeconds % 60;
    return `${days}d ${String(hours).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}m ${String(secs).padStart(2, '0')}s`;
}

function initDiscountCountdowns() {
    const countdownEls = document.querySelectorAll('.js-discount-countdown');
    if (!countdownEls.length) return;

    const tick = () => {
        const now = new Date();

        countdownEls.forEach((el) => {
            const startRaw = el.dataset.discountStart || '';
            const endRaw = el.dataset.discountEnd || '';
            const startsAt = startRaw ? new Date(startRaw) : null;
            const endsAt = endRaw ? new Date(endRaw) : null;

            if (startsAt && now < startsAt) {
                const remaining = Math.floor((startsAt.getTime() - now.getTime()) / 1000);
                el.textContent = `Starts in: ${formatDiscountCountdown(remaining)}`;
                return;
            }

            if (endsAt && now < endsAt) {
                const remaining = Math.floor((endsAt.getTime() - now.getTime()) / 1000);
                el.textContent = `Ends in: ${formatDiscountCountdown(remaining)}`;
                return;
            }

            el.textContent = 'Discount ended';
        });
    };

    tick();
    setInterval(tick, 1000);
}

// Add click handler to product cards - make globally available
window.initProductCards = function() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach((card, index) => {
        // Skip if card is null or not an element
        if (!card || !(card instanceof Element)) return;
        
        // Check if card already has the click handler to prevent duplicates
        if (card.hasAttribute('data-click-handler-added')) return;
        
        // Mark as processed
        card.setAttribute('data-click-handler-added', 'true');
        
        // Use click instead of mousedown for better reliability
        card.addEventListener('click', function(e) {
            // Don't navigate if clicking on interactive elements
            if (e.target.closest('button') || 
                e.target.closest('a') || 
                e.target.closest('input') || 
                e.target.closest('svg') ||
                e.target.closest('.quick-actions') ||
                e.target.closest('.product-overlay')) {
                return;
            }
            
            // Find product link and navigate
            const productLink = this.querySelector('a[href*="products"]');
            if (productLink && productLink.getAttribute('href')) {
                // Prevent default and stop propagation
                e.preventDefault();
                e.stopPropagation();
                
                // Navigate immediately
                window.location.href = productLink.getAttribute('href');
            }
        });
        
        // Add cursor pointer for better UX
        card.style.cursor = 'pointer';
    });
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    window.initProductCards();
    initDiscountCountdowns();
});

// Prevent double-click issues globally
document.addEventListener('dblclick', function(e) {
    // Prevent default double-click behavior on product cards
    if (e.target.closest('.product-card')) {
        e.preventDefault();
        return false;
    }
});
</script>
@endsection
