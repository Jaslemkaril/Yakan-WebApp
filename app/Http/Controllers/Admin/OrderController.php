<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    // Show all orders
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product'])->orderByDesc('created_at');

        // Advanced filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_amount')) {
            $query->where('total_amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $orders = $query->paginate($perPage)->appends($request->all());

        // Calculate statistics
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::whereRaw('LOWER(status) = ?', ['pending'])->count(),
            'processing_orders' => Order::whereRaw('LOWER(status) = ?', ['processing'])->count(),
            'shipped_orders' => Order::whereRaw('LOWER(status) = ?', ['shipped'])->count(),
            'delivered_orders' => Order::whereRaw('LOWER(status) = ?', ['delivered'])->count(),
            'cancelled_orders' => Order::whereRaw('LOWER(status) = ?', ['cancelled'])->count(),
            'total_revenue' => Order::whereIn('payment_status', ['paid', 'completed'])->sum('total_amount'),
            'pending_revenue' => Order::where('payment_status', 'pending')->sum('total_amount'),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())->whereIn('payment_status', ['paid', 'completed'])->sum('total_amount'),
        ];

        if ($request->ajax()) {
            return view('admin.orders.partials.orders-rows', compact('orders'))->render();
        }

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    // Show single order
    public function show(Order $order)
    {
        $order->load('user', 'orderItems.product.category');
        return view('admin.orders.show', compact('order'));
    }

    // Update order status
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;
        
        // Update payment status if provided
        if ($request->filled('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        
        // Auto-update payment status based on order status
        if ($request->status === 'processing' && $order->payment_status === 'pending') {
            $order->payment_status = 'paid';
        }
        
        if ($request->status === 'delivered' && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
        }
        
        if ($request->status === 'cancelled') {
            $order->payment_status = 'failed';
        }
        
        // sync tracking status and history
        $order->tracking_status = ucfirst($request->status);
        $order->appendTrackingEvent(ucfirst($request->status));
        $order->save();

        // Send notifications to user and admin
        if ($oldStatus !== $request->status) {
            $notificationService = new \App\Services\Notification\OrderStatusNotificationService();
            $notificationService->notifyOrderStatusChange($order, $oldStatus, $request->status);
        }

        return redirect()->back()->with('success', 'Order and payment status updated successfully!');
    }

    // Update tracking information
    public function updateTracking(Request $request, Order $order)
    {
        $request->validate([
            'tracking_status' => 'nullable|string|max:255',
            'courier_name' => 'nullable|string|max:255',
            'courier_contact' => 'nullable|string|max:255',
            'courier_tracking_url' => 'nullable|url|max:500',
            'estimated_delivery_date' => 'nullable|date',
            'tracking_notes' => 'nullable|string|max:1000',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_latitude' => 'nullable|numeric|between:-90,90',
            'delivery_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Update tracking fields
        if ($request->filled('tracking_status')) {
            $order->tracking_status = $request->tracking_status;
            
            // Add to tracking history
            $history = $order->tracking_history ?? [];
            
            // Decode JSON if it's a string
            if (is_string($history)) {
                $history = json_decode($history, true) ?? [];
            }
            
            // Ensure it's an array
            if (!is_array($history)) {
                $history = [];
            }
            
            array_unshift($history, [
                'status' => $request->tracking_status,
                'date' => now()->format('M d, Y h:i A'),
                'note' => $request->tracking_notes
            ]);
            $order->tracking_history = json_encode($history);
        }

        $order->courier_name = $request->courier_name;
        $order->courier_contact = $request->courier_contact;
        $order->courier_tracking_url = $request->courier_tracking_url;
        $order->estimated_delivery_date = $request->estimated_delivery_date;
        $order->tracking_notes = $request->tracking_notes;
        $order->delivery_address = $request->delivery_address;
        $order->delivery_latitude = $request->delivery_latitude;
        $order->delivery_longitude = $request->delivery_longitude;

        // If status is delivered, set delivered_at
        if ($request->tracking_status === 'Delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
            $order->status = 'delivered';
        }

        $order->save();

        return redirect()->back()->with('success', 'Tracking information updated successfully!');
    }

    // Quick update order status
    public function quickUpdateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
        ]);

        $order->status = $request->status;
        
        // Update payment status if provided
        if ($request->filled('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        
        // Auto-update payment status based on order status
        if ($request->status === 'processing' && $order->payment_status === 'pending') {
            $order->payment_status = 'paid';
        }
        
        if ($request->status === 'delivered' && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
        }
        
        if ($request->status === 'cancelled') {
            $order->payment_status = 'failed';
        }
        
        // sync tracking status and history
        $order->tracking_status = ucfirst($request->status);
        $order->appendTrackingEvent(ucfirst($request->status));
        $order->save();

        return redirect()->back()->with('success', 'Order status updated!');
    }

    // Update admin notes
    public function updateNotes(Request $request, Order $order)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $order->admin_notes = $request->admin_notes;
        $order->save();

        return redirect()->back()->with('success', 'Admin notes updated successfully!');
    }

    // Refund order
    public function refund(Request $request, Order $order)
    {
        if ($order->status !== 'completed') {
            return redirect()->back()->with('error', 'Only completed orders can be refunded.');
        }

        $order->status = 'refunded';
        $order->payment_status = 'refunded';
        $order->save();

        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $product->stock += $item->quantity;
            $product->save();
        }

        return redirect()->back()->with('success', 'Order refunded successfully.');
    }

    /**
     * Show the form for creating a new order
     */
    public function create()
    {
        $users = \App\Models\User::all();
        $products = \App\Models\Product::where('status', 'active')->get();
        
        return view('admin.orders.create', compact('users', 'products'));
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $user = \App\Models\User::find($validated['user_id']);
        $totalAmount = 0;

        // Create the order
        $order = \App\Models\Order::create([
            'user_id' => $validated['user_id'],
            'total_amount' => 0, // Will be calculated below
            'status' => 'pending',
            'payment_status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Add order items
        foreach ($validated['items'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            
            // Check stock availability
            if ($product->stock < $item['quantity']) {
                return redirect()->back()
                    ->with('error', "Insufficient stock for {$product->name}. Available: {$product->stock}, Requested: {$item['quantity']}")
                    ->withInput();
            }

            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;

            // Create order item
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);

            // Update product stock
            $product->stock -= $item['quantity'];
            $product->save();
        }

        // Update order total
        $order->total_amount = $totalAmount;
        $order->save();

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order created successfully!');
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit(Order $order)
    {
        $users = \App\Models\User::all();
        $products = \App\Models\Product::where('status', 'active')->get();
        
        return view('admin.orders.edit', compact('order', 'users', 'products'));
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        // Only allow editing if order is still pending
        if ($order->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending orders can be edited.');
        }

        // Restore original stock
        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $product->stock += $item->quantity;
            $product->save();
        }

        // Delete existing order items
        $order->orderItems()->delete();

        $totalAmount = 0;

        // Add new order items
        foreach ($validated['items'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            
            // Check stock availability
            if ($product->stock < $item['quantity']) {
                return redirect()->back()
                    ->with('error', "Insufficient stock for {$product->name}. Available: {$product->stock}, Requested: {$item['quantity']}")
                    ->withInput();
            }

            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;

            // Create order item
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);

            // Update product stock
            $product->stock -= $item['quantity'];
            $product->save();
        }

        // Update order
        $order->update([
            'user_id' => $validated['user_id'],
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order updated successfully!');
    }

    // Optional: Place order (depends on your cart logic)
    public function placeOrder(Request $request)
    {
        // Implement your order placing logic here
    }

    // Generate invoice for an order
    public function generateInvoice(Order $order)
    {
        $order->load(['user', 'orderItems.product']);

        return view('admin.orders.invoice', compact('order'));
    }
}