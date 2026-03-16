@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('content')
@php $authQ = request('auth_token') ? '?auth_token=' . request('auth_token') : ''; @endphp
<div class="space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="bg-gradient-to-r from-red-700 to-[#800000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold mb-1 flex items-center">
                    <i class="fas fa-warehouse mr-3"></i>
                    Inventory Management
                </h1>
                <p class="text-red-200 text-sm">Monitor and manage your product inventory</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.inventory.history') . $authQ }}"
                   class="inline-flex items-center gap-2 bg-white/15 border border-white/30 text-white text-sm px-4 py-2 rounded-lg hover:bg-white/25 transition-colors">
                    <i class="fas fa-history"></i> Stock History
                </a>
                <a href="{{ route('admin.products.create') . $authQ }}"
                   class="inline-flex items-center gap-2 bg-white/15 border border-white/30 text-white text-sm px-4 py-2 rounded-lg hover:bg-white/25 transition-colors">
                    <i class="fas fa-plus"></i> New Product
                </a>
                @if($lowStockCount > 0)
                <a href="{{ route('admin.inventory.low-stock') . $authQ }}"
                   class="inline-flex items-center gap-2 bg-yellow-400/90 text-yellow-900 text-sm px-4 py-2 rounded-lg font-semibold hover:bg-yellow-300 transition-colors">
                    <i class="fas fa-exclamation-triangle animate-pulse"></i> Low Stock ({{ $lowStockCount }})
                </a>
                @endif
                <a href="{{ route('admin.inventory.create') . $authQ }}"
                   class="inline-flex items-center gap-2 bg-white text-red-700 text-sm px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow">
                    <i class="fas fa-plus-circle"></i> Add Inventory
                </a>
            </div>
        </div>
    </div>

    {{-- ===== STATS CARDS ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Total Products</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalProducts }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-boxes text-red-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Total Value</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">&#8369;{{ number_format($totalValue, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-coins text-green-600 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-100 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-orange-500 uppercase tracking-wide font-medium">Low Stock</p>
                    <p class="text-3xl font-bold text-orange-600 mt-1">{{ $lowStockCount }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-500 text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Total Items</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $products->total() }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: rgba(128,0,0,0.1)">
                    <i class="fas fa-chart-bar text-lg" style="color: #800000"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== STOCK IN RECORDS ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-history" style="color: #800000"></i>
                Stock In Records
            </h2>
            <span class="text-xs text-gray-400 italic">Auto-calculated from stock logs</span>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
                <p class="text-xs text-blue-500 font-semibold uppercase tracking-wider mb-1">Today</p>
                <p class="text-2xl font-bold text-blue-700">+{{ $stockInToday ?? 0 }}</p>
            </div>
            <div class="bg-purple-50 border border-purple-100 rounded-xl p-4 text-center">
                <p class="text-xs text-purple-500 font-semibold uppercase tracking-wider mb-1">This Week</p>
                <p class="text-2xl font-bold text-purple-700">+{{ $stockInWeek ?? 0 }}</p>
            </div>
            <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
                <p class="text-xs text-green-500 font-semibold uppercase tracking-wider mb-1">This Year</p>
                <p class="text-2xl font-bold text-green-700">+{{ $stockInYear ?? 0 }}</p>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
                <p class="text-xs text-amber-500 font-semibold uppercase tracking-wider mb-1">Overall</p>
                <p class="text-2xl font-bold text-amber-700">+{{ $stockInOverall ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- ===== SEARCH & FILTERS ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <form method="GET" action="{{ route('admin.inventory.index') }}">
            @if(request('auth_token'))
                <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
            @endif
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by product name..."
                               class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                </div>
                <div class="sm:w-48">
                    <select name="status" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                        <option value="">All Status</option>
                        <option value="low_stock"  {{ request('status') == 'low_stock'  ? 'selected' : '' }}>Low Stock</option>
                        <option value="normal"     {{ request('status') == 'normal'     ? 'selected' : '' }}>Normal</option>
                        <option value="overstock"  {{ request('status') == 'overstock'  ? 'selected' : '' }}>Overstock</option>
                    </select>
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-white text-sm font-medium rounded-lg transition-colors" style="background-color: #800000">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.inventory.index') . $authQ }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ===== INVENTORY TABLE ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Min / Max</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($products as $product)
                        @php $inventory = $product->inventory; @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ !$inventory ? 'bg-yellow-50/50' : '' }}">

                            {{-- Product --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($product->image)
                                        <img class="h-10 w-10 rounded-lg object-cover border border-gray-100 shadow-sm"
                                             src="{{ $product->image_src }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $product->category->name ?? 'No Category' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Quantity --}}
                            <td class="px-6 py-4">
                                @if($inventory)
                                    <span class="text-lg font-bold {{ $inventory->isLowStock() ? 'text-red-600' : 'text-gray-800' }}">
                                        {{ $inventory->quantity }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">{{ $product->stock ?? 0 }}</span>
                                    <div class="text-xs text-orange-500 font-medium mt-0.5">No tracking</div>
                                @endif
                            </td>

                            {{-- Min/Max --}}
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($inventory)
                                    <span class="font-medium">{{ $inventory->min_stock_level }}</span>
                                    <span class="text-gray-400 mx-1">/</span>
                                    <span class="font-medium">{{ $inventory->max_stock_level }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                @if($inventory)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $inventory->stock_status_color }}">
                                        {{ $inventory->stock_status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">
                                        No Tracking
                                    </span>
                                @endif
                            </td>

                            {{-- Total Sold --}}
                            <td class="px-6 py-4">
                                @if($inventory)
                                    <div class="text-sm font-semibold text-gray-800">{{ $inventory->total_sold ?? 0 }}</div>
                                    @if($inventory->last_sale_at)
                                        <div class="text-xs text-gray-400">{{ $inventory->last_sale_at->format('M d, Y') }}</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- Revenue --}}
                            <td class="px-6 py-4 text-sm">
                                @if($inventory)
                                    <span class="font-semibold text-gray-800">&#8369;{{ number_format($inventory->total_revenue ?? 0, 2) }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if($inventory)
                                        <a href="{{ route('admin.inventory.show', $inventory) . $authQ }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" title="View Inventory">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('admin.inventory.edit', $inventory) . $authQ }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md text-white transition-colors" title="Edit Inventory" style="background-color: rgba(128,0,0,0.15); color: #800000">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button onclick="restockModal({{ $inventory->id }}, '{{ addslashes($product->name) }}')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md bg-green-100 text-green-700 hover:bg-green-200 transition-colors" title="Stock In">
                                            <i class="fas fa-plus"></i> Stock In
                                        </button>
                                        @if($inventory->quantity > 0)
                                        <button onclick="stockOutModal({{ $inventory->id }}, '{{ addslashes($product->name) }}', {{ $inventory->quantity }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md bg-red-100 text-red-700 hover:bg-red-200 transition-colors" title="Stock Out">
                                            <i class="fas fa-minus"></i> Stock Out
                                        </button>
                                        @endif
                                    @else
                                        <a href="{{ route('admin.inventory.create') }}?product_id={{ $product->id }}{{ request('auth_token') ? '&auth_token=' . request('auth_token') : '' }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md bg-orange-100 text-orange-700 hover:bg-orange-200 transition-colors">
                                            <i class="fas fa-plus-circle"></i> Add Tracking
                                        </a>
                                    @endif
                                    <a href="{{ route('products.show', $product->id) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="View Product Page">
                                        <i class="fas fa-store"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product->id) . $authQ }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md bg-orange-50 text-orange-600 hover:bg-orange-100 transition-colors" title="Edit Product">
                                        <i class="fas fa-tag"></i>
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <i class="fas fa-box-open text-5xl"></i>
                                    <p class="font-medium text-gray-500">No products found</p>
                                    <a href="{{ route('admin.products.create') }}"
                                       class="mt-2 inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-medium rounded-lg transition-colors" style="background-color: #800000">
                                        <i class="fas fa-plus"></i> Add First Product
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ===== STOCK IN MODAL ===== --}}
<div id="restockModal" class="fixed inset-0 bg-black/50 hidden overflow-y-auto h-full w-full z-50">
    <div class="flex items-center justify-center min-h-full p-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-green-600 text-sm"></i>
                    </span>
                    Stock In
                </h3>
                <button onclick="closeRestockModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <p class="text-sm text-gray-500 mb-4" id="restockProductLabel"></p>
            <form id="restockForm" class="space-y-4">
                <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Quantity to Add <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="quantity" id="restockQtyInput" min="1" max="9999" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Note <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" name="note" id="restockNoteInput" placeholder="e.g. New delivery batch"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Confirm Stock In
                    </button>
                    <button type="button" onclick="closeRestockModal()"
                            class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== STOCK OUT MODAL ===== --}}
