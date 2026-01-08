@extends('layouts.app')

@section('title', $heritage->title . ' - Cultural Heritage')

@push('styles')
<style>
    .content-body {
        line-height: 1.8;
    }
    
    .content-body h2 {
        font-size: 1.875rem;
        font-weight: 700;
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #1f2937;
    }
    
    .content-body h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        color: #374151;
    }
    
    .content-body p {
        margin-bottom: 1rem;
        color: #4b5563;
    }
    
    .content-body ul, .content-body ol {
        margin-left: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .content-body li {
        margin-bottom: 0.5rem;
    }
    
    .content-body blockquote {
        border-left: 4px solid #92400e;
        padding-left: 1rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #6b7280;
    }
    
    .content-body img {
        border-radius: 0.5rem;
        margin: 1.5rem 0;
    }

    /* Image Modal Styles */
    .image-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.95);
        cursor: zoom-out;
    }

    .image-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .image-modal img {
        max-width: 90%;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 0.5rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .image-modal .close-modal {
        position: absolute;
        top: 2rem;
        right: 2rem;
        color: white;
        font-size: 2.5rem;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
        background: rgba(255, 255, 255, 0.2);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .image-modal .close-modal:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .featured-image {
        cursor: zoom-in;
        transition: transform 0.3s ease;
    }

    .featured-image:hover {
        transform: scale(1.02);
    }
</style>
@endpush

@section('content')
    <!-- Breadcrumb -->
    <div class="bg-gray-50 border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('welcome') }}" class="text-gray-500 hover:text-gray-700">Home</a>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                <a href="{{ route('cultural-heritage.index') }}" class="text-gray-500 hover:text-gray-700">Cultural Heritage</a>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                <span class="text-gray-900 font-medium">{{ Str::limit($heritage->title, 50) }}</span>
            </nav>
        </div>
    </div>

    <!-- Article Header -->
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <header class="mb-8">
            <div class="flex items-center space-x-3 mb-4">
                <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-sm font-semibold">
                    {{ ucfirst($heritage->category) }}
                </span>
                @if($heritage->published_date)
                <span class="text-gray-500 text-sm">
                    <i class="fas fa-calendar mr-1"></i>{{ $heritage->published_date->format('F d, Y') }}
                </span>
                @endif
            </div>
            
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">{{ $heritage->title }}</h1>
            
            @if($heritage->summary)
            <p class="text-xl text-gray-600 leading-relaxed">{{ $heritage->summary }}</p>
            @endif
            
            @if($heritage->author)
            <div class="flex items-center mt-6 pt-6 border-t border-gray-200">
                <div class="w-12 h-12 bg-amber-600 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                    {{ strtoupper(substr($heritage->author, 0, 1)) }}
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $heritage->author }}</p>
                    <p class="text-sm text-gray-500">Cultural Heritage Contributor</p>
                </div>
            </div>
            @endif
        </header>

        <!-- Featured Image -->
        @if($heritage->image)
        <div class="mb-12">
            <img src="{{ $heritage->image_url }}" 
                 alt="{{ $heritage->title }}" 
                 class="w-full h-auto max-h-[600px] object-contain rounded-2xl shadow-xl featured-image bg-gray-100"
                 onclick="openImageModal(this.src, '{{ $heritage->title }}')"
                 title="Click to view full image">
        </div>
        @endif

        <!-- Article Content -->
        <div class="prose prose-lg max-w-none content-body">
            {!! nl2br(e($heritage->content)) !!}
        </div>

        <!-- Share Section -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Share this story</h3>
            <div class="flex space-x-3">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                   target="_blank"
                   class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($heritage->title) }}" 
                   target="_blank"
                   class="flex items-center justify-center w-10 h-10 bg-sky-500 text-white rounded-full hover:bg-sky-600 transition-colors">
                    <i class="fab fa-twitter"></i>
                </a>
                <button onclick="copyToClipboard()" 
                        class="flex items-center justify-center w-10 h-10 bg-gray-600 text-white rounded-full hover:bg-gray-700 transition-colors">
                    <i class="fas fa-link"></i>
                </button>
            </div>
        </div>

        <!-- Navigation -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <a href="{{ route('cultural-heritage.index') }}" 
               class="inline-flex items-center text-amber-600 hover:text-amber-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Cultural Heritage
            </a>
        </div>
    </article>

    <!-- Related Content -->
    @if($related->count() > 0)
    <section class="bg-gray-50 py-16 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Related Stories</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($related as $item)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="relative h-48">
                        @if($item->image)
                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" 
                             class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                            <i class="fas fa-book text-4xl text-amber-400"></i>
                        </div>
                        @endif
                        <span class="absolute top-3 right-3 bg-amber-600 text-white px-3 py-1 rounded-full text-xs font-semibold">
                            {{ ucfirst($item->category) }}
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">{{ $item->title }}</h3>
                        <p class="text-gray-600 mb-4 line-clamp-2 text-sm">
                            {{ $item->summary ?? $item->excerpt }}
                        </p>
                        <a href="{{ route('cultural-heritage.show', $item->slug) }}" 
                           class="text-amber-600 hover:text-amber-700 font-medium text-sm">
                            Read More <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="close-modal" onclick="closeImageModal()">&times;</span>
        <img id="modalImage" src="" alt="">
    </div>
@endsection

@push('scripts')
<script>
function openImageModal(src, alt) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modal.classList.add('active');
    modalImg.src = src;
    modalImg.alt = alt;
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
});

function copyToClipboard() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}
</script>
@endpush
