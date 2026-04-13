<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
                'shipping_city' => 'nullable|string|max:100',
                'shipping_province' => 'nullable|string|max:100',
                'shipping_zip' => 'nullable|string|max:20',
                'shipping_barangay' => 'nullable|string|max:150',
                'shipping_street' => 'nullable|string|max:255',
                'payment_method' => 'required|string|in:gcash,maya,bank_transfer,cash,online_banking,paymongo',
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

            // Normalise payment_method: 'online_banking' → 'maya'
            $dbPaymentMethod = $validated['payment_method'] === 'online_banking' ? 'maya' : $validated['payment_method'];

            // status ENUM: pending_confirmation, confirmed, processing, shipped, delivered, cancelled, refunded
            $orderStatus = $paymentStatus === 'paid' ? 'processing' : 'pending_confirmation';

            $orderRef = 'ORD-' . strtoupper(Str::random(12));

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
                'shipping_city' => $validated['shipping_city'] ?? null,
                'shipping_province' => $validated['shipping_province'] ?? null,
                'payment_method' => $dbPaymentMethod,
                'payment_status' => $paymentStatus,
                'payment_reference' => $validated['payment_reference'] ?? null,
                // Totals are recalculated server-side after item prices are resolved
                'subtotal' => 0,
                'shipping_fee' => $validated['shipping_fee'] ?? 0,
                'discount' => $validated['discount'] ?? 0,
                'total_amount' => 0,
                'delivery_type' => $validated['delivery_type'] ?? 'deliver',
                'status' => $orderStatus,
                'notes' => $validated['notes'] ?? null,
                'source' => 'mobile',
                'gcash_receipt' => $gcashReceiptPath,
                'bank_receipt' => $bankReceiptPath,
            ]);

            // Resolve server-side prices — never trust client-supplied prices
            $serverSubtotal = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitPrice = $product->price;
                $serverSubtotal += $unitPrice * $item['quantity'];
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $unitPrice,
                ]);
            }

            $shippingFee = $validated['shipping_fee'] ?? 0;
            $discount    = $validated['discount'] ?? 0;
            $order->update([
                'subtotal'     => $serverSubtotal,
                'total_amount' => max(0, $serverSubtotal + $shippingFee - $discount),
            ]);

            return response()->json([
                'success' => true,
                'data' => $order->load('items'),
                'message' => 'Order created successfully'
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Order store error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Order could not be created. Please try again.'
            ], 500);
        }
    }

    public function index()
    {
        $user = request()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $orders = Order::with(['items.product' => function($query) {
            $query->select('id', 'name', 'image');
        }])->where('user_id', $user->id)->latest()->get();
        
        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders fetched successfully'
        ]);
    }

    public function show($id)
    {
        $user = request()->user();

        $order = Order::with(['items.product' => function($query) {
            $query->select('id', 'name', 'image');
        }])->where('id', $id)->where('user_id', $user?->id)->first();
        
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
        $cloudinary = new \App\Services\CloudinaryService();

        if ($request->hasFile('gcash_receipt')) {
            $file = $request->file('gcash_receipt');
            if ($cloudinary->isEnabled()) {
                $result = $cloudinary->uploadFile($file, 'receipts');
                $updateData['gcash_receipt'] = $result ? $result['url'] : $file->store('receipts', 'public');
            } else {
                $updateData['gcash_receipt'] = $file->store('receipts', 'public');
            }
        }

        if ($request->hasFile('bank_receipt')) {
            $file = $request->file('bank_receipt');
            if ($cloudinary->isEnabled()) {
                $result = $cloudinary->uploadFile($file, 'receipts');
                $updateData['bank_receipt'] = $result ? $result['url'] : $file->store('receipts', 'public');
            } else {
                $updateData['bank_receipt'] = $file->store('receipts', 'public');
            }
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

    public function cancel(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:255',
            ]);

            $order = Order::where('id', $id)->where('user_id', $user->id)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if (!in_array($order->status, ['pending', 'pending_confirmation'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be cancelled.',
                ], 422);
            }

            \DB::beginTransaction();

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'admin_notes' => 'Customer cancelled: ' . $validated['reason'],
            ]);

            foreach ($order->items as $item) {
                $inventory = \App\Models\Inventory::where('product_id', $item->product_id)->first();
                if ($inventory) {
                    $inventory->increment('quantity', $item->quantity);
                }

                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order has been cancelled.',
                'data' => $order->fresh(['items.product', 'user']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Order cancel error', ['message' => $e->getMessage(), 'order_id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order. Please try again.',
            ], 500);
        }
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
