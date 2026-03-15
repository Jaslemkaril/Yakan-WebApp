<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Process payment using selected gateway
     */
    public function processPayment(Request $request, CustomOrder $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'payment_method' => 'required|in:gcash,maya,online_banking,bank_transfer',
        ]);

        try {
            // Generate a simple transaction ID and save payment method
            $order->payment_method = $request->payment_method;
            $order->transaction_id = strtoupper($request->payment_method) . '_' . uniqid();
            $order->save();

            // For bank transfer, redirect to instructions
            if ($request->payment_method === 'bank_transfer') {
                return redirect()->route('custom_orders.payment_instructions', $order);
            }

            // For other methods, you might redirect to payment gateways
            // For now, redirect to instructions as well
            return redirect()->route('custom_orders.payment_instructions', $order);
            
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'order_id' => $order->id,
                'method' => $request->payment_method,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Payment processing failed. Please try again.');
        }
    }

    /**
     * Show payment instructions for bank transfer
     */
    public function showPaymentInstructions(CustomOrder $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if (!$order->payment_method) {
            return redirect()->route('custom_orders.payment', $order);
        }

        // Build instructions from system settings
        $isGcash = in_array($order->payment_method, ['gcash', 'maya', 'online_banking']);

        if ($isGcash) {
            $isMaya = $order->payment_method === 'maya';
            $instructions = [
                'title'         => $isMaya ? 'Maya Payment Instructions' : 'GCash Payment Instructions',
                'gcash_number'  => $isMaya
                    ? SystemSetting::get('maya_number', SystemSetting::get('gcash_number', ''))
                    : SystemSetting::get('gcash_number', ''),
                'account_name'  => $isMaya
                    ? SystemSetting::get('maya_name', SystemSetting::get('gcash_name', 'Tuwas Yakan'))
                    : SystemSetting::get('gcash_name', 'Tuwas Yakan'),
                'steps'         => [
                    'Open your ' . ($isMaya ? 'Maya' : 'GCash') . ' app and tap "Send Money".',
                    'Enter the ' . ($isMaya ? 'Maya' : 'GCash') . ' number shown below.',
                    'Enter the exact amount: ₱' . number_format($order->final_price, 2) . '.',
                    'Use your Order ID (' . $order->id . ') as the payment message/reference.',
                    'Take a screenshot of the success screen.',
                    'Come back here and confirm your payment below.',
                ],
                'amount'         => $order->final_price,
                'reference_code' => $order->transaction_id ?? 'ORDER-' . $order->id,
                'notes'          => 'Include Order #' . $order->id . ' in your ' . ($isMaya ? 'Maya' : 'GCash') . ' message for quick verification.',
            ];
        } else {
            $instructions = [
                'title'          => 'Bank Transfer Instructions',
                'bank_name'      => SystemSetting::get('bank_name', ''),
                'account_name'   => SystemSetting::get('bank_account_name', 'Tuwas Yakan'),
                'account_number' => SystemSetting::get('bank_account_number', ''),
                'branch'         => SystemSetting::get('bank_branch', ''),
                'steps'          => [
                    'Go to your bank or use your online banking app.',
                    'Transfer to the bank account details shown below.',
                    'Enter the exact amount: ₱' . number_format($order->final_price, 2) . '.',
                    'Use your Order ID (' . $order->id . ') as the payment reference.',
                    'Keep your transfer receipt or confirmation.',
                    'Come back here and confirm your payment below.',
                ],
                'amount'         => $order->final_price,
                'reference_code' => $order->transaction_id ?? 'ORDER-' . $order->id,
                'notes'          => 'Include Order #' . $order->id . ' in the transfer reference for quick verification.',
            ];
        }

        return view('custom_orders.payment_instructions', compact('order', 'instructions'));
    }

    /**
     * Handle payment return from gateway
     */
    public function paymentReturn(Request $request, string $gateway)
    {
        $transactionId = $request->get('transaction_id') ?: $request->get('payment_request_id');
        
        if (!$transactionId) {
            return redirect()->route('custom_orders.index')
                ->with('error', 'Payment return failed: No transaction ID found');
        }

        // For now, just redirect to orders with a success message
        // In a real implementation, you would verify the payment with the gateway
        return redirect()->route('custom_orders.index')
            ->with('success', 'Payment completed successfully!');
    }

    /**
     * Handle webhook from payment gateways
     */
    public function handleWebhook(Request $request, string $gateway)
    {
        $payload = $request->all();
        
        Log::info('Webhook received', ['gateway' => $gateway, 'payload' => $payload]);

        // For now, just log the webhook and return success
        // In a real implementation, you would process the webhook and update payment status
        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request, CustomOrder $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if (!$order->payment_method || !$order->transaction_id) {
            return response()->json(['status' => 'no_payment']);
        }

        // For now, just return the current payment status from database
        // In a real implementation, you might check with payment gateways
        return response()->json([
            'status' => $order->payment_status,
            'details' => [
                'payment_method' => $order->payment_method,
                'transaction_id' => $order->transaction_id,
                'amount' => $order->final_price,
                'paid_at' => $order->paid_at,
                'updated_at' => $order->updated_at
            ],
        ]);
    }

    /**
     * Upload payment proof for a regular order (mobile/web)
     */
    public function uploadProof(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'proof_image' => 'required|file|mimes:jpeg,png,jpg,webp|max:5120', // 5MB
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Store proof under storage/app/public/payment_proofs
        $path = $request->file('proof_image')->store('payment_proofs', 'public');

        // Store relative path only, not full URL
        $order->payment_proof_path = $path;
        $order->payment_status = 'paid';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment proof uploaded',
            'data' => [
                'order_id' => $order->id,
                'payment_status' => $order->payment_status,
                'payment_proof_url' => asset('storage/' . $path),
            ],
        ]);
    }
}
