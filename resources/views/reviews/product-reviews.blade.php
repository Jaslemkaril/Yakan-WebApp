@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('products.show', $product) }}" class="inline-flex items-center text-maroon-600 hover:text-maroon-700 mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Product
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Customer Reviews</h1>
            <p class="text-gray-600 mt-2">{{ $product->name }}</p>
        </div>

        <!-- Rating Summary -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Average Rating -->
                <div class="text-center">
                    <div class="text-5xl font-bold text-gray-900 mb-2">{{ number_format($averageRating, 1) }}</div>
                    <div class="flex justify-center gap-1 mb-2">
                        @for($i = 0; $i < 5; $i++)
                            @if($i < floor($averageRating))
                                <span class="text-2xl text-yellow-400">★</span>
                            @elseif($i < ceil($averageRating))
                                <span class="text-2xl text-yellow-400" style="opacity: {{ $averageRating - floor($averageRating) }};"></span>
                            @else
                                <span class="text-2xl text-gray-300">★</span>
                            @endif
                        @endfor
                    </div>
                    <p class="text-gray-600">Based on {{ $totalReviews }} review{{ $totalReviews !== 1 ? 's' : '' }}</p>
                </div>

                <!-- Rating Distribution -->
                <div class="md:col-span-2">
                    @php
                        $maxCount = max(array_values($ratingDistribution)) ?: 1;
                    @endphp
                    @for($rating = 5; $rating >= 1; $rating--)
                        @php
                            $count = $ratingDistribution[$rating] ?? 0;
                            $percentage = ($count / $totalReviews) * 100;
                        @endphp
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-sm font-medium text-gray-700 w-12">{{ $rating }} ★</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-yellow-400 h-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-12 text-right">{{ $count }}</span>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="space-y-6">
            @forelse($reviews as $review)
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200">
                    <!-- Review Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex gap-1">
                                    @for($i = 0; $i < 5; $i++)
                                        <span class="{{ $i < $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                    @endfor
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ $review->rating }}.0</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $review->title ?? 'No title' }}</h3>
                        </div>
                        @if($review->verified_purchase)
                            <div class="flex items-center gap-1 bg-green-50 px-3 py-1 rounded-full">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs font-medium text-green-700">Verified</span>
                            </div>
                        @endif
                    </div>

                    <!-- Review Content -->
                    <p class="text-gray-700 mb-4">{{ $review->comment }}</p>

                    <!-- Review Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div class="text-sm text-gray-600">
                            <p class="font-medium text-gray-900">{{ $review->user->name }}</p>
                            <p>{{ $review->created_at->diffForHumans() }}</p>
                        </div>

                        <!-- Helpful/Unhelpful -->
                        <div class="flex items-center gap-4">
                            <button class="helpful-btn flex items-center gap-1 text-gray-600 hover:text-green-600 transition" data-review="{{ $review->id }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.646 7.23a2 2 0 01-1.789 1.106H7a2 2 0 01-2-2v-8a2 2 0 012-2h2.4a1 1 0 00.988-.765l1.286-5.546A1 1 0 0112 2c.596 0 1.129.325 1.414.816l2.35 3.529"/>
                                </svg>
                                <span class="helpful-count text-sm">{{ $review->helpful_count }}</span>
                            </button>
                            <button class="unhelpful-btn flex items-center gap-1 text-gray-600 hover:text-red-600 transition" data-review="{{ $review->id }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.646-7.23a2 2 0 011.789-1.106H17a2 2 0 012 2v8a2 2 0 01-2 2h-2.4a1 1 0 00-.988.765l-1.286 5.546A1 1 0 0112 22c-.596 0-1.129-.325-1.414-.816l-2.35-3.529"/>
                                </svg>
                                <span class="unhelpful-count text-sm">{{ $review->unhelpful_count }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <p class="text-gray-600 text-lg">No reviews yet</p>
                    <p class="text-gray-500">Be the first to review this product!</p>
                </div>
            @endforelse

            <!-- Pagination -->
            @if($reviews->hasPages())
                <div class="mt-8">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.helpful-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const reviewId = this.dataset.review;
        fetch(`/reviews/${reviewId}/helpful`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.querySelector('.helpful-count').textContent = data.helpful_count;
            }
        });
    });
});

document.querySelectorAll('.unhelpful-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const reviewId = this.dataset.review;
        fetch(`/reviews/${reviewId}/unhelpful`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.querySelector('.unhelpful-count').textContent = data.unhelpful_count;
            }
        });
    });
});
</script>
@endsection
