<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Upload payment proof
     */
    public function uploadProof(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
                'proof_image' => 'required|image|max:5120', // 5MB max
            ]);

            // Verify order belongs to user
            $order = Order::where('id', $validated['order_id'])
                         ->where('user_id', $user->id)
                         ->firstOrFail();

            // Store image
            $imagePath = $request->file('proof_image')->store('payment-proofs', 'public');

            // Create or update payment record
            $payment = Payment::updateOrCreate(
                ['order_id' => $validated['order_id']],
                [
                    'user_id' => $user->id,
                    'order_id' => $validated['order_id'],
                    'proof_image' => $imagePath,
                    'status' => 'pending_verification',
                    'uploaded_at' => now(),
                ]
            );

            // Update order status
            $order->update([
                'payment_status' => 'pending_verification',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment proof uploaded successfully',
                'data' => $payment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload payment proof: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment status
     */
    public function getStatus($orderId)
    {
        try {
            $user = auth()->user();

            $order = Order::where('id', $orderId)
                         ->where('user_id', $user->id)
                         ->firstOrFail();

            $payment = Payment::where('order_id', $orderId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Payment status retrieved successfully',
                'data' => [
                    'order_id' => $order->id,
                    'payment_status' => $order->payment_status,
                    'payment' => $payment,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Verify payment (ADMIN ONLY)
     */
    public function verify(Request $request)
    {
        try {
            // In production, check if user is admin
            // if (!auth()->user()->is_admin) {
            //     return response()->json([...], 403);
            // }

            $validated = $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
                'status' => 'required|in:verified,rejected',
            ]);

            $order = Order::findOrFail($validated['order_id']);
            $payment = Payment::where('order_id', $validated['order_id'])->first();

            if ($validated['status'] === 'verified') {
                $order->update(['payment_status' => 'verified']);
                if ($payment) {
                    $payment->update(['status' => 'verified']);
                }
                $message = 'Payment verified successfully';
            } else {
                $order->update(['payment_status' => 'rejected']);
                if ($payment) {
                    $payment->update(['status' => 'rejected']);
                }
                $message = 'Payment rejected';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
