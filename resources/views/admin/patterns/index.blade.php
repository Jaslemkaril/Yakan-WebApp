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
        {{-- Stats row --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 px-4 py-3 text-center">
                <p class="text-2xl font-black text-gray-900">{{ $patterns->total() }}</p>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Patterns</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 px-4 py-3 text-center">
                <p class="text-2xl font-black" style="color:#800000;">{{ $patterns->getCollection()->where('is_active', true)->count() }}</p>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Active</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 px-4 py-3 text-center">
                @php $avgPattern = $patterns->getCollection()->whereNotNull('pattern_price')->where('pattern_price','>',0)->avg('pattern_price'); @endphp
                <p class="text-2xl font-black" style="color:#800000;">{{ $avgPattern ? '₱'.number_format($avgPattern,0) : '—' }}</p>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Avg. Pattern Price</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 px-4 py-3 text-center">
                @php $avgMeter = $patterns->getCollection()->whereNotNull('price_per_meter')->where('price_per_meter','>',0)->avg('price_per_meter'); @endphp
                <p class="text-2xl font-black" style="color:#800000;">{{ $avgMeter ? '₱'.number_format($avgMeter,0) : '—' }}</p>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Avg. Per Meter</p>
            </div>
        </div>

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
                        <option value="simple"   {{ request('difficulty') == 'simple'   ? 'selected' : '' }}>Simple</option>
                        <option value="medium"   {{ request('difficulty') == 'medium'   ? 'selected' : '' }}>Medium</option>
                        <option value="advanced" {{ request('difficulty') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                        <option value="complex"  {{ request('difficulty') == 'complex'  ? 'selected' : '' }}>Complex</option>
                        <option value="expert"   {{ request('difficulty') == 'expert'   ? 'selected' : '' }}>Expert</option>
                        <option value="master"   {{ request('difficulty') == 'master'   ? 'selected' : '' }}>Master</option>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($patterns as $pattern)
                @php
                    $svgContent = $pattern->hasSvg() ? $pattern->getSvgContent() : null;
                    $difficultyConfig = [
                        'simple'   => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800'],
                        'medium'   => ['bg' => 'bg-yellow-100',  'text' => 'text-yellow-800'],
                        'advanced' => ['bg' => 'bg-orange-100',  'text' => 'text-orange-800'],
                        'complex'  => ['bg' => 'bg-red-100',     'text' => 'text-red-800'],
                        'expert'   => ['bg' => 'bg-red-100',     'text' => 'text-red-800'],
                        'master'   => ['bg' => 'bg-purple-100',  'text' => 'text-purple-800'],
                    ];
                    $dc = $difficultyConfig[$pattern->difficulty_level] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                @endphp
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 border border-gray-100 flex flex-col">

                    {{-- Pattern preview --}}
                    @if($svgContent)
                        <div class="w-full h-44 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-3 overflow-hidden relative">
                            <div class="max-w-full max-h-full flex items-center justify-center">{!! $svgContent !!}</div>
                            @if(!$pattern->is_active)
                                <div class="absolute inset-0 bg-gray-900/20 flex items-center justify-center">
                                    <span class="bg-gray-800/70 text-white text-xs font-bold px-2 py-1 rounded">Inactive</span>
                                </div>
                            @endif
                        </div>
                    @elseif($pattern->media->isNotEmpty())
                        @php $firstMedia = $pattern->media->first(); @endphp
                        <div class="w-full h-44 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center overflow-hidden relative">
                            <img src="{{ $firstMedia->url }}" alt="{{ $firstMedia->alt_text ?? $pattern->name }}" class="w-full h-full object-contain p-2">
                            @if(!$pattern->is_active)
                                <div class="absolute inset-0 bg-gray-900/20 flex items-center justify-center">
                                    <span class="bg-gray-800/70 text-white text-xs font-bold px-2 py-1 rounded">Inactive</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="w-full h-44 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-xs text-gray-400">No preview</p>
                            </div>
                        </div>
                    @endif

                    <div class="p-4 flex flex-col flex-1">

                        {{-- Name + category --}}
                        <div class="mb-3">
                            <h3 class="text-base font-black text-gray-900 truncate" title="{{ $pattern->name }}">{{ $pattern->name }}</h3>
                            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mt-0.5">{{ ucfirst($pattern->category) }}</p>
                        </div>

                        {{-- Pricing panel --}}
                        <div class="rounded-lg border border-red-100 mb-3 overflow-hidden" style="background: #fff9f9;">
                            <div class="grid grid-cols-2 divide-x divide-red-100">
                                <div class="px-3 py-2.5 text-center">
                                    <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide mb-0.5">Pattern Price</p>
                                    @if($pattern->pattern_price && $pattern->pattern_price > 0)
                                        <p class="text-base font-black" style="color: #800000;">₱{{ number_format($pattern->pattern_price, 2) }}</p>
                                    @else
                                        <p class="text-base font-black text-gray-300">—</p>
                                    @endif
                                </div>
                                <div class="px-3 py-2.5 text-center">
                                    <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide mb-0.5">Per Meter</p>
                                    @if($pattern->price_per_meter && $pattern->price_per_meter > 0)
                                        <p class="text-base font-black" style="color: #800000;">₱{{ number_format($pattern->price_per_meter, 2) }}</p>
                                    @else
                                        <p class="text-base font-black text-gray-300">—</p>
                                    @endif
                                </div>
                            </div>
                            @if($pattern->base_price_multiplier && $pattern->base_price_multiplier != 1)
                            <div class="border-t border-red-100 px-3 py-1.5 flex items-center justify-center gap-1" style="background: #fff2f2;">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <p class="text-[10px] text-gray-500 font-medium">Price multiplier: <strong>×{{ number_format($pattern->base_price_multiplier, 2) }}</strong></p>
                            </div>
                            @endif
                        </div>

                        {{-- Production time --}}
                        <div class="flex items-center gap-1.5 text-xs text-gray-500 mb-3">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $pattern->estimated_days }} day{{ $pattern->estimated_days != 1 ? 's' : '' }} production</span>
                        </div>

                        {{-- Badges --}}
                        <div class="flex items-center gap-1.5 flex-wrap mb-4">
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-full {{ $dc['bg'] }} {{ $dc['text'] }}">
                                {{ ucfirst($pattern->difficulty_level) }}
                            </span>
                            @if($pattern->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-full bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1"></span>Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-full bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1"></span>Inactive
                                </span>
                            @endif
                        </div>

                        {{-- Actions pinned to bottom --}}
                        <div class="flex gap-2 mt-auto pt-1">
                            <a href="{{ route('admin.patterns.edit', $pattern) }}{{ request()->has('auth_token') ? '?auth_token=' . request()->get('auth_token') : '' }}"
                               class="flex-1 text-center px-3 py-2 border-2 text-sm font-bold rounded-lg hover:bg-red-50 transition-colors"
                               style="border-color: #800000; color: #800000;">Edit</a>
                            <form action="{{ route('admin.patterns.destroy', $pattern) }}" method="POST" onsubmit="return confirm('Delete {{ addslashes($pattern->name) }}? This cannot be undone.')" class="flex-1">
                                @csrf
                                @method('DELETE')
                                @if(request()->has('auth_token'))
                                    <input type="hidden" name="auth_token" value="{{ request()->get('auth_token') }}">
                                @endif
                                <button type="submit" class="w-full px-3 py-2 text-white text-sm font-bold rounded-lg hover:opacity-90 transition-opacity" style="background-color: #800000;">Delete</button>
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
