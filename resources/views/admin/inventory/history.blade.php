@extends('layouts.admin')

@section('title', 'Stock History')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-[#800000] to-[#600000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold mb-2 flex items-center">
                    <i class="fas fa-history mr-3"></i>
                    Stock Movement History
                </h1>
                <p class="text-red-100 text-lg">Audit all stock in and stock out activities</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.inventory.index') }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-warehouse mr-2"></i>Back to Inventory
                </a>
            </div>
        </div>
    </div>

    @if(!$hasStockLogsTable)
        <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg p-4">
            <p class="font-semibold">Stock history table is not available yet.</p>
            <p class="text-sm mt-1">Run migrations so stock logs can be recorded and viewed.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-xs uppercase tracking-wide text-green-700 font-semibold">Stock In</p>
                <p class="text-2xl font-bold text-green-800">+{{ number_format($summaryIn) }}</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-xs uppercase tracking-wide text-red-700 font-semibold">Stock Out</p>
                <p class="text-2xl font-bold text-red-800">-{{ number_format($summaryOut) }}</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-xs uppercase tracking-wide text-blue-700 font-semibold">Net Movement</p>
                <p class="text-2xl font-bold {{ $summaryNet >= 0 ? 'text-blue-800' : 'text-red-700' }}">
                    {{ $summaryNet > 0 ? '+' : '' }}{{ number_format($summaryNet) }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.inventory.history') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                    <select name="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ (string)request('product_id') === (string)$product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Movement</label>
                    <select name="movement" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">All</option>
                        <option value="in" {{ request('movement') === 'in' ? 'selected' : '' }}>Stock In</option>
                        <option value="out" {{ request('movement') === 'out' ? 'selected' : '' }}>Stock Out</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recorded By</label>
                    <select name="created_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ (string)request('created_by') === (string)$user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors">
                        <i class="fas fa-filter mr-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.inventory.history') }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}"
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date &amp; Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Movement</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($stockLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $log->created_at?->format('M d, Y h:i A') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $log->product?->name ?? 'Unknown Product' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->quantity >= 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Stock In</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Stock Out</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $log->quantity >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $log->quantity > 0 ? '+' : '' }}{{ $log->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $log->creator?->name ?? 'System' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $log->note ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No stock history records found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($stockLogs, 'hasPages') && $stockLogs->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $stockLogs->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
