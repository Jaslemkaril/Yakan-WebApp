@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-maroon-700 to-maroon-800 shadow-2xl" style="background: linear-gradient(to right, #800000, #600000);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-white">My Wishlist</h1>
                    <p class="text-maroon-100 mt-2">{{ $wishlist->items->count() }} items saved</p>
                </div>
                <div class="flex gap-2">
                    <span class="inline-flex items-center px-3 py-1 text-sm font-bold rounded-full bg-blue-100 text-blue-800">
                        {{ $wishlist->name }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        @if($wishlist->items->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($wishlist->items as $item)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        @php
                            $entity = $item->item;
                        @endphp
                        @if($entity instanceof \App\Models\Product)
                            <a href="{{ route('products.show', $entity) }}">
                                @if($entity->image)
                                    <img src="{{ asset('uploads/products/' . $entity->image) }}" alt="{{ $entity->name }}" class="w-full h-56 object-cover" />
                                @else
                                    <div class="w-full h-56 bg-gray-200 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                            </a>
                            <div class="p-4">
                                <h3 class="text-lg font-black text-gray-900 truncate">{{ $entity->name }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $entity->category->name ?? 'No Category' }}</p>
                                <div class="flex items-center justify-between mt-3">
                                    <span class="text-xl font-bold text-maroon-600">₱{{ number_format($entity->price, 2) }}</span>
                                    <form action="{{ route('wishlist.remove') }}" method="POST" onsubmit="return confirm('Remove from wishlist?')">
                                        @csrf
                                        <input type="hidden" name="type" value="product" />
                                        <input type="hidden" name="id" value="{{ $entity->id }}" />
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold">Remove</button>
                                    </form>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <a href="{{ route('products.show', $entity) }}" class="flex-1 text-center px-3 py-1 bg-maroon-600 text-white text-sm rounded-lg hover:bg-maroon-700 transition-colors">View</a>
                                    <form action="{{ route('cart.add') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $entity->id }}" />
                                        <input type="hidden" name="quantity" value="1" />
                                        <button type="submit" class="flex-1 px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        @elseif($entity instanceof \App\Models\YakanPattern)
                            <a href="{{ route('patterns.show', $entity) }}">
                                @if($entity->media->isNotEmpty())
                                    <img src="{{ $entity->media->first()->url }}" alt="{{ $entity->media->first()->alt_text ?? $entity->name }}" class="w-full h-56 object-cover" />
                                @else
                                    <div class="w-full h-56 bg-gray-200 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </a>
                            <div class="p-4">
                                <h3 class="text-lg font-black text-gray-900 truncate">{{ $entity->name }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $entity->category }} • {{ ucfirst($entity->difficulty_level) }}</p>
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($entity->tags->take(2) as $tag)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-full bg-maroon-100 text-maroon-800">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                                <div class="flex items-center justify-between mt-3">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-full {{ $entity->difficulty_level == 'simple' ? 'bg-green-100 text-green-800' : ($entity->difficulty_level == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($entity->difficulty_level) }}
                                    </span>
                                    <form action="{{ route('wishlist.remove') }}" method="POST" onsubmit="return confirm('Remove from wishlist?')">
                                        @csrf
                                        <input type="hidden" name="type" value="pattern" />
                                        <input type="hidden" name="id" value="{{ $entity->id }}" />
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold">Remove</button>
                                    </form>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <a href="{{ route('patterns.show', $entity) }}" class="flex-1 text-center px-3 py-1 bg-maroon-600 text-white text-sm rounded-lg hover:bg-maroon-700 transition-colors">View</a>
                                    <a href="{{ route('custom_orders.create') }}?pattern_id={{ $entity->id }}" class="flex-1 text-center px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors">Order</a>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <h3 class="text-xl font-black text-gray-900 mb-2">Your wishlist is empty</h3>
                <p class="text-gray-600 mb-6">Start adding products and patterns you love!</p>
                <div class="flex gap-4 justify-center">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center px-6 py-3 bg-maroon-600 text-white font-black rounded-lg hover:bg-maroon-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Browse Products
                    </a>
                    <a href="{{ route('patterns.index') }}" class="inline-flex items-center px-6 py-3 bg-maroon-600 text-white font-black rounded-lg hover:bg-maroon-700 transition-colors">
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
