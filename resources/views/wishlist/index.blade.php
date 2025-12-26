@extends('layouts.app')

@section('title', 'My Wishlist')

@push('styles')
<style>
    .wishlist-hero {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        position: relative;
        overflow: hidden;
    }

    .wishlist-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    .wishlist-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 2px solid #f3f4f6;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .wishlist-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 32px rgba(128, 0, 0, 0.15);
        border-color: #800000;
    }

    .wishlist-image {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        height: 200px;
    }

    .wishlist-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .wishlist-card:hover .wishlist-image img {
        transform: scale(1.08);
    }

    .wishlist-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(128, 0, 0, 0.9);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        backdrop-filter: blur(10px);
    }

    .wishlist-content {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .wishlist-title {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .wishlist-meta {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .wishlist-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }

    .wishlist-tag {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 700;
    }

    .wishlist-price {
        font-size: 20px;
        font-weight: 700;
        color: #800000;
        margin-bottom: 12px;
    }

    .wishlist-actions {
        display: flex;
        gap: 8px;
        margin-top: auto;
    }

    .wishlist-btn {
        flex: 1;
        padding: 10px 12px;
        border-radius: 8px;
        border: none;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .wishlist-btn-primary {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
    }

    .wishlist-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(128, 0, 0, 0.3);
    }

    .wishlist-btn-secondary {
        background: #ecfdf5;
        color: #047857;
        border: 2px solid #d1fae5;
        font-weight: 700;
    }

    .wishlist-btn-secondary:hover {
        background: #d1fae5;
        transform: translateY(-2px);
    }

    .wishlist-remove {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .wishlist-remove:hover {
        background: #fecaca;
        transform: scale(1.05);
    }

    .empty-wishlist {
        background: white;
        border-radius: 16px;
        padding: 4rem 2rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 2px solid #f3f4f6;
    }

    .empty-wishlist-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        box-shadow: 0 8px 20px rgba(236, 72, 153, 0.2);
    }

    .empty-wishlist-icon svg {
        width: 60px;
        height: 60px;
        color: #be185d;
    }

    .wishlist-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-radius: 20px;
        font-weight: 700;
        font-size: 14px;
        backdrop-filter: blur(10px);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="wishlist-hero py-12 relative">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl lg:text-5xl font-bold text-white mb-2 flex items-center gap-3">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        My Wishlist
                    </h1>
                    <p class="text-lg text-gray-100">Your collection of favorite items</p>
                </div>
                <div class="wishlist-count-badge">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span>{{ $wishlist->items->count() }} items saved</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12">
        @if($wishlist->items->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($wishlist->items as $item)
                    <div class="wishlist-card">
                        @php
                            $entity = $item->item;
                        @endphp
                        @if($entity instanceof \App\Models\Product)
                            <div class="wishlist-image">
                                <a href="{{ route('products.show', $entity) }}" class="block w-full h-full">
                                    @if($entity->image)
                                        <img src="{{ asset('uploads/products/' . $entity->image) }}" alt="{{ $entity->name }}" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                                <div class="wishlist-badge">Product</div>
                            </div>
                            <div class="wishlist-content">
                                <h3 class="wishlist-title">{{ $entity->name }}</h3>
                                <p class="wishlist-meta">{{ $entity->category->name ?? 'No Category' }}</p>
                                <div class="wishlist-price">₱{{ number_format($entity->price, 2) }}</div>
                                <div class="wishlist-actions">
                                    <a href="{{ route('products.show', $entity) }}" class="wishlist-btn wishlist-btn-primary">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                    <form action="{{ route('cart.add', $entity) }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1" />
                                        <button type="submit" class="wishlist-btn wishlist-btn-secondary w-full">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Add
                                        </button>
                                    </form>
                                </div>
                                <form action="{{ route('wishlist.remove') }}" method="POST" onsubmit="return confirm('Remove from wishlist?')" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="type" value="product" />
                                    <input type="hidden" name="id" value="{{ $entity->id }}" />
                                    <button type="submit" class="wishlist-remove w-full">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Remove
                                    </button>
                                </form>
                            </div>
                        @elseif($entity instanceof \App\Models\YakanPattern)
                            <div class="wishlist-image">
                                <a href="{{ route('patterns.show', $entity) }}" class="block w-full h-full">
                                    @if($entity->media->isNotEmpty())
                                        <img src="{{ $entity->media->first()->url }}" alt="{{ $entity->media->first()->alt_text ?? $entity->name }}" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                                <div class="wishlist-badge">Pattern</div>
                            </div>
                            <div class="wishlist-content">
                                <h3 class="wishlist-title">{{ $entity->name }}</h3>
                                <p class="wishlist-meta">{{ $entity->category }} • {{ ucfirst($entity->difficulty_level) }}</p>
                                <div class="wishlist-tags">
                                    @foreach($entity->tags->take(2) as $tag)
                                        <span class="wishlist-tag">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                                <div class="wishlist-actions">
                                    <a href="{{ route('patterns.show', $entity) }}" class="wishlist-btn wishlist-btn-primary">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                    <a href="{{ route('custom_orders.create') }}?pattern_id={{ $entity->id }}" class="wishlist-btn wishlist-btn-secondary">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Order
                                    </a>
                                </div>
                                <form action="{{ route('wishlist.remove') }}" method="POST" onsubmit="return confirm('Remove from wishlist?')" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="type" value="pattern" />
                                    <input type="hidden" name="id" value="{{ $entity->id }}" />
                                    <button type="submit" class="wishlist-remove w-full">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Remove
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty Wishlist -->
            <div class="empty-wishlist">
                <div class="empty-wishlist-icon">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                
                <h3 class="text-3xl font-bold text-gray-900 mb-3">Your wishlist is empty</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto text-lg leading-relaxed">
                    Start adding products and patterns you love to your wishlist!
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-maroon-600 to-maroon-700 text-white font-bold rounded-lg hover:shadow-lg transition-all transform hover:-translate-y-1">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Browse Products
                    </a>
                    <a href="{{ route('patterns.index') }}" class="inline-flex items-center px-8 py-3 bg-white text-maroon-600 font-bold border-2 border-maroon-600 rounded-lg hover:bg-maroon-50 transition-all transform hover:-translate-y-1">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Browse Patterns
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
