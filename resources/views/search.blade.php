@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Search Results</h1>
        <p class="text-lg text-gray-600">
            @if(!empty($query))
                Found {{ $totalResults }} results for: <span class="font-semibold text-red-600">"{{ $query }}"</span>
            @else
                Enter a search term to find products, orders, or custom orders.
            @endif
        </p>
    </div>

    {{-- Search Bar --}}
    <div class="mb-8">
        <form action="{{ route('search') }}" method="GET" class="relative">
            <div class="relative">
                <input 
                    type="text" 
                    name="q" 
                    value="{{ $query ?? '' }}"
                    placeholder="Search products, orders, or anything..." 
                    class="w-full px-6 py-4 pl-14 text-lg bg-white border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-red-500 focus:ring-4 focus:ring-red-100 transition-all duration-300 shadow-sm hover:shadow-md"
                    id="searchInput"
                >
                <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button 
                    type="submit" 
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 px-6 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-300 shadow-md hover:shadow-lg"
                >
                    Search
                </button>
            </div>
        </form>
        
        {{-- Live Search Dropdown --}}
        <div id="liveSearchResults" class="hidden absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 max-h-96 overflow-y-auto">
        </div>
    </div>

    {{-- Search Results --}}
    @if(!empty($query))
        @if($totalResults > 0)
            {{-- Products Section --}}
            @if($products->count() > 0)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Products ({{ $products->count() }})
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                                @if($product->image)
                                    <div class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    </div>
                                @else
                                    <div class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="p-6">
                                    <h3 class="font-bold text-lg text-gray-900 mb-2">{{ $product->name }}</h3>
                                    @if($product->category)
                                        <span class="inline-block px-3 py-1 text-xs font-semibold text-red-600 bg-red-50 rounded-full mb-2">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($product->description, 100) }}</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-2xl font-bold text-red-600">₱{{ number_format($product->price, 2) }}</span>
                                        <a href="{{ route('products.show', $product) }}" class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-300">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Orders Section --}}
            @if($orders->count() > 0)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Orders ({{ $orders->count() }})
                    </h2>
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($orders as $order)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $order->tracking_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                       'bg-gray-100 text-gray-800') }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ₱{{ number_format($order->total_amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $order->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('orders.show', $order) }}" class="text-red-600 hover:text-red-900">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Custom Orders Section --}}
            @if($customOrders->count() > 0)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Custom Orders ({{ $customOrders->count() }})
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($customOrders as $order)
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="font-bold text-lg text-gray-900">{{ $order->product_type ?? 'Custom Product' }}</h3>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                    @if($order->specifications)
                                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($order->specifications, 150) }}</p>
                                    @endif
                                    @if($order->patterns)
                                        <p class="text-gray-500 text-xs mb-4"><strong>Patterns:</strong> {{ $order->patterns }}</p>
                                    @endif
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
                                        <a href="{{ route('custom_orders.show', $order) }}" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-300">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No results found</h3>
                    <p class="text-gray-600">Try searching with different keywords or check your spelling.</p>
                </div>
            </div>
        @endif
    @endif
</div>

<script>
// Live search functionality
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const liveSearchResults = document.getElementById('liveSearchResults');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            liveSearchResults.classList.add('hidden');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            performLiveSearch(query);
        }, 300);
    });
    
    // Hide live search when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !liveSearchResults.contains(e.target)) {
            liveSearchResults.classList.add('hidden');
        }
    });
}

function performLiveSearch(query) {
    fetch(`/search/live?q=${encodeURIComponent(query)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        displayLiveSearchResults(data);
    })
    .catch(error => {
        console.error('Search error:', error);
        liveSearchResults.classList.add('hidden');
    });
}

function displayLiveSearchResults(data) {
    if (data.total === 0) {
        liveSearchResults.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                No results found
            </div>
        `;
    } else {
        let html = '<div class="max-h-96 overflow-y-auto">';
        
        // Products
        if (data.products.length > 0) {
            html += '<div class="border-b border-gray-100"><div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-600 uppercase">Products</div></div>';
            data.products.forEach(product => {
                html += `
                    <a href="${product.url}" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">${product.name}</p>
                                <p class="text-sm text-gray-500">₱${product.price} ${product.category ? '· ' + product.category : ''}</p>
                            </div>
                        </div>
                    </a>
                `;
            });
        }
        
        // Orders
        if (data.orders.length > 0) {
            html += '<div class="border-b border-gray-100"><div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-600 uppercase">Orders</div></div>';
            data.orders.forEach(order => {
                html += `
                    <a href="${order.url}" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">${order.tracking_number}</p>
                                <p class="text-sm text-gray-500">₱${order.total_amount} · ${order.created_at}</p>
                            </div>
                        </div>
                    </a>
                `;
            });
        }
        
        // Custom Orders
        if (data.customOrders.length > 0) {
            html += '<div class="border-b border-gray-100"><div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-600 uppercase">Custom Orders</div></div>';
            data.customOrders.forEach(order => {
                html += `
                    <a href="${order.url}" class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">${order.product_type}</p>
                                <p class="text-sm text-gray-500">${order.created_at} · ${order.status}</p>
                            </div>
                        </div>
                    </a>
                `;
            });
        }
        
        html += '</div>';
        liveSearchResults.innerHTML = html;
    }
    
    liveSearchResults.classList.remove('hidden');
}
</script>
@endsection