<div id="stockOutModal" class="fixed inset-0 bg-black/50 hidden overflow-y-auto h-full w-full z-50">
    <div class="flex items-center justify-center min-h-full p-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-minus text-red-600 text-sm"></i>
                    </span>
                    Stock Out
                </h3>
                <button onclick="closeStockOutModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <p class="text-sm text-gray-500 mb-1" id="stockOutProductLabel"></p>
            <p class="text-sm font-medium text-gray-700 mb-4" id="stockOutAvailableLabel"></p>
            <form id="stockOutForm" class="space-y-4">
                <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Quantity to Remove <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="quantity" id="stockOutQty" min="1" max="9999" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Reason <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" name="note" id="stockOutNoteInput" placeholder="e.g. Damaged, manual adjustment"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fas fa-minus mr-1"></i> Confirm Stock Out
                    </button>
                    <button type="button" onclick="closeStockOutModal()"
                            class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const authToken = '{{ request('auth_token') }}';
const authSuffix = authToken ? `?auth_token=${authToken}` : '';
const csrfToken = '{{ csrf_token() }}';

// ── helpers ──────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    const existing = document.getElementById('inv-toast');
    if (existing) existing.remove();
    const color = type === 'success' ? 'bg-green-600' : 'bg-red-600';
    const toast = document.createElement('div');
    toast.id = 'inv-toast';
    toast.className = `fixed top-5 right-5 z-[9999] px-5 py-3 rounded-xl text-white text-sm font-semibold shadow-xl flex items-center gap-2 ${color}`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

