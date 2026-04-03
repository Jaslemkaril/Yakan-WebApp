<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Orders - Custom Orders Database Check</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Custom Orders Database Check</h1>
            <p class="text-gray-600 mb-4">Real-time view of all custom orders in the database</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="text-blue-600 text-sm font-semibold">Total Orders</div>
                    <div class="text-3xl font-bold text-blue-900">{{ $totalOrders }}</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
                    <div class="text-green-600 text-sm font-semibold">Pending</div>
                    <div class="text-3xl font-bold text-green-900">{{ $pendingOrders }}</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border-l-4 border-purple-500">
                    <div class="text-purple-600 text-sm font-semibold">Latest Order</div>
                    <div class="text-lg font-bold text-purple-900">
                        @if($latestOrder)
                            {{ $latestOrder->created_at->diffForHumans() }}
                        @else
                            No orders yet
                        @endif
                    </div>
                </div>
            </div>

            <button onclick="location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
                🔄 Refresh Data
            </button>
            <a href="{{ route('custom_orders.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-2 inline-block">
                ➕ Create New Order
            </a>
        </div>

        @if($orders->count() > 0)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fabric</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patterns</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preview</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $order->order_ref ?? '#'.$order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $order->order_name }}</div>
                                    <div class="text-xs text-gray-500">{{ Str::limit($order->description ?? 'N/A', 40) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($order->user)
                                            {{ $order->user->name }}
                                        @else
                                            User #{{ $order->user_id }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">ID: {{ $order->user_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($order->status === 'approved') bg-blue-100 text-blue-800
                                        @elseif($order->status === 'in_production') bg-purple-100 text-purple-800
                                        @elseif($order->status === 'completed') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $order->fabric_type ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->fabric_quantity_meters ?? 0 }}m</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($order->patterns)
                                            @php
                                                $patternIds = is_string($order->patterns) ? json_decode($order->patterns, true) : $order->patterns;
                                                $patternCount = is_array($patternIds) ? count($patternIds) : 0;
                                            @endphp
                                            {{ $patternCount }} pattern(s)
                                        @else
                                            No patterns
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($order->preview_image)
                                        <img src="{{ $order->preview_image }}" alt="Preview" class="h-12 w-12 object-cover rounded border border-gray-300">
                                    @else
                                        <div class="h-12 w-12 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">
                                            No preview
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->created_at->format('h:i A') }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Latest Order Details</h2>
                @if($latestOrder)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-2">Basic Info</h3>
                            <dl class="space-y-1 text-sm">
                                <div class="flex"><dt class="font-medium w-40">Order ID:</dt><dd>#{{ $latestOrder->id }}</dd></div>
                                <div class="flex"><dt class="font-medium w-40">Order Name:</dt><dd>{{ $latestOrder->order_name }}</dd></div>
                                <div class="flex"><dt class="font-medium w-40">Status:</dt><dd>{{ $latestOrder->status }}</dd></div>
                                <div class="flex"><dt class="font-medium w-40">Payment Status:</dt><dd>{{ $latestOrder->payment_status }}</dd></div>
                                <div class="flex"><dt class="font-medium w-40">Price:</dt><dd>₱{{ number_format($latestOrder->final_price, 2) }}</dd></div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-2">Fabric & Pattern</h3>
                            <dl class="space-y-1 text-sm">
                                <div class="flex"><dt class="font-medium w-40">Fabric Type:</dt><dd>{{ $latestOrder->fabric_type ?? 'N/A' }}</dd></div>
                                <div class="flex"><dt class="font-medium w-40">Quantity:</dt><dd>{{ $latestOrder->fabric_quantity_meters ?? 0 }} meters</dd></div>
                                <div class="flex"><dt class="font-medium w-40">Intended Use:</dt><dd>{{ $latestOrder->intended_use ?? 'N/A' }}</dd></div>
                                <div class="flex"><dt class="font-medium w-40">Has Preview:</dt><dd>{{ $latestOrder->preview_image ? 'Yes' : 'No' }}</dd></div>
                                <div class="flex"><dt class="font-medium w-40">Customization:</dt><dd>{{ $latestOrder->customization_settings ? 'Yes' : 'No' }}</dd></div>
                            </dl>
                        </div>
                    </div>

                    @if($latestOrder->customization_settings)
                        <div class="mt-4">
                            <h3 class="font-semibold text-gray-700 mb-2">Customization Settings</h3>
                            <pre class="bg-gray-50 p-3 rounded text-xs overflow-auto">{{ $latestOrder->customization_settings }}</pre>
                        </div>
                    @endif
                @endif
            </div>
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-yellow-800">No Custom Orders Found</h3>
                        <p class="mt-2 text-yellow-700">The database currently has 0 custom orders. Try creating one to test the submission process.</p>
                        <div class="mt-4">
                            <a href="{{ route('custom_orders.create') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded inline-block">
                                Create Your First Order
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($order2Debug))
            <div class="mt-6 bg-purple-50 border-2 border-purple-200 rounded-lg p-6">
                <h2 class="text-xl font-bold text-purple-900 mb-4">🔍 Order #2 Debug Information</h2>
                <div class="bg-white rounded p-4 space-y-2 text-sm">
                    <div class="flex"><dt class="font-semibold w-48 text-purple-700">design_upload length:</dt><dd class="font-mono text-gray-900">{{ $order2Debug['design_upload_length'] }}</dd></div>
                    <div class="flex"><dt class="font-semibold w-48 text-purple-700">has_design_upload:</dt><dd class="font-mono {{ $order2Debug['has_design_upload'] ? 'text-green-600' : 'text-red-600' }}">{{ $order2Debug['has_design_upload'] ? 'TRUE ✓' : 'FALSE ✗' }}</dd></div>
                    <div class="flex"><dt class="font-semibold w-48 text-purple-700">is_base64:</dt><dd class="font-mono {{ $order2Debug['is_base64'] ? 'text-green-600' : 'text-orange-600' }}">{{ $order2Debug['is_base64'] ? 'TRUE ✓' : 'FALSE ✗' }}</dd></div>
                    <div class="flex"><dt class="font-semibold w-48 text-purple-700">design_method:</dt><dd class="font-mono text-gray-900">{{ $order2Debug['design_method'] }}</dd></div>
                    <div class="flex"><dt class="font-semibold w-48 text-purple-700">fabric_type:</dt><dd class="font-mono text-gray-900">{{ $order2Debug['fabric_type'] }}</dd></div>
                    <div class="mt-3 pt-3 border-t border-purple-200">
                        <dt class="font-semibold text-purple-700 mb-2">design_upload preview (first 100 chars):</dt>
                        <dd class="font-mono text-xs bg-gray-100 p-2 rounded break-all">{{ $order2Debug['design_upload_preview'] }}</dd>
                    </div>
                    @if($order2Debug['design_metadata'])
                        <div class="mt-3 pt-3 border-t border-purple-200">
                            <dt class="font-semibold text-purple-700 mb-2">design_metadata:</dt>
                            <dd class="font-mono text-xs bg-gray-100 p-2 rounded overflow-auto max-h-40">{{ json_encode($order2Debug['design_metadata'], JSON_PRETTY_PRINT) }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="mt-6 bg-gray-100 rounded-lg p-4">
            <p class="text-sm text-gray-600">
                <strong>Note:</strong> This is a test page to verify custom order submissions. 
                Last checked: <strong>{{ now()->format('M d, Y h:i:s A') }}</strong>
            </p>
        </div>
    </div>

    <script>
        // Auto-refresh every 5 seconds
        let autoRefresh = false;
        let refreshInterval;

        document.addEventListener('keydown', function(e) {
            if (e.key === 'r' && e.ctrlKey) {
                e.preventDefault();
                location.reload();
            }
            if (e.key === 'a' && e.ctrlKey) {
                e.preventDefault();
                autoRefresh = !autoRefresh;
                if (autoRefresh) {
                    refreshInterval = setInterval(() => location.reload(), 5000);
                    alert('Auto-refresh enabled (every 5 seconds)');
                } else {
                    clearInterval(refreshInterval);
                    alert('Auto-refresh disabled');
                }
            }
        });

        console.log('Keyboard shortcuts:');
        console.log('Ctrl+R: Refresh page');
        console.log('Ctrl+A: Toggle auto-refresh (5s)');
    </script>
</body>
</html>
