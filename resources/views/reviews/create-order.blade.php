@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center text-maroon-600 hover:text-maroon-700 mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Order
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Review Your Order</h1>
            <p class="text-gray-600 mt-2">Order #{{ $order->order_ref }}</p>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h2>
            <div class="space-y-4">
                @foreach($items as $item)
                    <div class="flex items-start justify-between pb-4 border-b border-gray-200 last:border-b-0">
                        <div class="flex items-start gap-4">
                            @if($item->product && $item->product->image)
                                <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name }}" class="w-20 h-20 object-cover rounded">
                            @else
                                <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $item->product_name }}</h3>
                                <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                                <p class="text-sm font-medium text-maroon-600">₱{{ number_format($item->price, 2) }}</p>
                            </div>
                        </div>

                        <!-- Review Status -->
                        @php
                            $itemReview = $existingReviews->firstWhere('order_item_id', $item->id);
                        @endphp
                        @if($itemReview)
                            <div class="text-right">
                                <div class="flex items-center gap-1 mb-2">
                                    @for($i = 0; $i < $itemReview->rating; $i++)
                                        <span class="text-yellow-400">★</span>
                                    @endfor
                                    @for($i = $itemReview->rating; $i < 5; $i++)
                                        <span class="text-gray-300">★</span>
                                    @endfor
                                </div>
                                <p class="text-xs text-gray-500">Reviewed</p>
                            </div>
                        @else
                            <a href="#item-{{ $item->id }}" class="text-maroon-600 hover:text-maroon-700 font-medium text-sm">
                                Write Review
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Review Forms -->
        <div class="space-y-8">
            @foreach($items as $item)
                @php
                    $itemReview = $existingReviews->firstWhere('order_item_id', $item->id);
                @endphp
                <div id="item-{{ $item->id }}" class="bg-white rounded-lg shadow-md p-6 scroll-mt-20">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        {{ $itemReview ? 'Update Review' : 'Write a Review' }} - {{ $item->product_name }}
                    </h3>

                    <form action="{{ route('reviews.store.order-item', $item) }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Rating -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Rating</label>
                            <div class="flex gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" class="rating-btn group" data-rating="{{ $i }}" data-item="{{ $item->id }}">
                                        <span class="text-4xl transition-colors duration-200 {{ $itemReview && $itemReview->rating >= $i ? 'text-yellow-400' : 'text-gray-300 group-hover:text-yellow-300' }}">★</span>
                                    </button>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="rating-{{ $item->id }}" value="{{ $itemReview?->rating ?? 0 }}" required>
                            @error('rating')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div>
                            <label for="title-{{ $item->id }}" class="block text-sm font-medium text-gray-700 mb-2">Review Title</label>
                            <input type="text" id="title-{{ $item->id }}" name="title" placeholder="Summarize your experience" 
                                value="{{ $itemReview?->title ?? old('title') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-transparent">
                            @error('title')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Comment -->
                        <div>
                            <label for="comment-{{ $item->id }}" class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                            <textarea id="comment-{{ $item->id }}" name="comment" rows="5" placeholder="Share your experience with this product..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-transparent">{{ $itemReview?->comment ?? old('comment') }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">Maximum 1000 characters</p>
                            @error('comment')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-gradient-to-r from-maroon-600 to-maroon-700 text-white font-semibold py-3 rounded-lg hover:from-maroon-700 hover:to-maroon-800 transition duration-200 shadow-md hover:shadow-lg">
                                {{ $itemReview ? 'Update Review' : 'Submit Review' }}
                            </button>
                            @if($itemReview)
                                <form action="{{ route('reviews.destroy', $itemReview) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-6 py-3 border border-red-300 text-red-600 font-semibold rounded-lg hover:bg-red-50 transition duration-200">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.rating-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const rating = this.dataset.rating;
        const itemId = this.dataset.item;
        document.getElementById(`rating-${itemId}`).value = rating;
        
        // Update visual feedback
        this.parentElement.querySelectorAll('.rating-btn').forEach(b => {
            const bRating = b.dataset.rating;
            const star = b.querySelector('span');
            if (bRating <= rating) {
                star.classList.remove('text-gray-300', 'group-hover:text-yellow-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300', 'group-hover:text-yellow-300');
            }
        });
    });
});
</script>
@endsection
