@extends('layouts.app')

@section('title', 'Cultural Heritage - Yakan')

@push('styles')
<style>
    .heritage-hero {
        background: #800000;
        position: relative;
        overflow: hidden;
    }

    .heritage-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .heritage-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .heritage-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .category-badge {
        background: linear-gradient(135deg, #92400e, #78350f);
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="heritage-hero py-20 relative">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center animate-fade-in-up">
                <h1 class="text-5xl lg:text-6xl font-bold text-white mb-6">Yakan Cultural Heritage</h1>
                <p class="text-xl text-amber-100 max-w-3xl mx-auto">
                    Discover the rich history, vibrant traditions, and timeless wisdom of the Yakan people
                </p>
            </div>
        </div>
    </section>

    <!-- Category Filter -->
    <div class="bg-white shadow-md sticky top-16 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-center space-x-4 overflow-x-auto">
                <a href="{{ route('cultural-heritage.index') }}" 
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ !request('category') ? 'bg-[#800000] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All
                </a>
                @foreach(['history', 'tradition', 'culture', 'art', 'crafts', 'language'] as $cat)
                <a href="{{ route('cultural-heritage.index', ['category' => $cat]) }}" 
                   class="px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap {{ request('category') == $cat ? 'bg-[#800000] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ ucfirst($cat) }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($featured && !request('category'))
        <!-- Featured Content -->
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Featured Story</h2>
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden heritage-card">
                <div class="grid md:grid-cols-2 gap-0">
                    <div class="relative h-64 md:h-auto">
                        @if($featured->image)
                        <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}" 
                             class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                            <i class="fas fa-book-open text-6xl text-amber-400"></i>
                        </div>
                        @endif
                        <span class="absolute top-4 left-4 category-badge text-white px-3 py-1 rounded-full text-sm font-semibold">
                            {{ ucfirst($featured->category) }}
                        </span>
                    </div>
                    <div class="p-8 flex flex-col justify-center">
                        <h3 class="text-3xl font-bold text-gray-900 mb-4">{{ $featured->title }}</h3>
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            {{ $featured->summary ?? $featured->content }}
                        </p>
                        <div class="flex items-center text-sm text-gray-500 mb-6">
                            @if($featured->author)
                            <span class="mr-4">
                                <i class="fas fa-user mr-1"></i>{{ $featured->author }}
                            </span>
                            @endif
                            @if($featured->published_date)
                            <span class="mr-4">
                                <i class="fas fa-calendar mr-1"></i>{{ $featured->published_date->format('M d, Y') }}
                            </span>
                            @endif
                        </div>
                        <a href="{{ route('cultural-heritage.show', $featured->slug) }}" 
                           class="inline-flex items-center px-6 py-3 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors w-fit">
                            Read Full Story
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Content Grid -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">
                @if(request('category'))
                    {{ ucfirst(request('category')) }} Stories
                @else
                    All Stories
                @endif
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($heritages as $heritage)
                @if($featured && $heritage->id == $featured->id && !request('category'))
                    @continue
                @endif
                
                <div class="bg-white rounded-xl shadow-lg overflow-hidden heritage-card">
                    <div class="relative h-48">
                        @if($heritage->image)
                        <img src="{{ $heritage->image_url }}" alt="{{ $heritage->title }}" 
                             class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                            <i class="fas fa-book text-4xl text-amber-400"></i>
                        </div>
                        @endif
                        <span class="absolute top-3 right-3 category-badge text-white px-3 py-1 rounded-full text-xs font-semibold">
                            {{ ucfirst($heritage->category) }}
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">{{ $heritage->title }}</h3>
                        <p class="text-gray-600 mb-4 line-clamp-3 text-sm">
                            {{ $heritage->summary ?? Str::limit($heritage->content, 100) }}
                        </p>
                        
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                            @if($heritage->published_date)
                            <span>
                                <i class="fas fa-calendar mr-1"></i>{{ $heritage->published_date->format('M d, Y') }}
                            </span>
                            @endif
                        </div>
                        
                        <a href="{{ route('cultural-heritage.show', $heritage->slug) }}" 
                           class="block text-center px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors text-sm font-medium">
                            Read More
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16">
                    <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No content available in this category yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-[#800000] py-16 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Explore Yakan Products</h2>
            <p class="text-white/90 mb-8 max-w-2xl mx-auto">
                Discover authentic Yakan crafts and support our cultural heritage
            </p>
            <a href="{{ route('products.index') }}" 
               class="inline-flex items-center px-8 py-3 bg-white text-[#800000] rounded-lg hover:bg-gray-100 transition-colors font-medium">
                Browse Products
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
@endsection
