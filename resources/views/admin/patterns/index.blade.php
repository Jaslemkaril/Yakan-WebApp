@extends('layouts.admin')

@section('title', 'Patterns Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-maroon-700 to-maroon-800 shadow-2xl" style="background: linear-gradient(to right, #800000, #600000);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-black text-white">Patterns Management</h1>
                    <p class="text-white mt-2">Manage Yakan cultural patterns and media</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.patterns.create') }}" class="inline-flex items-center px-6 py-3 bg-white hover:bg-maroon-50 text-maroon-800 font-black rounded-lg shadow-xl hover:shadow-2xl transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add New Pattern
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="bg-white rounded-xl shadow-lg p-4">
            <form method="GET" id="filterForm" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Categories</option>
                        <option value="traditional" {{ request('category') == 'traditional' ? 'selected' : '' }}>Traditional</option>
                        <option value="modern" {{ request('category') == 'modern' ? 'selected' : '' }}>Modern</option>
                        <option value="contemporary" {{ request('category') == 'contemporary' ? 'selected' : '' }}>Contemporary</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                    <select name="difficulty" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Levels</option>
                        <option value="simple" {{ request('difficulty') == 'simple' ? 'selected' : '' }}>Simple</option>
                        <option value="intermediate" {{ request('difficulty') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ request('difficulty') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                        <option value="expert" {{ request('difficulty') == 'expert' ? 'selected' : '' }}>Expert</option>
                        <option value="master" {{ request('difficulty') == 'master' ? 'selected' : '' }}>Master</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
                    <select name="tag" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Tags</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->slug }}" {{ request('tag') == $tag->slug ? 'selected' : '' }}>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-maroon-600 text-white rounded-lg hover:bg-maroon-700 transition-colors">Filter</button>
                <a href="{{ route('admin.patterns.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Reset</a>
            </form>
        </div>
    </div>

    <!-- Patterns Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        @if($patterns->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($patterns as $pattern)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                        @if($pattern->hasSvg())
                            <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center p-4 overflow-hidden">
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="max-w-full max-h-full">
                                        {!! $pattern->getSvgContent() !!}
                                    </div>
                                </div>
                            </div>
                        @elseif($pattern->media->isNotEmpty())
                            @php
                                $firstMedia = $pattern->media->first();
                                $imagePath = $firstMedia->url;
                            @endphp
                            <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden">
                                <img src="{{ $imagePath }}" alt="{{ $firstMedia->alt_text ?? $pattern->name }}" class="w-full h-full object-contain p-2">
                            </div>
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-xs text-gray-500">{{ $pattern->name }}</p>
                                </div>
                            </div>
                        @endif
                        <div class="p-4">
                            <h3 class="text-lg font-black text-gray-900 truncate">{{ $pattern->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $pattern->category }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-full {{ $pattern->difficulty_level == 'simple' ? 'bg-green-100 text-green-800' : ($pattern->difficulty_level == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($pattern->difficulty_level) }}
                                </span>
                                @if($pattern->is_active)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </div>
                            <div class="flex gap-2 mt-4">
                                <a href="{{ route('admin.patterns.edit', $pattern) }}" class="flex-1 text-center px-3 py-1 bg-maroon-600 text-white text-sm rounded-lg hover:bg-maroon-700 transition-colors">Edit</a>
                                <form action="{{ route('admin.patterns.destroy', $pattern) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex-1 px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $patterns->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-xl font-black text-gray-900 mb-2">No Patterns Found</h3>
                <p class="text-gray-600 mb-6">Get started by creating your first pattern.</p>
                <a href="{{ route('admin.patterns.create') }}" class="inline-flex items-center px-6 py-3 bg-maroon-600 text-white font-black rounded-lg hover:bg-maroon-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Pattern
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
