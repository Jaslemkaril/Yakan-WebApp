@extends('layouts.app')

@section('title', 'Write a Review')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('products.show', $product) }}" class="inline-flex items-center text-maroon-600 hover:text-maroon-800 font-bold mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Product
            </a>
            <h1 class="text-3xl font-black text-gray-900">Write a Review</h1>
            <p class="text-gray-600 mt-2">Share your experience with {{ $product->name }}</p>
        </div>

        <!-- Product Info -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center">
                @if($product->image)
                    <img class="h-16 w-16 rounded-lg object-cover" src="{{ asset('uploads/products/' . $product->image) }}" alt="">
                @else
                    <div class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                @endif
                <div class="ml-4">
                    <h2 class="text-lg font-black text-gray-900">{{ $product->name }}</h2>
                    <p class="text-gray-600">${{ number_format($product->price, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Review Form -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <form method="POST" action="{{ route('reviews.store', $product) }}" class="p-6 space-y-6">
                @csrf
                
                <!-- Rating -->
                <div>
                    <label class="block text-sm font-black text-gray-700 mb-4">Rating *</label>
                    <div class="flex items-center space-x-2">
                        <div id="starRating" class="flex space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" class="star-btn text-3xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="{{ $i }}">
                                    <i class="far fa-star"></i>
                                </button>
                            @endfor
                        </div>
                        <span id="ratingText" class="ml-3 text-gray-600 font-medium">Click to rate</span>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="0" required>
                    @error('rating')
                        <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-black text-gray-700 mb-2">Review Title (Optional)</label>
                    <input type="text" name="title" maxlength="255" placeholder="Summarize your experience"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Comment -->
                <div>
                    <label class="block text-sm font-black text-gray-700 mb-2">Your Review *</label>
                    <textarea name="comment" rows="6" maxlength="2000" required
                              placeholder="Tell us about your experience with this product. What did you like? What could be improved?"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-maroon-500 focus:border-maroon-500"></textarea>
                    <div class="mt-2 text-right">
                        <span id="charCount" class="text-sm text-gray-500">0 / 2000</span>
                    </div>
                    @error('comment')
                        <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Guidelines -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-sm font-black text-blue-800 mb-2">Review Guidelines:</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Be honest and detailed in your review</li>
                        <li>• Focus on the product quality and features</li>
                        <li>• Avoid offensive language or personal attacks</li>
                        <li>• Your review will help other customers make informed decisions</li>
                    </ul>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" class="flex-1 px-6 py-3 bg-maroon-600 hover:bg-maroon-700 text-white font-black rounded-lg shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Submit Review
                    </button>
                    <a href="{{ route('products.show', $product) }}" class="flex-1 px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition-all text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const starButtons = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('ratingInput');
    const ratingText = document.getElementById('ratingText');
    const commentTextarea = document.querySelector('textarea[name="comment"]');
    const charCount = document.getElementById('charCount');
    
    const ratingTexts = {
        1: 'Poor',
        2: 'Fair', 
        3: 'Good',
        4: 'Very Good',
        5: 'Excellent'
    };

    starButtons.forEach(button => {
        button.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            ratingText.textContent = ratingTexts[rating];
            
            starButtons.forEach((btn, index) => {
                const star = btn.querySelector('i');
                if (index < rating) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                    btn.classList.remove('text-gray-300');
                    btn.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                    btn.classList.remove('text-yellow-400');
                    btn.classList.add('text-gray-300');
                }
            });
        });
    });

    commentTextarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = `${length} / 2000`;
        
        if (length > 1900) {
            charCount.classList.add('text-red-600');
        } else {
            charCount.classList.remove('text-red-600');
        }
    });
});
</script>
@endsection
