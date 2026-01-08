<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Get authenticated user (required now)
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required to create an order'
                ], 401);
            }

            $validated = $request->validate([
                'customer_name' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email',
                'customer_phone' => 'required|string|max:20',
                'shipping_address' => 'required|string',
                'delivery_address' => 'required|string',
                'payment_method' => 'required|string|in:gcash,bank_transfer,cash',
                'payment_status' => 'nullable|string|in:pending,paid,verified,failed',
                'payment_reference' => 'nullable|string',
                'subtotal' => 'required|numeric|min:0',
                'shipping_fee' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'total_amount' => 'nullable|numeric|min:0',
                'delivery_type' => 'nullable|string|in:pickup,deliver',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'gcash_receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
                'bank_receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            // Handle receipt file uploads
            $gcashReceiptPath = null;
            $bankReceiptPath = null;

            if ($request->hasFile('gcash_receipt')) {
                $gcashReceiptPath = $request->file('gcash_receipt')->store('receipts', 'public');
            }

            if ($request->hasFile('bank_receipt')) {
                $bankReceiptPath = $request->file('bank_receipt')->store('receipts', 'public');
            }

            $isPrepaid = in_array($validated['payment_method'], ['gcash', 'bank_transfer']);
            $paymentStatus = ($validated['payment_status'] ?? null) === 'paid'
                ? 'paid'
                : ($isPrepaid ? 'paid' : ($validated['payment_status'] ?? 'pending'));
            
            // If payment is paid (from mobile), set status to processing immediately
            $orderStatus = $paymentStatus === 'paid' ? 'processing' : 'pending';

            $orderRef = 'ORD-' . strtoupper(uniqid());

            // Use authenticated user's info (always available now)
            $customerName = $validated['customer_name'] ?? $user->name;
            $customerEmail = $validated['customer_email'] ?? $user->email;

            $order = Order::create([
                'order_ref' => $orderRef,
                'tracking_number' => $orderRef,
                'user_id' => $user->id,  // Always link to authenticated user
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'delivery_address' => $validated['delivery_address'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => $paymentStatus,
                'payment_reference' => $validated['payment_reference'] ?? null,
                'subtotal' => $validated['subtotal'],
                'shipping_fee' => $validated['shipping_fee'] ?? 0,
                'discount' => $validated['discount'] ?? 0,
                'total_amount' => $validated['total'] ?? $validated['total_amount'],
                'delivery_type' => $validated['delivery_type'] ?? 'deliver',
                'status' => $orderStatus,
                'notes' => $validated['notes'] ?? null,
                'source' => 'mobile',
                'gcash_receipt' => $gcashReceiptPath,
                'bank_receipt' => $bankReceiptPath,
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $order->load('items'),
                'message' => 'Order created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $orders = Order::with(['items.product' => function($query) {
            $query->select('id', 'name', 'image');
        }])->latest()->get();
        
        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders fetched successfully'
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['items.product' => function($query) {
            $query->select('id', 'name', 'image');
        }])->find($id);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order fetched successfully'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,processing,shipped,delivered,completed,cancelled'
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order status updated successfully'
        ]);
    }

    public function uploadReceipt(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $request->validate([
            'gcash_receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'bank_receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $updateData = [];

        if ($request->hasFile('gcash_receipt')) {
            $updateData['gcash_receipt'] = $request->file('gcash_receipt')->store('receipts', 'public');
        }

        if ($request->hasFile('bank_receipt')) {
            $updateData['bank_receipt'] = $request->file('bank_receipt')->store('receipts', 'public');
        }

        if (!empty($updateData)) {
            // Update payment status to paid when receipt is uploaded
            $updateData['payment_status'] = 'paid';
            $updateData['status'] = 'processing';
            
            $order->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $order->fresh(),
                'message' => 'Receipt uploaded successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No receipt file provided'
        ], 400);
    }

    public function adminIndex()
    {
        $orders = Order::with('items', 'user')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Admin orders fetched successfully'
        ]);
    }
}