async function submitAjax(url, formData, btn, originalText) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
            redirect: 'follow',
        });
        // Any 2xx or redirect to inventory = success
        if (res.ok || res.redirected) {
            return true;
        }
        return false;
    } catch (e) {
        return false;
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ── Stock In ─────────────────────────────────────────────────────────
let currentRestockId = null;

function restockModal(inventoryId, productName) {
    currentRestockId = inventoryId;
    document.getElementById('restockProductLabel').textContent = `Product: ${productName}`;
    document.getElementById('restockQtyInput').value = '';
    document.getElementById('restockNoteInput').value = '';
    document.getElementById('restockModal').classList.remove('hidden');
}
function closeRestockModal() {
    document.getElementById('restockModal').classList.add('hidden');
}

document.getElementById('restockForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const qty = document.getElementById('restockQtyInput').value;
    if (!qty || qty < 1) { showToast('Please enter a valid quantity.', 'error'); return; }

    const fd = new FormData();
    fd.append('_method', 'PATCH');
    fd.append('_token', csrfToken);
    if (authToken) fd.append('auth_token', authToken);
    fd.append('quantity', qty);
    fd.append('note', document.getElementById('restockNoteInput').value);

    const btn = this.querySelector('button[type="submit"]');
    const ok = await submitAjax(`/admin/inventory/${currentRestockId}/restock${authSuffix}`, fd, btn, '<i class="fas fa-plus mr-1"></i> Confirm Stock In');

    closeRestockModal();
    if (ok) {
        showToast('Stock added successfully!');
        setTimeout(() => window.location.reload(), 800);
    } else {
        showToast('Something went wrong. Please try again.', 'error');
    }
});

// ── Stock Out ────────────────────────────────────────────────────────
let currentStockOutId = null;

function stockOutModal(inventoryId, productName, availableQty) {
    currentStockOutId = inventoryId;
    const qtyInput = document.getElementById('stockOutQty');
    qtyInput.value = 1;
    qtyInput.max = Math.max(1, availableQty);
    document.getElementById('stockOutProductLabel').textContent = `Product: ${productName}`;
    document.getElementById('stockOutAvailableLabel').textContent = `Available stock: ${availableQty}`;
    document.getElementById('stockOutModal').classList.remove('hidden');
}
function closeStockOutModal() {
    document.getElementById('stockOutModal').classList.add('hidden');
}

document.getElementById('stockOutForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const qty = document.getElementById('stockOutQty').value;
    if (!qty || qty < 1) { showToast('Please enter a valid quantity.', 'error'); return; }

    const fd = new FormData();
    fd.append('_method', 'PATCH');
    fd.append('_token', csrfToken);
    if (authToken) fd.append('auth_token', authToken);
    fd.append('quantity', qty);
    fd.append('note', document.getElementById('stockOutNoteInput').value);

    const btn = this.querySelector('button[type="submit"]');
    const ok = await submitAjax(`/admin/inventory/${currentStockOutId}/stock-out${authSuffix}`, fd, btn, '<i class="fas fa-minus mr-1"></i> Confirm Stock Out');

    closeStockOutModal();
    if (ok) {
        showToast('Stock removed successfully!');
        setTimeout(() => window.location.reload(), 800);
    } else {
        showToast('Something went wrong. Please try again.', 'error');
    }
});
</script>
@endsection
