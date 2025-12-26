@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('custom_orders.show', $customOrder) }}" class="inline-flex items-center text-maroon-600 hover:text-maroon-700 mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Order
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Review Your Custom Order</h1>
            <p class="text-gray-600 mt-2">Order ID: {{ $customOrder->id }}</p>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Details</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Product</p>
                    <p class="font-semibold text-gray-900">{{ $customOrder->product?->name ?? 'Custom Product' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Quantity</p>
                    <p class="font-semibold text-gray-900">{{ $customOrder->quantity ?? 1 }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $customOrder->status)) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Order Date</p>
                    <p class="font-semibold text-gray-900">{{ $customOrder->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Review Form -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                {{ $existingReview ? 'Update Your Review' : 'Share Your Experience' }}
            </h3>

            <form action="{{ route('reviews.store.custom-order', $customOrder) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Rating -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">How would you rate this order?</label>
                    <div class="flex gap-3">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" class="rating-btn group" data-rating="{{ $i }}">
                                <span class="text-5xl transition-colors duration-200 {{ $existingReview && $existingReview->rating >= $i ? 'text-yellow-400' : 'text-gray-300 group-hover:text-yellow-300' }}">★</span>
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating" value="{{ $existingReview?->rating ?? 0 }}" required>
                    @error('rating')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Review Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g., Excellent quality and fast delivery" 
                        value="{{ $existingReview?->title ?? old('title') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-transparent">
                    @error('title')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Comment -->
                <div>
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                    <textarea id="comment" name="comment" rows="6" placeholder="Tell us about your experience with this custom order. What did you like? Any suggestions for improvement?"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-transparent">{{ $existingReview?->comment ?? old('comment') }}</textarea>
                    <p class="text-xs text-gray-500 mt-2">Maximum 1000 characters</p>
                    @error('comment')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Verified Purchase Badge -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-medium text-green-900">Verified Purchase</p>
                        <p class="text-sm text-green-700">Your review will be marked as a verified purchase</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-maroon-600 to-maroon-700 text-white font-semibold py-3 rounded-lg hover:from-maroon-700 hover:to-maroon-800 transition duration-200 shadow-md hover:shadow-lg">
                        {{ $existingReview ? 'Update Review' : 'Submit Review' }}
                    </button>
                    @if($existingReview)
                        <form action="{{ route('reviews.destroy', $existingReview) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this review?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-6 py-3 border border-red-300 text-red-600 font-semibold rounded-lg hover:bg-red-50 transition duration-200">
                                Delete Review
                            </button>
                        </form>
                    @endif
                </div>
            </form>
        </div>

        <!-- Review Guidelines -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="font-semibold text-blue-900 mb-3">Review Guidelines</h4>
            <ul class="text-sm text-blue-800 space-y-2">
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 font-bold">•</span>
                    <span>Be honest and fair in your review</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 font-bold">•</span>
                    <span>Focus on the product quality and your experience</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 font-bold">•</span>
                    <span>Avoid offensive language or personal attacks</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 font-bold">•</span>
                    <span>Your review will be moderated before publishing</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.rating-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const rating = this.dataset.rating;
        document.getElementById('rating').value = rating;
        
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
