@extends('layouts.app')

@section('title', $product->name . ' - Yakan')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8 text-sm">
        <a href="{{ route('welcome') }}" class="text-gray-500 hover:text-gray-700">Home</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700">Products</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-900 font-medium">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Product Images -->
        <div class="space-y-4">
            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden shadow-lg">
                @if($product->image)
                    <img id="mainProductImage" src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" 
                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200\'><div class=\'text-8xl opacity-20\'>📦</div></div>';">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                        <div class="text-8xl opacity-20">📦</div>
                    </div>
                @endif
            </div>
            
            <!-- Thumbnail Gallery -->
            <div class="flex gap-2 overflow-x-auto" id="thumbnailGallery">
                @php
                    // Decode all_images if it's a string
                    $allImages = $product->all_images;
                    if (is_string($allImages)) {
                        $allImages = json_decode($allImages, true);
                    }
                    $allImages = $allImages ?? [];
                    $hasMultipleImages = is_array($allImages) && count($allImages) > 0;
                @endphp
                
                @if($hasMultipleImages)
                    @foreach($allImages as $index => $img)
                        <div class="thumbnail-item w-20 h-20 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden cursor-pointer border-2 {{ $index === 0 ? 'border-red-500' : 'border-gray-300' }} hover:border-red-400 transition-colors"
                             onclick="changeMainImage('{{ asset('uploads/products/' . $img['path']) }}', this)"
                             data-color="{{ $img['color'] ?? '' }}">
                            <img src="{{ asset('uploads/products/' . $img['path']) }}" alt="Thumbnail {{ $index + 1 }}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center\'><div class=\'text-2xl opacity-30\'>📦</div></div>';">
                        </div>
                    @endforeach
                @else
                    @if($product->image)
                        <div class="thumbnail-item w-20 h-20 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden cursor-pointer border-2 border-red-500"
                             onclick="changeMainImage('{{ asset('uploads/products/' . $product->image) }}', this)">
                            <img src="{{ asset('uploads/products/' . $product->image) }}" alt="Thumbnail" class="w-full h-full object-cover"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center\'><div class=\'text-2xl opacity-30\'>📦</div></div>';">
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Product Details -->
        <div class="space-y-6">
            <!-- Product Header -->
            <div>
                @if($product->category)
                    <span class="inline-block px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full mb-3">
                        {{ $product->category->name }}
                    </span>
                @endif
                
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>
                
                <!-- Rating -->
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex text-yellow-400" title="4.0 out of 5 stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= 4)
                                ★
                            @else
                                ☆
                            @endif
                        @endfor
                    </div>
                    <span class="text-sm text-gray-500">(24 reviews)</span>
                    <span class="text-sm text-gray-400">|</span>
                    <span class="text-sm text-gray-500">SKU: {{ $product->sku ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Price Section -->
            <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-2xl p-6 border border-red-100">
                <div class="flex items-baseline gap-3">
                    <div class="text-4xl font-bold text-red-600">₱{{ number_format($product->price, 2) }}</div>
                    @if($product->original_price && $product->original_price > $product->price)
                        <div class="text-lg text-gray-500 line-through">₱{{ number_format($product->original_price, 2) }}</div>
                        <span class="bg-red-600 text-white px-2 py-1 rounded-full text-xs font-semibold">
                            Save ₱{{ number_format($product->original_price - $product->price, 2) }}
                        </span>
                    @endif
                </div>
                
                <!-- Stock Status -->
                <div class="mt-4 flex items-center gap-2">
                    @if($product->stock > 0)
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-green-700">
                            {{ $product->stock }} units in stock - Ready to ship
                        </span>
                    @else
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        <span class="text-sm font-medium text-red-700">
                            Out of stock
                        </span>
                    @endif
                </div>
            </div>

            <!-- Description -->
            <div class="prose prose-gray max-w-none">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                <div class="text-gray-600 leading-relaxed">
                    @if($product->description)
                        {!! nl2br(e($product->description)) !!}
                    @else
                        <p>Premium quality product with exceptional features and craftsmanship. Perfect for your everyday needs.</p>
                    @endif
                </div>
            </div>

            <!-- Quantity Selection -->
            <div class="flex items-center gap-4">
                <label for="qty" class="text-sm font-semibold text-gray-700">Quantity:</label>
                <div class="flex items-center border border-gray-300 rounded-lg">
                    <button type="button" onclick="decrementQty()" class="px-3 py-2 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                    </button>
                    <input id="qty" name="quantity" type="number" min="1" max="{{ $product->stock ?? 999 }}" value="1" 
                           class="w-16 text-center border-0 focus:ring-0" readonly>
                    <button type="button" onclick="incrementQty()" class="px-3 py-2 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
                <span class="text-sm text-gray-500">({{ $product->stock ?? 0 }} available)</span>
            </div>

            <!-- Purchase Options -->
            <div class="space-y-3">
                <!-- Buttons Container -->
                <div class="flex flex-col sm:flex-row gap-3 items-stretch">
                    <!-- Add to Cart -->
                    <form id="addToCartForm" method="POST" action="{{ route('cart.add', $product) }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="quantity" id="cartQty" value="1">
                        <button type="submit" 
                                class="w-full h-full bg-gradient-to-r from-red-600 to-red-700 text-white px-6 py-3 rounded-xl font-semibold hover:from-red-700 hover:to-red-800 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 whitespace-nowrap"
                                @if($product->stock == 0) disabled @endif
                        >
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>
                                @if($product->stock > 0)
                                    Add to Cart
                                @else
                                    Out of Stock
                                @endif
                            </span>
                        </button>
                    </form>

                <!-- Wishlist -->
                <button id="wishlistBtn" 
                        class="flex-1 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-50 hover:border-gray-400 transition-all duration-300 flex items-center justify-center gap-2 whitespace-nowrap"
                        onclick="toggleWishlist('product', {{ $product->id }})"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span id="wishlistBtnText" class="hidden sm:inline-block">Add to Wishlist</span>
                    <span id="wishlistBtnTextMobile" class="sm:hidden">Wishlist</span>
                </button>

                <!-- Buy Now -->
                <form id="buyNowForm" method="POST" action="{{ route('cart.add', $product) }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="quantity" id="buyNowQty" value="1">
                    <input type="hidden" name="buy_now" value="1">
                    <button type="submit" 
                            id="buyNowBtn"
                            class="w-full h-full border-2 border-red-600 text-red-600 px-6 py-3 rounded-xl font-semibold hover:bg-red-50 hover:border-red-700 transition-all duration-300 flex items-center justify-center gap-2 whitespace-nowrap"
                            @if($product->stock == 0) disabled @endif
                    >
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span id="buyNowBtnText">
                            @if($product->stock > 0)
                                Buy Now
                            @else
                                Out of Stock
                            @endif
                        </span>
                    </button>
                </form>
                </div>
            </div>

            <!-- Product Features -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Features</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm text-gray-600">Premium Quality</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-sm text-gray-600">Fast Shipping</span>
                    </div>
                </div>
            </div>

            <!-- Recent Views -->
            @include('layouts._recent_views')

            <!-- Related Products -->
            @if(isset($relatedProducts) && (is_array($relatedProducts) ? count($relatedProducts) > 0 : $relatedProducts->isNotEmpty()))
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-black text-gray-900 mb-4">Related Products</h2>
                    <div class="space-y-3">
                        @foreach($relatedProducts as $related)
                            <a href="{{ route('products.show', $related) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                @if($related->image && file_exists(public_path('uploads/products/' . $related->image)))
                                    <img src="{{ asset('uploads/products/' . $related->image) }}" alt="{{ $related->name }}" class="w-12 h-12 object-cover rounded-lg" />
                                @else
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-black text-gray-900 truncate">{{ $related->name }}</h4>
                                    <p class="text-xs text-gray-600">₱{{ number_format($related->price, 2) }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Customer Reviews Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-t-4" style="border-top-color: #800000;">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Customer Reviews</h2>
                
                @php
                    // Get approved reviews for this product
                    $reviews = \App\Models\Review::where('product_id', $product->id)
                        ->where('is_approved', true)
                        ->with('user')
                        ->orderByDesc('created_at')
                        ->get();
                @endphp

                @if($reviews->count() > 0)
                    <div class="space-y-6">
                        @foreach($reviews as $review)
                            <div class="border-b pb-6 last:border-b-0">
                                <!-- Review Header -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #800000, #600000);">
                                            <span class="text-white font-bold text-sm">{{ strtoupper(substr($review->user->name ?? 'User', 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $review->user->name ?? 'Anonymous' }}</p>
                                            <p class="text-xs text-gray-500">{{ $review->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    @if($review->verified_purchase)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Verified Purchase
                                        </span>
                                    @endif
                                </div>

                                <!-- Rating Stars -->
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="flex text-yellow-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <span class="text-lg">★</span>
                                            @else
                                                <span class="text-lg opacity-30">★</span>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ $review->rating }}.0</span>
                                </div>

                                <!-- Review Title -->
                                @if($review->title)
                                    <h4 class="font-semibold text-gray-900 mb-2">{{ $review->title }}</h4>
                                @endif

                                <!-- Review Comment -->
                                @if($review->comment)
                                    <p class="text-gray-700 mb-4 leading-relaxed">{{ $review->comment }}</p>
                                @endif

                                <!-- Helpful/Unhelpful -->
                                <div class="flex items-center gap-4 text-sm">
                                    <button class="flex items-center gap-1 text-gray-500 hover:text-green-600 transition-colors" 
                                            onclick="markHelpful({{ $review->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.646 7.23a2 2 0 01-1.789 1.106H7a2 2 0 01-2-2V9a6 6 0 0112-6z"/>
                                        </svg>
                                        <span>Helpful ({{ $review->helpful_count }})</span>
                                    </button>
                                    <button class="flex items-center gap-1 text-gray-500 hover:text-red-600 transition-colors"
                                            onclick="markUnhelpful({{ $review->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.646-7.23a2 2 0 011.789-1.106H17a2 2 0 012 2v9a6 6 0 01-12 0z"/>
                                        </svg>
                                        <span>Not Helpful ({{ $review->unhelpful_count }})</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <p class="text-gray-500 mb-4">No reviews yet. Be the first to review this product!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Helpful/Unhelpful functionality
function markHelpful(reviewId) {
    fetch(`/reviews/${reviewId}/helpful`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markUnhelpful(reviewId) {
    fetch(`/reviews/${reviewId}/unhelpful`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

<script>
// Quantity controls
function incrementQty() {
    const input = document.getElementById('qty');
    const maxValue = parseInt(input.max) || 999;
    if (parseInt(input.value) < maxValue) {
        input.value = parseInt(input.value) + 1;
        updateHiddenInputs();
    }
}

function decrementQty() {
    const input = document.getElementById('qty');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        updateHiddenInputs();
    }
}

function updateHiddenInputs() {
    const qty = document.getElementById('qty').value;
    document.getElementById('cartQty').value = qty;
    document.getElementById('buyNowQty').value = qty;
}

// Wishlist functionality
function checkWishlistStatus() {
    fetch('{{ route("wishlist.check") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            type: 'product',
            id: {{ $product->id }}
        })
    })
    .then(response => response.json())
    .then(data => {
        updateWishlistButton(data.in_wishlist);
    })
    .catch(error => console.error('Error checking wishlist:', error));
}

function toggleWishlist(type, id) {
    const btn = document.getElementById('wishlistBtn');
    const btnText = document.getElementById('wishlistBtnText');
    
    // Disable button temporarily
    btn.disabled = true;
    btnText.textContent = 'Loading...';
    
    const action = btn.classList.contains('in-wishlist') ? 'remove' : 'add';
    
    fetch(`{{ route("wishlist.add") }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            type: type,
            id: id,
            _action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateWishlistButton(action === 'add');
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
        btn.disabled = false;
    });
}

function updateWishlistButton(inWishlist) {
    const btn = document.getElementById('wishlistBtn');
    const btnText = document.getElementById('wishlistBtnText');
    const btnTextMobile = document.getElementById('wishlistBtnTextMobile');
    
    if (inWishlist) {
        btn.classList.add('in-wishlist', 'border-red-500', 'text-red-600', 'bg-red-50');
        btn.classList.remove('border-gray-300', 'text-gray-700');
        btnText.textContent = 'Remove from Wishlist';
        btnTextMobile.textContent = 'Remove';
    } else {
        btn.classList.remove('in-wishlist', 'border-red-500', 'text-red-600', 'bg-red-50');
        btn.classList.add('border-gray-300', 'text-gray-700');
        btnText.textContent = 'Add to Wishlist';
        btnTextMobile.textContent = 'Wishlist';
    }
}

function showNotification(message, type = 'success') {
    // Simple notification (you can replace with a better toast system)
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

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateHiddenInputs();
    checkWishlistStatus();
});

// Global double-click prevention
document.addEventListener('dblclick', function(e) {
    if (e.target.closest('button, form, .btn')) {
        e.preventDefault();
        return false;
    }
}, true);

// Change main product image
function changeMainImage(imageSrc, thumbnailElement) {
    const mainImage = document.getElementById('mainProductImage');
    if (mainImage) {
        mainImage.src = imageSrc;
    }
    
    // Update thumbnail borders
    document.querySelectorAll('.thumbnail-item').forEach(thumb => {
        thumb.classList.remove('border-red-500');
        thumb.classList.add('border-gray-300');
    });
    
    if (thumbnailElement) {
        thumbnailElement.classList.remove('border-gray-300');
        thumbnailElement.classList.add('border-red-500');
    }
}


</script>
@endsection
