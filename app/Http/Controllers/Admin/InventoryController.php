<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct()
    {
        // Auth handled by route-level AdminCheck middleware
    }

    /**
     * Display a listing of the inventory.
     */
    public function index(Request $request): View
    {
        // Get all products with their inventory (one inventory per product)
        $query = Product::with(['inventory', 'category'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'low_stock') {
                    $q->whereHas('inventory', function($inv) {
                        $inv->whereRaw('quantity <= min_stock_level');
                    });
                } elseif ($request->status === 'overstock') {
                    $q->whereHas('inventory', function($inv) {
                        $inv->whereRaw('quantity >= max_stock_level');
                    });
                } elseif ($request->status === 'normal') {
                    $q->whereHas('inventory', function($inv) {
                        $inv->whereRaw('quantity > min_stock_level AND quantity < max_stock_level');
                    });
                }
            });

        $products = $query->paginate(15);
        
        $lowStockCount = Inventory::whereRaw('quantity <= min_stock_level')->count();
        $totalProducts = Product::count();
        $totalValue = Inventory::selectRaw('SUM(quantity * selling_price) as total')->value('total') ?? 0;

        $stockInToday = 0;
        $stockInWeek = 0;
        $stockInYear = 0;
        $stockInOverall = 0;

        if (\Schema::hasTable('stock_logs')) {
            $stockInToday = \App\Models\StockLog::where('quantity', '>', 0)
                ->whereDate('created_at', today())
                ->sum('quantity');
            $stockInWeek = \App\Models\StockLog::whereBetween('created_at', [
                    now()->copy()->startOfWeek(),
                    now()->copy()->endOfWeek(),
                ])
                ->where('quantity', '>', 0)
                ->sum('quantity');
            $stockInYear = \App\Models\StockLog::where('quantity', '>', 0)
                ->whereYear('created_at', now()->year)
                ->sum('quantity');
            $stockInOverall = \App\Models\StockLog::where('quantity', '>', 0)->sum('quantity');
        }

        return view('admin.inventory.index', compact(
            'products',
            'lowStockCount',
            'totalProducts',
            'totalValue',
            'stockInToday',
            'stockInWeek',
            'stockInYear',
            'stockInOverall'
        ));
    }

    /**
     * Show the form for creating a new inventory record.
     */
    public function create(): View
    {
        return view('admin.inventory.create');
    }

    /**
     * Store a newly created inventory record.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:1',
            'max_stock_level' => 'required|integer|min:1|gt:min_stock_level',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Find or create product
        $product = Product::firstOrCreate(['name' => $validated['product_name']], [
            'name' => $validated['product_name'],
            'price' => $validated['selling_price'] ?? 0,
            'status' => 'active',
            'description' => 'Auto-created from inventory record',
        ]);

        // Check if inventory already exists for this product
        if (Inventory::where('product_id', $product->id)->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['product_name' => 'Inventory already exists for this product.']);
        }

        // Create inventory record
        $inventoryData = [
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'min_stock_level' => $validated['min_stock_level'],
            'max_stock_level' => $validated['max_stock_level'],
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'supplier' => $validated['supplier'],
            'location' => $validated['location'],
            'notes' => $validated['notes'],
            'low_stock_alert' => $validated['quantity'] <= $validated['min_stock_level'],
        ];

        Inventory::create($inventoryData);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory record created successfully for ' . $product->name . '.');
    }

    /**
     * Display the specified inventory record.
     */
    public function show(Inventory $inventory): View
    {
        $inventory->load('product');
        return view('admin.inventory.show', compact('inventory'));
    }

    /**
     * Show the form for editing the specified inventory record.
     */
    public function edit(Inventory $inventory): View
    {
        $inventory->load('product');
        return view('admin.inventory.edit', compact('inventory'));
    }

    /**
     * Update the specified inventory record.
     */
    public function update(Request $request, Inventory $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:1',
            'max_stock_level' => 'required|integer|min:1|gt:min_stock_level',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['low_stock_alert'] = $validated['quantity'] <= $validated['min_stock_level'];

        $inventory->update($validated);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory record updated successfully.');
    }

    /**
     * Restock inventory.
     */
    public function restock(Request $request, Inventory $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:1000',
            'note' => 'nullable|string|max:255',
        ]);

        $inventory->restock($validated['quantity']);

        if (\Schema::hasTable('stock_logs')) {
            \App\Models\StockLog::create([
                'product_id' => $inventory->product_id,
                'quantity' => $validated['quantity'],
                'note' => $validated['note'] ?? 'Restock from inventory page',
                'created_by' => auth()->id(),
            ]);
        }

        \Cache::flush();

        return redirect()->route('admin.inventory.index')
            ->with('success', "Successfully restocked {$validated['quantity']} units.");
    }

    /**
     * Deduct stock from inventory.
     */
    public function stockOut(Request $request, Inventory $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:1000',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validated['quantity'] > $inventory->quantity) {
            return redirect()->route('admin.inventory.index')
                ->with('error', "Cannot remove {$validated['quantity']} units. Available stock is only {$inventory->quantity}.");
        }

        $inventory->quantity -= $validated['quantity'];
        $inventory->low_stock_alert = $inventory->quantity <= $inventory->min_stock_level;
        $inventory->save();

        if (\Schema::hasTable('stock_logs')) {
            \App\Models\StockLog::create([
                'product_id' => $inventory->product_id,
                'quantity' => -$validated['quantity'],
                'note' => $validated['note'] ?? 'Stock out from inventory page',
                'created_by' => auth()->id(),
            ]);
        }

        \Cache::flush();

        return redirect()->route('admin.inventory.index')
            ->with('success', "Successfully removed {$validated['quantity']} units from stock.");
    }

    /**
     * Display stock movement history with filters.
     */
    public function history(Request $request): View
    {
        $products = Product::orderBy('name')->get(['id', 'name']);
        $users = collect();

        $summaryIn = 0;
        $summaryOut = 0;
        $summaryNet = 0;

        $hasStockLogsTable = \Schema::hasTable('stock_logs');
        if (!$hasStockLogsTable) {
            $stockLogs = collect();
            return view('admin.inventory.history', compact(
                'stockLogs',
                'products',
                'users',
                'summaryIn',
                'summaryOut',
                'summaryNet',
                'hasStockLogsTable'
            ));
        }

        $baseQuery = \App\Models\StockLog::with([
            'product:id,name',
            'creator:id,name',
        ])->orderBy('created_at', 'desc');

        if ($request->filled('product_id')) {
            $baseQuery->where('product_id', (int) $request->product_id);
        }

        if ($request->filled('movement')) {
            if ($request->movement === 'in') {
                $baseQuery->where('quantity', '>', 0);
            } elseif ($request->movement === 'out') {
                $baseQuery->where('quantity', '<', 0);
            }
        }

        if ($request->filled('date_from')) {
            $baseQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $baseQuery->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('created_by')) {
            $baseQuery->where('created_by', (int) $request->created_by);
        }

        $stockLogs = (clone $baseQuery)->paginate(20)->withQueryString();

        $summaryIn = (clone $baseQuery)->where('quantity', '>', 0)->sum('quantity');
        $summaryOut = abs((clone $baseQuery)->where('quantity', '<', 0)->sum('quantity'));
        $summaryNet = $summaryIn - $summaryOut;

        $userIds = \App\Models\StockLog::whereNotNull('created_by')
            ->distinct()
            ->pluck('created_by');
        $users = \App\Models\User::whereIn('id', $userIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.inventory.history', compact(
            'stockLogs',
            'products',
            'users',
            'summaryIn',
            'summaryOut',
            'summaryNet',
            'hasStockLogsTable'
        ));
    }

    /**
     * Remove the specified inventory record.
     */
    public function destroy(Inventory $inventory): RedirectResponse
    {
        $inventory->delete();

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory record deleted successfully.');
    }

    /**
     * Get low stock alerts.
     */
    public function lowStockAlerts(): View
    {
        $lowStockItems = Inventory::with('product')
            ->whereRaw('quantity <= min_stock_level')
            ->orderBy('quantity', 'asc')
            ->get();

        return view('admin.inventory.low-stock', compact('lowStockItems'));
    }

    /**
     * Generate inventory report.
     */
    public function report(): View
    {
        $inventories = Inventory::with('product')->get();
        
        $report = [
            'total_products' => $inventories->count(),
            'low_stock_count' => $inventories->where('low_stock_alert', true)->count(),
            'total_quantity' => $inventories->sum('quantity'),
            'total_value' => $inventories->sum(function ($inv) {
                return $inv->quantity * ($inv->selling_price ?? $inv->product->price);
            }),
            'top_products' => $inventories->sortByDesc('quantity')->take(10),
            'critical_stock' => $inventories->filter(function ($inv) {
                return $inv->quantity <= $inv->min_stock_level;
            })->sortBy('quantity'),
        ];

        return view('admin.inventory.report', compact('report', 'inventories'));
    }
}
