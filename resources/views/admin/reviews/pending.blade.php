@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Review Moderation</h1>
            <p class="text-gray-600 mt-2">Approve or reject pending customer reviews</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Pending Reviews</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $reviews->total() }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Reviews</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\Review::count() }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Approved Reviews</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\Review::approved()->count() }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @forelse($reviews as $review)
                <div class="border-b border-gray-200 p-6 hover:bg-gray-50 transition duration-200 last:border-b-0">
                    <!-- Review Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="flex gap-1">
                                    @for($i = 0; $i < 5; $i++)
                                        <span class="{{ $i < $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                    @endfor
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ $review->rating }}.0</span>
                                @if($review->verified_purchase)
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Verified Purchase</span>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $review->title ?? 'No title' }}</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">{{ $review->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Review Content -->
                    <div class="mb-4">
                        <p class="text-gray-700 mb-3">{{ $review->comment }}</p>
                        
                        <!-- Review Metadata -->
                        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                            <div>
                                <span class="font-medium">User:</span>
                                <a href="#" class="text-maroon-600 hover:text-maroon-700">{{ $review->user->name }}</a>
                            </div>
                            @if($review->product)
                                <div>
                                    <span class="font-medium">Product:</span>
                                    <a href="{{ route('products.show', $review->product) }}" class="text-maroon-600 hover:text-maroon-700">{{ $review->product->name }}</a>
                                </div>
                            @endif
                            @if($review->order)
                                <div>
                                    <span class="font-medium">Order:</span>
                                    <a href="{{ route('orders.show', $review->order) }}" class="text-maroon-600 hover:text-maroon-700">#{{ $review->order->order_ref }}</a>
                                </div>
                            @endif
                            @if($review->customOrder)
                                <div>
                                    <span class="font-medium">Custom Order:</span>
                                    <a href="{{ route('custom_orders.show', $review->customOrder) }}" class="text-maroon-600 hover:text-maroon-700">#{{ $review->customOrder->id }}</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <!-- Approve Button -->
                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 font-medium rounded-lg hover:bg-green-200 transition duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Approve
                            </button>
                        </form>

                        <!-- Reject Button -->
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 font-medium rounded-lg hover:bg-red-200 transition duration-200" onclick="openRejectModal({{ $review->id }})">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            Reject
                        </button>

                        <!-- View Details -->
                        <a href="#" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Details
                        </a>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div id="reject-modal-{{ $review->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Review</h3>
                        <p class="text-gray-600 mb-4">Please provide a reason for rejecting this review:</p>
                        
                        <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                            @csrf
                            <textarea name="reason" rows="4" placeholder="Enter rejection reason..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent mb-4" required></textarea>
                            
                            <div class="flex gap-3">
                                <button type="button" onclick="closeRejectModal({{ $review->id }})" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition duration-200">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition duration-200">
                                    Reject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-600 text-lg font-medium">No pending reviews</p>
                    <p class="text-gray-500">All reviews have been moderated!</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($reviews->hasPages())
            <div class="mt-8">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function openRejectModal(reviewId) {
    document.getElementById(`reject-modal-${reviewId}`).classList.remove('hidden');
}

function closeRejectModal(reviewId) {
    document.getElementById(`reject-modal-${reviewId}`).classList.add('hidden');
}
</script>
@endsection
