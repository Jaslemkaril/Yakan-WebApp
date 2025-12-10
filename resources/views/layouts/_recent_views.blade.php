@if(auth()->check() && ($recentViews = \App\Models\RecentView::getRecentItems(auth()->id(), 5))->isNotEmpty())
    <div class="bg-white rounded-xl shadow-lg p-4">
        <h3 class="text-lg font-black text-gray-900 mb-3">Recently Viewed</h3>
        <div class="space-y-3">
            @foreach($recentViews as $item)
                @if($item instanceof \App\Models\Product)
                    <a href="{{ route('products.show', $item) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                        @if($item->image)
                            <img src="{{ asset('uploads/products/' . $item->image) }}" alt="{{ $item->name }}" class="w-12 h-12 object-cover rounded-lg" />
                        @else
                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-black text-gray-900 truncate">{{ $item->name }}</h4>
                            <p class="text-xs text-gray-600">Product • ₱{{ number_format($item->price, 2) }}</p>
                        </div>
                    </a>
                @elseif($item instanceof \App\Models\YakanPattern)
                    <a href="{{ route('patterns.show', $item) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                        @if($item->media->isNotEmpty())
                            <img src="{{ $item->media->first()->url }}" alt="{{ $item->name }}" class="w-12 h-12 object-cover rounded-lg" />
                        @else
                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-black text-gray-900 truncate">{{ $item->name }}</h4>
                            <p class="text-xs text-gray-600">Pattern • {{ ucfirst($item->difficulty_level) }}</p>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif
