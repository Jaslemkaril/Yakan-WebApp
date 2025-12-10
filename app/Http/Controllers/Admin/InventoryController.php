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
        $this->middleware('auth:admin');
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

        return view('admin.inventory.index', compact('products', 'lowStockCount', 'totalProducts', 'totalValue'));
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
        ]);

        $inventory->restock($validated['quantity']);

        return redirect()->route('admin.inventory.index')
            ->with('success', "Successfully restocked {$validated['quantity']} units.");
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
