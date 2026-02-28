<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use Illuminate\Http\Request;

class AdminCustomOrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = CustomOrder::with(['user', 'product'])
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Search by user name, email, or order details
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', function($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('order_name', 'like', "%{$search}%")
                    ->orWhere('specifications', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
                });
            }

            $orders = $query->paginate($request->get('per_page', 20))->withQueryString();

            // Stats — always from all orders (unfiltered)
            $totalOrders  = CustomOrder::count();
            $todayOrders  = CustomOrder::whereDate('created_at', today())->count();
            $pendingCount = CustomOrder::where('status', 'pending')->count();
            $approvedCount = CustomOrder::where('status', 'approved')->count();
            $inProductionCount = CustomOrder::where('status', 'in_production')->count();
            $totalRevenue = CustomOrder::where('payment_status', 'paid')->sum('final_price');
            
            return view('admin.custom_orders.index_enhanced', compact(
                'orders', 'totalOrders', 'todayOrders', 'pendingCount',
                'approvedCount', 'inProductionCount', 'totalRevenue'
            ));
        } catch (\Exception $e) {
            \Log::error('Custom Orders Index Error: ' . $e->getMessage());
            return 'Custom Orders Error: ' . $e->getMessage();
        }
    }

    public function show(CustomOrder $order)
    {
        $order->load(['user', 'product']);
        return view('admin.custom_orders.details', compact('order'));
    }

    public function updateStatus(Request $request, CustomOrder $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,in_production,price_quoted,approved,production_complete,out_for_delivery,delivered,completed,cancelled',
            'final_price' => 'nullable|numeric|min:0'
        ]);

        try {
            $oldStatus = $order->status;
            $order->status = $request->status;
            
            if ($request->final_price) {
                $order->final_price = $request->final_price;
            }
            
            // If status changed to price_quoted, set user_notified_at
            if ($request->status === 'price_quoted' && $oldStatus !== 'price_quoted') {
                $order->user_notified_at = now();
            }
            
            // If status changed to processing or in_production, mark payment as paid
            if (in_array($request->status, ['processing', 'in_production']) && $order->payment_status !== 'paid') {
                $order->payment_status = 'paid';
            }
            
            // Set timestamps for workflow statuses
            if ($request->status === 'production_complete' && !$order->production_completed_at) {
                $order->production_completed_at = now();
            }
            
            if ($request->status === 'out_for_delivery' && !$order->out_for_delivery_at) {
                $order->out_for_delivery_at = now();
            }
            
            if ($request->status === 'delivered' && !$order->delivered_at) {
                $order->delivered_at = now();
            }
            
            $order->save();

            // Send notifications to user and admin
            if ($oldStatus !== $order->status) {
                $notificationService = new \App\Services\Notification\OrderStatusNotificationService();
                $notificationService->notifyCustomOrderStatusChange($order, $oldStatus, $order->status);
            }

            // If it's an AJAX request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order status updated successfully',
                    'order' => $order
                ]);
            }

            // Otherwise redirect back with success message
            return redirect()->back()->with('success', 'Order status updated successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order status: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    public function quotePrice(Request $request, CustomOrder $order)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            // Price breakdown fields (optional)
            'material_cost' => 'nullable|numeric|min:0',
            'pattern_fee' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            // Build price breakdown if any breakdown fields are provided
            $hasBreakdown = $request->filled('material_cost') || 
                           $request->filled('pattern_fee') || 
                           $request->filled('labor_cost') || 
                           $request->filled('delivery_fee') ||
                           $request->filled('discount');

            $adminNotes = $request->notes;

            if ($hasBreakdown) {
                // Create price breakdown structure
                $breakdown = [];
                
                if ($request->filled('material_cost') && $request->material_cost > 0) {
                    $breakdown['material_cost'] = (float) $request->material_cost;
                }
                if ($request->filled('pattern_fee') && $request->pattern_fee > 0) {
                    $breakdown['pattern_fee'] = (float) $request->pattern_fee;
                }
                if ($request->filled('labor_cost') && $request->labor_cost > 0) {
                    $breakdown['labor_cost'] = (float) $request->labor_cost;
                }
                if ($request->filled('delivery_fee') && $request->delivery_fee > 0) {
                    $breakdown['delivery_fee'] = (float) $request->delivery_fee;
                }
                if ($request->filled('discount') && $request->discount > 0) {
                    $breakdown['discount'] = (float) $request->discount;
                }

                // Store as structured data
                $adminNotes = [
                    'breakdown' => $breakdown,
                    'notes' => $request->notes,
                    'quoted_at' => now()->toISOString(),
                ];
            }

            // Use model method for business logic
            $success = $order->quotePrice($request->price, $adminNotes);
            
            if ($success) {
                // Auto-progression: Update status to price_quoted
                $order->status = 'price_quoted';
                $order->save();
                
                // Notify user
                $order->notifyUser();
                
                \Log::info('Price quoted for custom order (auto-progressed to price_quoted)', [
                    'order_id' => $order->id,
                    'price' => $request->price,
                    'has_breakdown' => $hasBreakdown,
                    'user_id' => $order->user_id
                ]);
                
                // If it's an AJAX/JSON request, return JSON
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Price quoted successfully. Status updated to "Price Quoted".',
                        'order' => $order->fresh()
                    ]);
                }

                // Otherwise redirect back with success message
                return redirect()->back()->with('success', 'Price quoted successfully. Status updated to "Price Quoted".');
            }
            
            // For non-success, respond appropriately based on request type
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be quoted in its current status'
                ], 422);
            }

            return redirect()->back()->with('error', 'Order cannot be quoted in its current status');
            
        } catch (\Exception $e) {
            \Log::error('Quote price error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to quote price: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to quote price: ' . $e->getMessage());
        }
    }

    public function verifyPayment(Request $request, CustomOrder $order)
    {
        try {
            $request->validate([
                'payment_status' => 'required|in:paid,failed',
                'payment_notes' => 'nullable|string'
            ]);

            $paymentStatus = $request->payment_status;
            $notes = $request->payment_notes ?? '';

            // Update payment status
            $order->payment_status = $paymentStatus;
            if ($notes) {
                $order->payment_notes = $notes;
            }
            
            // Auto-progression: If payment verified, move to approved status
            if ($paymentStatus === 'paid') {
                $order->status = 'approved';
                \Log::info('Payment verified - Order auto-progressed to approved', [
                    'order_id' => $order->id,
                    'admin_id' => auth('admin')->id() ?? auth()->id()
                ]);
            } elseif ($paymentStatus === 'failed') {
                $order->status = 'cancelled';
            }
            
            $order->save();

            \Log::info('Payment verified for custom order', [
                'order_id' => $order->id,
                'payment_status' => $paymentStatus,
                'new_status' => $order->status,
                'admin_id' => auth('admin')->id() ?? auth()->id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $paymentStatus === 'paid' 
                        ? 'Payment verified. Order automatically approved and ready for production.'
                        : 'Payment marked as failed.',
                    'order' => $order->fresh()
                ]);
            }

            return redirect()->back()->with('success', 'Payment status updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Payment verification validation error', [
                'order_id' => $order->id,
                'errors' => $e->errors()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Payment verification error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update payment status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update payment status.');
        }
    }

    public function confirmPayment(CustomOrder $order)
    {
        try {
            // Verify the order is in the correct state for payment confirmation
            if ($order->status !== 'approved') {
                return redirect()->back()->with('error', 'Order must be in approved status to confirm payment.');
            }

            if ($order->payment_status !== 'paid' || !$order->payment_receipt) {
                return redirect()->back()->with('error', 'Payment receipt must be uploaded before confirmation.');
            }

            // Confirm payment by adding a timestamp - payment_status stays 'paid'
            // (custom_orders enum only has: pending, paid, failed - no 'verified')
            $order->payment_confirmed_at = now();
            $order->save();

            \Log::info('Payment confirmed for custom order', [
                'order_id' => $order->id,
                'admin_id' => auth('admin')->id() ?? auth()->id()
            ]);

            return redirect()->back()->with('success', 'Payment confirmed. You can now start production.');
        } catch (\Exception $e) {
            \Log::error('Payment confirmation error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to confirm payment: ' . $e->getMessage());
        }
    }

    public function rejectPayment(CustomOrder $order)
    {
        try {
            // Verify the order is in the correct state for payment rejection
            if ($order->status !== 'approved') {
                return redirect()->back()->with('error', 'Order must be in approved status to reject payment.');
            }

            if (!$order->payment_receipt) {
                return redirect()->back()->with('error', 'No payment receipt found to reject.');
            }

            // Reject payment and set status back for customer to resubmit
            $order->payment_status = 'failed';
            $order->payment_receipt = null; // Clear the rejected receipt
            $order->status = 'price_quoted'; // Revert to quoted status for customer to resubmit
            $order->save();

            \Log::info('Payment rejected for custom order', [
                'order_id' => $order->id,
                'admin_id' => auth('admin')->id() ?? auth()->id()
            ]);

            return redirect()->back()->with('success', 'Payment rejected. Customer will need to resubmit payment.');
        } catch (\Exception $e) {
            \Log::error('Payment rejection error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to reject payment: ' . $e->getMessage());
        }
    }
    
    public function rejectOrder(Request $request, CustomOrder $order)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500'
        ]);
        
        try {
            $oldStatus = $order->status;
            $order->status = 'rejected';
            $order->rejected_at = now();
            $order->rejection_reason = $request->rejection_reason ?? 'Order rejected by admin';
            $order->save();
            
            \Log::info('Custom order rejected', [
                'order_id' => $order->id,
                'reason' => $order->rejection_reason,
                'user_id' => $order->user_id
            ]);

            // Send notifications
            if ($oldStatus !== 'rejected') {
                $notificationService = new \App\Services\Notification\OrderStatusNotificationService();
                $notificationService->notifyCustomOrderStatusChange($order, $oldStatus, 'rejected');
            }
            
            // If it's an AJAX request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order rejected successfully. Customer has been notified.',
                    'order' => $order->fresh()
                ]);
            }
            
            // Otherwise redirect back with success message
            return redirect()->back()->with('success', 'Order rejected successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Reject order error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reject order: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to reject order: ' . $e->getMessage());
        }
    }

    /**
     * Notify customer about production delay
     */
    public function notifyDelay(Request $request, CustomOrder $order)
    {
        $request->validate([
            'delay_reason' => 'required|string|max:1000'
        ]);
        
        try {
            $order->is_delayed = true;
            $order->delay_reason = $request->delay_reason;
            $order->delay_notified_at = now();
            $order->save();
            
            \Log::info('Production delay notification sent', [
                'order_id' => $order->id,
                'reason' => $order->delay_reason,
                'user_id' => $order->user_id
            ]);

            // Send email notification to customer
            try {
                $user = $order->user;
                if ($user && $user->email) {
                    \Mail::to($user->email)->send(new \App\Mail\CustomOrderDelayNotification($order));
                    \Log::info('Delay email sent', ['order_id' => $order->id, 'email' => $user->email]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send delay email', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Delay notification sent to customer successfully.',
                    'order' => $order->fresh()
                ]);
            }
            
            return redirect()->back()->with('success', 'Delay notification sent successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Notify delay error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send delay notification: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to send delay notification: ' . $e->getMessage());
        }
    }

    /**
     * Clear delay status
     */
    public function clearDelay(Request $request, CustomOrder $order)
    {
        try {
            $order->is_delayed = false;
            $order->delay_reason = null;
            $order->save();
            
            \Log::info('Delay status cleared', [
                'order_id' => $order->id,
                'user_id' => $order->user_id
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Delay status cleared successfully.',
                    'order' => $order->fresh()
                ]);
            }
            
            return redirect()->back()->with('success', 'Delay status cleared successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Clear delay error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to clear delay status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to clear delay status: ' . $e->getMessage());
        }
    }

    public function approveOrder(CustomOrder $order)
    {
        try {
            $oldStatus = $order->status;
            $order->status = 'approved';
            $order->approved_at = now();
            $order->save();

            // Send notifications
            if ($oldStatus !== 'approved') {
                $notificationService = new \App\Services\Notification\OrderStatusNotificationService();
                $notificationService->notifyCustomOrderStatusChange($order, $oldStatus, 'approved');
            }

            // If it's an AJAX request, return JSON
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order approved successfully',
                    'order' => $order
                ]);
            }

            // Otherwise redirect back with success message
            return redirect()->back()->with('success', 'Order approved successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve order: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to approve order: ' . $e->getMessage());
        }
    }

    public function destroy(CustomOrder $order)
    {
        try {
            // Check if order can be deleted (only pending or cancelled orders)
            if (!in_array($order->status, ['pending', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete order that is ' . $order->status
                ], 422);
            }

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportOrders(Request $request)
    {
        try {
            $query = CustomOrder::with(['user', 'product']);

            // Apply same filters as index
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $orders = $query->get();

            $csvData = [];
            $csvData[] = ['Order ID', 'Customer Name', 'Customer Email', 'Product', 'Status', 'Price', 'Quantity', 'Created Date'];

            foreach ($orders as $order) {
                $csvData[] = [
                    $order->id,
                    $order->user->name ?? 'Guest',
                    $order->user->email ?? 'N/A',
                    $order->product->name ?? 'Custom Product',
                    ucfirst(str_replace('_', ' ', $order->status)),
                    $order->final_price ? '₱' . number_format($order->final_price, 2) : 'Not set',
                    $order->quantity ?? 1,
                    $order->created_at->format('Y-m-d H:i:s')
                ];
            }

            $filename = 'custom_orders_' . date('Y-m-d_H-i-s') . '.csv';
            
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $csvData[0]);
            
            for ($i = 1; $i < count($csvData); $i++) {
                fputcsv($handle, $csvData[$i]);
            }
            
            fclose($handle);

            return response()->streamDownload(function() use ($csvData) {
                $output = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($output, $row);
                }
                fclose($output);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show admin custom order creation landing page
     */
    public function create()
    {
        return redirect()->route('admin.custom_orders.create.choice');
    }

    /**
     * Show custom order type choice selection for admin
     */
    public function createChoice(Request $request)
    {
        try {
            // Clear any existing admin wizard session
            if ($request->session()->has('admin_wizard')) {
                $request->session()->forget('admin_wizard');
            }

            return view('admin.custom_orders.wizard.choice');
            
        } catch (\Exception $e) {
            \Log::error('Admin choice selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.index')
                ->with('error', 'Unable to load custom order options. Please try again.');
        }
    }

    /**
     * Show product selection for admin product-based customization
     */
    public function createProductSelection(Request $request)
    {
        try {
            // Clear any existing admin wizard session
            if ($request->session()->has('admin_wizard')) {
                $request->session()->forget('admin_wizard');
            }

            // Get available products for customization
            $products = \App\Models\Product::where('status', 'active')
                ->orderBy('name')
                ->get();

            // Get users for assignment
            $users = \App\Models\User::where('role', 'user')->orderBy('name')->get();

            return view('admin.custom_orders.wizard.product_selection', compact('products', 'users'));
            
        } catch (\Exception $e) {
            \Log::error('Admin product selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.create.choice')
                ->with('error', 'Unable to load products. Please try again.');
        }
    }

    /**
     * Store admin product selection and redirect to customization
     */
    public function storeProductSelection(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'product_id' => 'required|exists:products,id',
                'product_name' => 'required|string',
                'product_category' => 'nullable|string',
                'product_price' => 'nullable|numeric|min:0',
            ]);

            // Store product selection in admin session
            $wizardData = [
                'user_id' => $validated['user_id'],
                'product' => [
                    'id' => $validated['product_id'],
                    'name' => $validated['product_name'],
                    'category' => $validated['product_category'],
                    'price' => $validated['product_price'],
                ],
                'step' => 'product_selected',
                'created_at' => now(),
            ];

            $request->session()->put('admin_wizard', $wizardData);
            \Log::info('Admin product stored in wizard session', ['product' => $validated['product_name']]);

            return redirect()->route('admin.custom_orders.create.product.customize');
            
        } catch (\Exception $e) {
            \Log::error('Admin store product selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.create.product')
                ->with('error', 'Unable to select product. Please try again.');
        }
    }

    /**
     * Show admin product customization page (patterns and colors)
     */
    public function createProductCustomization(Request $request)
    {
        try {
            $wizardData = $request->session()->get('admin_wizard');
            
            if (!$wizardData || !isset($wizardData['product'])) {
                return redirect()->route('admin.custom_orders.create.product')
                    ->with('error', 'Please select a product first.');
            }

            // Get product details
            $product = \App\Models\Product::find($wizardData['product']['id']);
            $user = \App\Models\User::find($wizardData['user_id']);
            
            return view('admin.custom_orders.wizard.step2', [
                'product' => $product,
                'user' => $user,
                'isAdminFlow' => true // Flag to indicate this is admin flow
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Admin product customization error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.create.product')
                ->with('error', 'Unable to load customization options. Please try again.');
        }
    }

    /**
     * Store admin product customization (patterns and colors)
     */
    public function storeProductCustomization(Request $request)
    {
        try {
            $validated = $request->validate([
                'pattern' => 'required|string',
                'colors' => 'required|array',
                'colors.*' => 'required|string',
                'pattern_data' => 'nullable|array',
                'quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string',
                'estimated_price' => 'nullable|numeric|min:0',
            ]);

            $wizardData = $request->session()->get('admin_wizard');
            $wizardData['pattern'] = $validated['pattern'];
            $wizardData['colors'] = $validated['colors'];
            $wizardData['pattern_data'] = $validated['pattern_data'] ?? [];
            $wizardData['quantity'] = $validated['quantity'];
            $wizardData['notes'] = $validated['notes'] ?? '';
            $wizardData['estimated_price'] = $validated['estimated_price'] ?? 0;
            $wizardData['step'] = 'customization_complete';
            
            $request->session()->put('admin_wizard', $wizardData);
            
            \Log::info('Admin product customization stored', [
                'product' => $wizardData['product']['name'] ?? 'Unknown',
                'pattern' => $validated['pattern']
            ]);

            return redirect()->route('admin.custom_orders.create.review');
            
        } catch (\Exception $e) {
            \Log::error('Admin store product customization error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.create.product.customize')
                ->with('error', 'Unable to save customization. Please try again.');
        }
    }

    /**
     * Show fabric selection for admin fabric-first flow
     */
    public function createFabricSelection(Request $request)
    {
        try {
            // Clear any existing admin wizard session
            if ($request->session()->has('admin_wizard')) {
                $request->session()->forget('admin_wizard');
            }

            // Get users for assignment
            $users = \App\Models\User::where('role', 'user')->orderBy('name')->get();

            return view('admin.custom_orders.wizard.fabric_selection', compact('users'));
            
        } catch (\Exception $e) {
            \Log::error('Admin fabric selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.create.choice')
                ->with('error', 'Unable to load fabric selection. Please try again.');
        }
    }

    /**
     * Store admin fabric selection
     */
    public function storeFabricSelection(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'fabric_type' => 'required|string',
                'fabric_quantity_meters' => 'required|numeric|min:0.5',
                'intended_use' => 'required|string',
            ]);

            $wizardData = [
                'user_id' => $validated['user_id'],
                'fabric' => [
                    'type' => $validated['fabric_type'],
                    'quantity_meters' => $validated['fabric_quantity_meters'],
                    'intended_use' => $validated['intended_use'],
                ],
                'step' => 'fabric_selected',
                'created_at' => now(),
            ];

            $request->session()->put('admin_wizard', $wizardData);

            return redirect()->route('admin.custom_orders.create.pattern');
            
        } catch (\Exception $e) {
            \Log::error('Admin store fabric selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.create.fabric')
                ->with('error', 'Unable to save fabric selection. Please try again.');
        }
    }

    /**
     * Show pattern selection for admin fabric-first flow
     */
    public function createPatternSelection(Request $request)
    {
        try {
            $wizardData = $request->session()->get('admin_wizard');
            
            if (!$wizardData || !isset($wizardData['fabric'])) {
                return redirect()->route('admin.custom_orders.create.fabric')
                    ->with('error', 'Please select fabric first.');
            }

            $user = \App\Models\User::find($wizardData['user_id']);
            
            // Get active patterns from database with their media
            $patterns = \App\Models\YakanPattern::with('media')
                ->where('is_active', true)
                ->orderBy('popularity_score', 'desc')
                ->orderBy('name', 'asc')
                ->get();
            
            return view('admin.custom_orders.wizard.step2', [
                'user' => $user,
                'patterns' => $patterns,
                'isAdminFlow' => true,
                'isFabricFlow' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Admin pattern selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.create.fabric')
                ->with('error', 'Unable to load pattern selection. Please try again.');
        }
    }

    /**
     * Store admin pattern selection
     */
    public function storePatternSelection(Request $request)
    {
        try {
            $validated = $request->validate([
                'pattern' => 'required|string',
                'colors' => 'required|array',
                'colors.*' => 'required|string',
                'pattern_data' => 'nullable|array',
                'notes' => 'nullable|string',
                'estimated_price' => 'nullable|numeric|min:0',
            ]);

            $wizardData = $request->session()->get('admin_wizard');
            $wizardData['pattern'] = $validated['pattern'];
            $wizardData['colors'] = $validated['colors'];
            $wizardData['pattern_data'] = $validated['pattern_data'] ?? [];
            $wizardData['notes'] = $validated['notes'] ?? '';
            $wizardData['estimated_price'] = $validated['estimated_price'] ?? 0;
            $wizardData['step'] = 'pattern_complete';
            
            $request->session()->put('admin_wizard', $wizardData);

            return redirect()->route('admin.custom_orders.create.review');
            
        } catch (\Exception $e) {
            \Log::error('Admin store pattern selection error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.create.pattern')
                ->with('error', 'Unable to save pattern selection. Please try again.');
        }
    }

    /**
     * Show review and final creation page for admin
     */
    public function createReview(Request $request)
    {
        try {
            $wizardData = $request->session()->get('admin_wizard');
            
            if (!$wizardData) {
                return redirect()->route('admin.custom_orders.create.choice')
                    ->with('error', 'No order data found. Please start over.');
            }

            $user = \App\Models\User::find($wizardData['user_id']);
            
            // Get user's saved addresses
            $userAddresses = \App\Models\UserAddress::where('user_id', $wizardData['user_id'])
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
            $defaultAddress = \App\Models\UserAddress::where('user_id', $wizardData['user_id'])
                ->where('is_default', true)
                ->first();
            
            return view('admin.custom_orders.wizard.review', compact('wizardData', 'user', 'userAddresses', 'defaultAddress'));
            
        } catch (\Exception $e) {
            \Log::error('Admin review error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.create.choice')
                ->with('error', 'Unable to load review page. Please try again.');
        }
    }

    /**
     * Store the complete admin custom order
     */
    public function store(Request $request)
    {
        try {
            $wizardData = $request->session()->get('admin_wizard');
            
            if (!$wizardData) {
                return redirect()->route('admin.custom_orders.create.choice')
                    ->with('error', 'No order data found. Please start over.');
            }

            // Create the custom order
            $customOrder = \App\Models\CustomOrder::create([
                'user_id' => $wizardData['user_id'],
                'product_id' => $wizardData['product']['id'] ?? null,
                'fabric_type' => $wizardData['fabric']['type'] ?? null,
                'fabric_quantity_meters' => $wizardData['fabric']['quantity_meters'] ?? null,
                'intended_use' => $wizardData['fabric']['intended_use'] ?? null,
                'pattern' => $wizardData['pattern'] ?? null,
                'colors' => json_encode($wizardData['colors'] ?? []),
                'pattern_data' => json_encode($wizardData['pattern_data'] ?? []),
                'quantity' => $wizardData['quantity'] ?? 1,
                'notes' => $wizardData['notes'] ?? '',
                'estimated_price' => $wizardData['estimated_price'] ?? 0,
                'status' => 'pending',
                'admin_created' => true,
                'created_by' => auth()->guard('admin')->id(),
            ]);

            // Clear admin wizard session
            $request->session()->forget('admin_wizard');

            \Log::info('Admin custom order created', ['order_id' => $customOrder->id]);

            return redirect()->route('admin.custom_orders.show', $customOrder)
                ->with('success', 'Custom order created successfully!');

        } catch (\Exception $e) {
            \Log::error('Admin store custom order error: ' . $e->getMessage());
            return redirect()->route('admin.custom_orders.create.review')
                ->with('error', 'Unable to create custom order. Please try again.');
        }
    }

    /**
     * Update order details
     */
    public function update(Request $request, CustomOrder $order)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,processing,completed,cancelled',
                'final_price' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $order->update($validated);

            \Log::info('Admin updated order', ['order_id' => $order->id, 'changes' => $validated]);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin update order error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to update order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark order as completed
     */
    public function markCompleted(Request $request, CustomOrder $order)
    {
        try {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            \Log::info('Admin marked order as completed', ['order_id' => $order->id]);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as completed successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin mark completed error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to mark order as completed'
            ], 500);
        }
    }

    /**
     * Send notification to customer
     */
    public function sendNotification(Request $request, CustomOrder $order)
    {
        try {
            // Send email notification to customer
            if ($order->user && $order->user->email) {
                // Here you would implement email sending logic
                // Mail::to($order->user->email)->send(new OrderNotification($order));
                
                \Log::info('Admin sent notification to customer', ['order_id' => $order->id, 'email' => $order->user->email]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin send notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to send notification'
            ], 500);
        }
    }

    /**
     * Bulk operations
     */
    public function bulkApprove(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids', []);
            
            CustomOrder::whereIn('id', $orderIds)
                ->where('status', 'price_quoted')
                ->update(['status' => 'processing']);

            \Log::info('Admin bulk approved orders', ['order_ids' => $orderIds]);

            return response()->json([
                'success' => true,
                'message' => count($orderIds) . ' orders approved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin bulk approve error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to bulk approve orders'
            ], 500);
        }
    }

    public function bulkReject(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids', []);
            
            CustomOrder::whereIn('id', $orderIds)
                ->whereIn('status', ['pending', 'price_quoted'])
                ->update(['status' => 'cancelled']);

            \Log::info('Admin bulk rejected orders', ['order_ids' => $orderIds]);

            return response()->json([
                'success' => true,
                'message' => count($orderIds) . ' orders rejected successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin bulk reject error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to bulk reject orders'
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids', []);
            
            CustomOrder::whereIn('id', $orderIds)->delete();

            \Log::info('Admin bulk deleted orders', ['order_ids' => $orderIds]);

            return response()->json([
                'success' => true,
                'message' => count($orderIds) . ' orders deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin bulk delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to bulk delete orders'
            ], 500);
        }
    }

    /**
     * Enhanced index with advanced filtering
     */
    public function indexEnhanced(Request $request)
    {
        try {
            $query = CustomOrder::with(['user', 'product'])
                ->orderBy('created_at', 'desc');

            // Advanced filtering
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhereHas('user', function($subQ) use ($search) {
                          $subQ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('customer_type') && $request->customer_type) {
                if ($request->customer_type === 'admin_created') {
                    $query->where('admin_created', true);
                } elseif ($request->customer_type === 'user_created') {
                    $query->where('admin_created', false);
                }
            }

            if ($request->has('price_range') && $request->price_range) {
                $range = $request->price_range;
                if ($range === '0-1000') {
                    $query->where(function($q) {
                        $q->where('final_price', '<=', 1000)
                          ->orWhere('estimated_price', '<=', 1000);
                    });
                } elseif ($range === '1000-5000') {
                    $query->where(function($q) {
                        $q->whereBetween('final_price', [1000, 5000])
                          ->orWhereBetween('estimated_price', [1000, 5000]);
                    });
                } elseif ($range === '5000-10000') {
                    $query->where(function($q) {
                        $q->whereBetween('final_price', [5000, 10000])
                          ->orWhereBetween('estimated_price', [5000, 10000]);
                    });
                } elseif ($range === '10000+') {
                    $query->where(function($q) {
                        $q->where('final_price', '>', 10000)
                          ->orWhere('estimated_price', '>', 10000);
                    });
                }
            }

            // Sorting
            if ($request->has('sort')) {
                switch ($request->sort) {
                    case 'created_at_asc':
                        $query->orderBy('created_at', 'asc');
                        break;
                    case 'price_high':
                        $query->orderByRaw('COALESCE(final_price, estimated_price, 0) DESC');
                        break;
                    case 'price_low':
                        $query->orderByRaw('COALESCE(final_price, estimated_price, 0) ASC');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                }
            }

            $perPage = $request->get('per_page', 20);
            $orders = $query->paginate($perPage);
            
            // Calculate statistics
            $totalOrders = CustomOrder::count();
            $todayOrders = CustomOrder::whereDate('created_at', today())->count();
            $pendingCount = CustomOrder::where('status', 'pending')->count();
            $totalRevenue = CustomOrder::where('payment_status', 'paid')->sum('final_price');
            
            $stats = compact('totalOrders', 'todayOrders', 'pendingCount', 'totalRevenue');
            
            return view('admin.custom_orders.index_enhanced', compact('orders', 'stats'));
            
        } catch (\Exception $e) {
            \Log::error('Enhanced Custom Orders Index Error: ' . $e->getMessage());
            return 'Custom Orders Error: ' . $e->getMessage();
        }
    }

    /**
     * Production dashboard
     */
    public function productionDashboard(Request $request)
    {
        try {
            $stats = [
                'total_orders' => CustomOrder::count(),
                'pending_orders' => CustomOrder::where('status', 'pending')->count(),
                'processing_orders' => CustomOrder::where('status', 'processing')->count(),
                'completed_orders' => CustomOrder::where('status', 'completed')->count(),
                'total_revenue' => CustomOrder::where('status', 'completed')->sum('final_price'),
                'avg_processing_time' => $this->calculateAverageProcessingTime(),
            ];

            $recentOrders = CustomOrder::with(['user', 'product'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return view('admin.custom_orders.production_dashboard', compact('stats', 'recentOrders'));
            
        } catch (\Exception $e) {
            \Log::error('Production Dashboard Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to load production dashboard');
        }
    }

    /**
     * Calculate average processing time
     */
    private function calculateAverageProcessingTime()
    {
        try {
            $completedOrders = CustomOrder::where('status', 'completed')
                ->whereNotNull('completed_at')
                ->get();

            if ($completedOrders->isEmpty()) {
                return 0;
            }

            $totalMinutes = $completedOrders->sum(function($order) {
                return $order->created_at->diffInMinutes($order->completed_at);
            });

            return round($totalMinutes / $completedOrders->count(), 2);
            
        } catch (\Exception $e) {
            return 0;
        }
    }
}
