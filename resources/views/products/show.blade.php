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
                    <img id="mainProductImage" src="{{ $product->image_src }}" alt="{{ $product->name }}" 
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
                             onclick="changeMainImage('{{ str_starts_with($img['path'], 'http') ? $img['path'] : asset('uploads/products/' . $img['path']) }}', this)"
                             data-color="{{ $img['color'] ?? '' }}">
                            <img src="{{ str_starts_with($img['path'], 'http') ? $img['path'] : asset('uploads/products/' . $img['path']) }}" alt="Thumbnail {{ $index + 1 }}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center\'><div class=\'text-2xl opacity-30\'>📦</div></div>';">
                        </div>
                    @endforeach
                @else
                    @if($product->image)
                        <div class="thumbnail-item w-20 h-20 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden cursor-pointer border-2 border-red-500"
                             onclick="changeMainImage('{{ $product->image_src }}', this)">
                            <img src="{{ $product->image_src }}" alt="Thumbnail" class="w-full h-full object-cover"
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
                    <span class="inline-block px-3 py-1 text-white text-xs font-semibold rounded-full mb-3" style="background-color: #800000;">
                        {{ $product->category->name }}
                    </span>
                @endif
                
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>

                @if($product->is_bundle)
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full mb-4" style="background-color: rgba(128, 0, 0, 0.1); color: #800000;">
                        Product Bundle
                    </span>
                @endif
                
                <!-- Rating -->
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex text-yellow-400" title="4.0 out of 5 stars">
                        @php
                        $avgRating = \App\Models\Review::where('product_id', $product->id)->where('is_approved', true)->avg('rating') ?? 0;
                        $reviewCount = \App\Models\Review::where('product_id', $product->id)->where('is_approved', true)->count();
                    @endphp
                    @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($avgRating))
                                ★
                            @else
                                ☆
                            @endif
                        @endfor
                    </div>
                    <span class="text-sm text-gray-500">({{ $reviewCount }} {{ Str::plural('review', $reviewCount) }})</span>
                    <span class="text-sm text-gray-400">|</span>
                    <span class="text-sm text-gray-500">SKU: {{ $product->sku ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Price Section -->
            @php
                $displayPrice = isset($initialDisplayPrice) ? (float) $initialDisplayPrice : (float) $product->getDiscountedPrice((float) $product->price);
                $displayOriginalPrice = isset($initialOriginalPrice) ? (float) $initialOriginalPrice : (float) $product->price;
                $availableStock = isset($initialAvailableStock)
                    ? (int) $initialAvailableStock
                    : (int) ($product->inventory ? $product->inventory->quantity : $product->stock);
            @endphp
            <div class="bg-gradient-to-r from-red-50 to-red-50 rounded-2xl p-6 border" style="border-color: #800000;">
                <div class="flex items-baseline gap-3">
                    <div id="productDisplayPrice" class="text-4xl font-bold" style="color: #800000;">₱{{ number_format($displayPrice, 2) }}</div>
                    <div id="productOriginalPriceWrap" class="{{ $displayOriginalPrice > $displayPrice ? '' : 'hidden' }}">
                        <div id="productOriginalPrice" class="text-lg text-gray-500 line-through">₱{{ number_format($displayOriginalPrice, 2) }}</div>
                    </div>
                    <span id="productSavingsBadge" class="text-white px-2 py-1 rounded-full text-xs font-semibold {{ $displayOriginalPrice > $displayPrice ? '' : 'hidden' }}" style="background-color: #800000;">
                        Save ₱<span id="productSavingsValue">{{ number_format(max(0, $displayOriginalPrice - $displayPrice), 2) }}</span>
                    </span>
                </div>
                
                <!-- Stock Status -->
                <div class="mt-4 flex items-center gap-2">
                    <div id="productStockDot" class="w-2 h-2 {{ $availableStock > 0 ? 'bg-green-500 animate-pulse' : 'bg-red-500' }} rounded-full"></div>
                    <span id="productStockText" class="text-sm font-medium {{ $availableStock > 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $availableStock > 0 ? $availableStock . ' units in stock - Ready to ship' : 'Out of stock' }}
                    </span>
                </div>

                <!-- Estimated Delivery -->
                @if($availableStock > 0)
                @php
                    // Calculate 3–5 business days from today (skip weekends)
                    $addBusinessDays = function(int $days): \Carbon\Carbon {
                        $date = \Carbon\Carbon::now();
                        $added = 0;
                        while ($added < $days) {
                            $date->addDay();
                            if (!$date->isWeekend()) $added++;
                        }
                        return $date;
                    };
                    $earliest = $addBusinessDays(3);
                    $latest   = $addBusinessDays(5);
                    $sameMonth = $earliest->month === $latest->month;
                    $range = $sameMonth
                        ? $earliest->format('M d') . '–' . $latest->format('d, Y')
                        : $earliest->format('M d') . ' – ' . $latest->format('M d, Y');
                @endphp
                <div class="mt-3 flex items-center gap-3 px-4 py-3 rounded-xl"
                     style="background: linear-gradient(135deg, #fff5f5 0%, #ffe4e4 100%); border: 1px solid #f5c6c6;">
                    <svg class="w-5 h-5 flex-shrink-0" style="color:#800000;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div>
                        <span class="text-xs font-semibold uppercase text-gray-500">Estimated Delivery</span>
                        <p class="text-sm font-bold" style="color:#800000;">{{ $range }}</p>
                        <p class="text-xs text-gray-500">3–5 business days after order confirmation</p>
                    </div>
                </div>
                @endif
            </div>

            @if(!empty($hasVariants) && isset($activeVariants) && $activeVariants->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Variants</h3>
                    <select id="variantSelector" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                        @foreach($activeVariants as $variant)
                            @php
                                $variantBasePrice = (float) $variant->price;
                                $variantDisplayPrice = (float) $product->getDiscountedPrice($variantBasePrice);
                                $variantData = [
                                    'id' => (int) $variant->id,
                                    'size' => (string) ($variant->size ?? ''),
                                    'color' => (string) ($variant->color ?? ''),
                                    'sku' => (string) ($variant->sku ?? ''),
                                    'stock' => (int) ($variant->stock ?? 0),
                                    'price' => round($variantDisplayPrice, 2),
                                    'original_price' => round($variantBasePrice, 2),
                                ];
                            @endphp
                            <option
                                value="{{ $variant->id }}"
                                data-variant='@json($variantData)'
                                {{ isset($defaultVariant) && (int) $defaultVariant->id === (int) $variant->id ? 'selected' : '' }}
                            >
                                {{ $variant->display_name }} | ₱{{ number_format($variantDisplayPrice, 2) }} | Stock: {{ (int) ($variant->stock ?? 0) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Select size/color variant before adding to cart or buying now.</p>
                </div>
            @endif

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

            @if($product->is_bundle && $product->bundleItems->isNotEmpty())
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Included in this bundle</h3>
                    <ul class="space-y-2">
                        @foreach($product->bundleItems as $bundleItem)
                            <li class="flex items-center justify-between text-sm text-gray-700">
                                <span>{{ $bundleItem->componentProduct->name ?? 'Item' }}</span>
                                <span class="font-semibold" style="color: #800000;">x{{ (int) $bundleItem->quantity }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Quantity Selection -->
            <div class="flex items-center gap-4">
                <label for="qty" class="text-sm font-semibold text-gray-700">Quantity:</label>
                <div class="flex items-center border border-gray-300 rounded-lg">
                    <button type="button" onclick="decrementQty()" class="px-3 py-2 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                    </button>
                    <input id="qty" name="quantity" type="number" min="1" max="{{ $availableStock ?? 999 }}" value="1" 
                           class="w-16 text-center border-0 focus:ring-0"
                           oninput="validateQty(this)" onblur="clampQty(this)">
                    <button type="button" onclick="incrementQty()" class="px-3 py-2 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
                <span class="text-sm text-gray-500">({{ $availableStock ?? 0 }} available)</span>
            </div>
            <p id="qtyError" class="hidden text-sm font-medium text-red-600">Quantity cannot exceed available stock.</p>

            <!-- Purchase Options -->
            <div class="space-y-3">
                <!-- Buttons Container -->
                <div class="flex flex-col sm:flex-row gap-3 items-stretch">
                    <!-- Add to Cart -->
                    <form id="addToCartForm" method="POST" action="{{ route('cart.add', $product) }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="quantity" id="cartQty" value="1">
                        <input type="hidden" name="variant_id" id="cartVariantId" value="{{ isset($defaultVariant) ? $defaultVariant->id : '' }}">
                        <button type="submit" 
                                class="w-full h-full text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 whitespace-nowrap"
                                style="background: linear-gradient(135deg, #800000 0%, #600000 100%);"
                                onmouseover="this.style.background='linear-gradient(135deg, #600000 0%, #400000 100%)'"
                                onmouseout="this.style.background='linear-gradient(135deg, #800000 0%, #600000 100%)'"
                                @if($availableStock == 0) disabled @endif>
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>
                                @if($availableStock > 0)
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
                    <input type="hidden" name="variant_id" id="buyNowVariantId" value="{{ isset($defaultVariant) ? $defaultVariant->id : '' }}">
                    <input type="hidden" name="buy_now" value="1">
                    <button type="submit" 
                            id="buyNowBtn"
                            class="w-full h-full border-2 px-6 py-3 rounded-xl font-semibold transition-all duration-300 flex items-center justify-center gap-2 whitespace-nowrap"
                            style="border-color: #800000; color: #800000;"
                            onmouseover="this.style.backgroundColor='#fff5f5'"
                            onmouseout="this.style.backgroundColor='transparent'"
                            @if($availableStock == 0) disabled @endif
                    >
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span id="buyNowBtnText">
                            @if($availableStock > 0)
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
                                @if($related->image)
                                    <img src="{{ $related->image_src }}" alt="{{ $related->name }}" class="w-12 h-12 object-cover rounded-lg" />
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
                @php
                    $reviews    = \App\Models\Review::where('product_id', $product->id)->where('is_approved', true)->with('user')->orderByDesc('created_at')->get();
                    $ratingAvg  = $reviews->count() ? round($reviews->avg('rating'), 1) : 0;
                    $ratingDist = $reviews->groupBy('rating')->map->count();
                @endphp

                <!-- Header + Rating Summary -->
                <div class="flex flex-col sm:flex-row gap-6 mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Customer Reviews</h2>
                        @if($reviews->count())
                            <div class="flex items-center gap-2">
                                <span class="text-4xl font-extrabold" style="color:#800000;">{{ $ratingAvg }}</span>
                                <div>
                                    <div class="flex text-yellow-400 text-xl">
                                        @for($i=1;$i<=5;$i++)<span>{{ $i <= round($ratingAvg) ? '★' : '☆' }}</span>@endfor
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $reviews->count() }} {{ Str::plural('review', $reviews->count()) }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    @if($reviews->count())
                    <div class="flex-1 space-y-1.5 max-w-xs">
                        @for($s=5;$s>=1;$s--)
                            @php $cnt = $ratingDist[$s] ?? 0; $pct = $reviews->count() ? round($cnt/$reviews->count()*100) : 0; @endphp
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-4 text-right text-gray-600 font-semibold">{{ $s }}</span>
                                <svg class="w-3 h-3 text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full" style="width:{{ $pct }}%; background:#800000;"></div>
                                </div>
                                <span class="text-gray-500 w-8">{{ $cnt }}</span>
                            </div>
                        @endfor
                    </div>
                    @endif
                </div>

                <!-- Reviews List -->
                @if($reviews->count() > 0)
                    <div class="space-y-6 mb-8">
                        @foreach($reviews as $review)
                            <div class="border-b pb-6 last:border-b-0">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#800000,#600000);">
                                            <span class="text-white font-bold text-sm">{{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $review->user->name ?? 'Anonymous' }}</p>
                                            <p class="text-xs text-gray-500">{{ $review->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    @if($review->verified_purchase)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Verified Purchase
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex text-yellow-400">
                                        @for($i=1;$i<=5;$i++)<span class="text-lg">{{ $i <= $review->rating ? '★' : '☆' }}</span>@endfor
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700">{{ $review->rating }}/5</span>
                                </div>
                                @if($review->title)<h4 class="font-semibold text-gray-900 mb-1">{{ $review->title }}</h4>@endif
                                @if($review->comment)<p class="text-gray-700 mb-3 leading-relaxed text-sm">{{ $review->comment }}</p>@endif
                                @if($review->review_images && count($review->review_images))
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @foreach($review->review_images as $img)
                                            <img src="{{ $img }}" alt="Review photo" class="w-20 h-20 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90" onclick="this.requestFullscreen&&this.requestFullscreen()">
                                        @endforeach
                                    </div>
                                @endif
                                <div class="flex items-center gap-4 text-sm">
                                    <button class="flex items-center gap-1 text-gray-500 hover:text-green-600 transition-colors" onclick="markHelpful({{ $review->id }}, this)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.646 7.23A2 2 0 0117 18H7a2 2 0 01-2-2V9a6 6 0 0112-6z"/></svg>
                                        Helpful (<span class="helpful-count-{{ $review->id }}">{{ $review->helpful_count }}</span>)
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-gray-400 text-sm mb-6">No reviews yet. Be the first to review this product!</div>
                @endif

                <!-- Leave a Review -->
                <div class="pt-6 border-t">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Leave a Review</h3>

                    @auth
                        @if($userOrderItem)
                            {{-- User has a delivered order with this product and hasn't reviewed yet --}}
                            <form action="{{ route('reviews.store.order-item', $userOrderItem) }}" method="POST" enctype="multipart/form-data" id="product-review-form">
                                @csrf
                                <div class="space-y-4">
                                    {{-- Star Rating --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rating <span class="text-red-500">*</span></label>
                                        <div class="flex gap-1" id="product-stars">
                                            @for($s=1;$s<=5;$s++)
                                                <button type="button" data-value="{{ $s }}"
                                                    class="product-star-btn w-9 h-9 text-gray-300 hover:text-yellow-400 transition-colors"
                                                    onclick="setProductRating({{ $s }})">
                                                    <svg fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                </button>
                                            @endfor
                                        </div>
                                        <input type="hidden" name="rating" id="product-rating-input" required>
                                        <p class="text-xs text-gray-400 mt-1" id="rating-label">Click a star to rate</p>
                                    </div>

                                    {{-- Title --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Review Title</label>
                                        <input type="text" name="title" maxlength="255" placeholder="Summarize your experience"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                                    </div>

                                    {{-- Comment --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Your Review</label>
                                        <textarea name="comment" rows="4" maxlength="1000" placeholder="Share your experience with this product..."
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent resize-none"></textarea>
                                    </div>

                                    {{-- Photos --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Photos <span class="text-gray-400 font-normal">(optional, up to 5)</span></label>
                                        <input type="file" name="images[]" accept="image/*" multiple
                                            class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#800000] file:text-white hover:file:bg-[#600000] cursor-pointer"
                                            onchange="previewProductImages(this)">
                                        <div id="product-img-preview" class="flex flex-wrap gap-2 mt-2"></div>
                                    </div>

                                    <button type="submit"
                                        class="inline-flex items-center gap-2 bg-[#800000] hover:bg-[#600000] text-white font-bold py-2.5 px-6 rounded-lg transition-colors duration-200 shadow">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Submit Review
                                    </button>
                                </div>
                            </form>

                        @elseif($userReview)
                            {{-- Already reviewed --}}
                            <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span class="font-semibold text-green-800">You already reviewed this product</span>
                                    <span class="ml-auto text-xs text-gray-500">{{ $userReview->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="flex text-yellow-400 mb-1">
                                    @for($i=1;$i<=5;$i++)<span>{{ $i <= $userReview->rating ? '★' : '☆' }}</span>@endfor
                                </div>
                                @if($userReview->title)<p class="font-semibold text-gray-800 text-sm">"{{ $userReview->title }}"</p>@endif
                                @if($userReview->comment)<p class="text-gray-700 text-sm mt-1">{{ $userReview->comment }}</p>@endif
                            </div>

                        @else
                            {{-- Not purchased or not yet delivered --}}
                            <div class="bg-amber-50 rounded-xl p-4 border border-amber-200 flex items-start gap-3">
                                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-amber-800 text-sm">You can leave a review after purchasing this product and receiving your order. Once your order status is <strong>Delivered</strong>, a review form will appear here and in your <a href="{{ route('orders.index') }}" class="underline font-semibold">order history</a>.</p>
                            </div>
                        @endif

                    @else
                        <div class="bg-gray-50 rounded-xl p-5 text-center">
                            <p class="text-gray-600 text-sm">Please <a href="{{ route('login') }}" class="text-[#800000] font-semibold hover:underline">log in</a> to leave a review.</p>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';

// ── Helpful button (no page reload)
function markHelpful(reviewId, btn) {
    fetch(`/reviews/${reviewId}/helpful`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const el = document.querySelector(`.helpful-count-${reviewId}`);
            if (el) el.textContent = data.helpful_count;
            if (btn) { btn.classList.add('text-green-600'); btn.disabled = true; }
        }
    })
    .catch(console.error);
}

// ── Star rating for the product page inline form
const ratingLabels = ['','Terrible','Poor','Okay','Good','Excellent'];
function setProductRating(value) {
    document.getElementById('product-rating-input').value = value;
    document.getElementById('rating-label').textContent = ratingLabels[value] || '';
    document.querySelectorAll('.product-star-btn').forEach(btn => {
        const v = parseInt(btn.getAttribute('data-value'));
        btn.classList.toggle('text-yellow-400', v <= value);
        btn.classList.toggle('text-gray-300',   v >  value);
    });
}

// Hover preview
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.product-star-btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const v = parseInt(this.getAttribute('data-value'));
            document.querySelectorAll('.product-star-btn').forEach(b => {
                const bv = parseInt(b.getAttribute('data-value'));
                b.classList.toggle('text-yellow-300', bv <= v);
                b.classList.toggle('text-gray-300',   bv >  v);
            });
        });
        btn.addEventListener('mouseleave', function() {
            const selected = parseInt(document.getElementById('product-rating-input').value || 0);
            document.querySelectorAll('.product-star-btn').forEach(b => {
                const bv = parseInt(b.getAttribute('data-value'));
                b.classList.toggle('text-yellow-400', bv <= selected);
                b.classList.toggle('text-gray-300',   bv >  selected);
            });
        });
    });

    // Validate star selection before submit
    const reviewForm = document.getElementById('product-review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            if (!document.getElementById('product-rating-input').value) {
                e.preventDefault();
                alert('Please select a star rating.');
            }
        });
    }
});

// ── Image preview for product review form
function previewProductImages(input) {
    const preview = document.getElementById('product-img-preview');
    preview.innerHTML = '';
    Array.from(input.files).slice(0, 5).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-20 h-20 object-cover rounded-lg border border-gray-200';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>

<script>
const HAS_VARIANTS = @json(!empty($hasVariants));

function formatPeso(amount) {
    const numeric = Number(amount || 0);
    return '₱' + numeric.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function syncSelectedVariant(variantId) {
    const cartVariantInput = document.getElementById('cartVariantId');
    const buyNowVariantInput = document.getElementById('buyNowVariantId');
    if (cartVariantInput) cartVariantInput.value = variantId || '';
    if (buyNowVariantInput) buyNowVariantInput.value = variantId || '';
}

function applyVariantFromSelector() {
    const selector = document.getElementById('variantSelector');
    if (!selector) {
        return;
    }

    const selectedOption = selector.options[selector.selectedIndex];
    const variantDataRaw = selectedOption ? selectedOption.getAttribute('data-variant') : null;
    if (!variantDataRaw) {
        return;
    }

    let variant = null;
    try {
        variant = JSON.parse(variantDataRaw);
    } catch (error) {
        return;
    }

    syncSelectedVariant(variant.id || '');

    const displayPrice = document.getElementById('productDisplayPrice');
    const originalPriceWrap = document.getElementById('productOriginalPriceWrap');
    const originalPrice = document.getElementById('productOriginalPrice');
    const savingsBadge = document.getElementById('productSavingsBadge');
    const savingsValue = document.getElementById('productSavingsValue');
    const stockDot = document.getElementById('productStockDot');
    const stockText = document.getElementById('productStockText');
    const qtyInput = document.getElementById('qty');
    const addToCartBtn = document.querySelector('#addToCartForm button[type="submit"]');
    const buyNowBtn = document.getElementById('buyNowBtn');
    const buyNowBtnText = document.getElementById('buyNowBtnText');

    const variantPrice = Number(variant.price || 0);
    const variantOriginal = Number(variant.original_price || variantPrice);
    const variantStock = Number(variant.stock || 0);

    if (displayPrice) {
        displayPrice.textContent = formatPeso(variantPrice);
    }
    if (originalPrice) {
        originalPrice.textContent = formatPeso(variantOriginal);
    }

    const hasSavings = variantOriginal > variantPrice;
    if (originalPriceWrap) originalPriceWrap.classList.toggle('hidden', !hasSavings);
    if (savingsBadge) savingsBadge.classList.toggle('hidden', !hasSavings);
    if (savingsValue && hasSavings) {
        savingsValue.textContent = (variantOriginal - variantPrice).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (stockDot) {
        stockDot.classList.toggle('bg-green-500', variantStock > 0);
        stockDot.classList.toggle('animate-pulse', variantStock > 0);
        stockDot.classList.toggle('bg-red-500', variantStock <= 0);
    }
    if (stockText) {
        stockText.classList.toggle('text-green-700', variantStock > 0);
        stockText.classList.toggle('text-red-700', variantStock <= 0);
        stockText.textContent = variantStock > 0
            ? `${variantStock} units in stock - Ready to ship`
            : 'Out of stock';
    }

    if (qtyInput) {
        qtyInput.max = String(Math.max(variantStock, 1));
        const currentQty = Number(qtyInput.value || 1);
        if (variantStock <= 0) {
            qtyInput.value = '1';
            qtyInput.disabled = true;
        } else {
            qtyInput.disabled = false;
            if (currentQty > variantStock) {
                qtyInput.value = String(variantStock);
            }
            if (currentQty < 1) {
                qtyInput.value = '1';
            }
        }
    }

    if (addToCartBtn) {
        addToCartBtn.disabled = variantStock <= 0;
    }
    if (buyNowBtn) {
        buyNowBtn.disabled = variantStock <= 0;
    }
    if (buyNowBtnText) {
        buyNowBtnText.textContent = variantStock > 0 ? 'Buy Now' : 'Out of Stock';
    }

    updateHiddenInputs();
}

// Quantity controls
function showQtyError(show) {
    const qtyError = document.getElementById('qtyError');
    if (!qtyError) return;
    qtyError.classList.toggle('hidden', !show);
}

function isQtyValid() {
    const input = document.getElementById('qty');
    if (!input) return true;

    const maxValue = parseInt(input.max) || 999;
    const minValue = parseInt(input.min) || 1;
    const currentValue = parseInt(input.value) || minValue;
    const valid = currentValue >= minValue && currentValue <= maxValue;

    showQtyError(!valid);
    return valid;
}

function incrementQty() {
    const input = document.getElementById('qty');
    const maxValue = parseInt(input.max) || 999;
    const currentValue = parseInt(input.value) || 1;
    if (currentValue < maxValue) {
        input.value = currentValue + 1;
        updateHiddenInputs();
        showQtyError(false);
    } else {
        showQtyError(true);
    }
}

function decrementQty() {
    const input = document.getElementById('qty');
    const currentValue = parseInt(input.value) || 1;
    if (currentValue > 1) {
        input.value = currentValue - 1;
        updateHiddenInputs();
        showQtyError(false);
    }
}

function updateHiddenInputs() {
    const qty = document.getElementById('qty').value;
    document.getElementById('cartQty').value = qty;
    document.getElementById('buyNowQty').value = qty;
}

function validateQty(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
    updateHiddenInputs();
    isQtyValid();
}

function clampQty(input) {
    let val = parseInt(input.value) || 1;
    const min = parseInt(input.min) || 1;
    const max = parseInt(input.max) || 999;
    if (val < min) val = min;
    if (val > max) val = max;
    input.value = val;
    updateHiddenInputs();
    showQtyError(false);
}

const addToCartForm = document.getElementById('addToCartForm');
if (addToCartForm) {
    addToCartForm.addEventListener('submit', function(e) {
        if (!isQtyValid()) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });
}

const buyNowForm = document.getElementById('buyNowForm');
if (buyNowForm) {
    buyNowForm.addEventListener('submit', function(e) {
        if (!isQtyValid()) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });
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
    @guest
        window.location.href = '{{ route("login") }}';
        return;
    @endguest
    
    const btn = document.getElementById('wishlistBtn');
    const btnText = document.getElementById('wishlistBtnText');
    const btnTextMobile = document.getElementById('wishlistBtnTextMobile');
    
    // Disable button temporarily
    btn.disabled = true;
    btnText.textContent = 'Loading...';
    btnTextMobile.textContent = 'Loading...';
    
    const action = btn.classList.contains('in-wishlist') ? 'remove' : 'add';
    
    fetch('{{ route("wishlist.add") }}', {
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
        btn.classList.add('in-wishlist');
        btn.classList.remove('border-gray-300', 'text-gray-700');
        btn.style.borderColor = '#800000';
        btn.style.color = '#800000';
        btn.style.backgroundColor = '#fff5f5';
        btnText.textContent = 'In Wishlist';
        btnTextMobile.textContent = 'In Wishlist';
    } else {
        btn.classList.remove('in-wishlist');
        btn.classList.add('border-gray-300', 'text-gray-700');
        btn.style.borderColor = '';
        btn.style.color = '';
        btn.style.backgroundColor = '';
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
    const variantSelector = document.getElementById('variantSelector');
    if (variantSelector) {
        variantSelector.addEventListener('change', applyVariantFromSelector);
        applyVariantFromSelector();
    } else {
        syncSelectedVariant('');
    }

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
