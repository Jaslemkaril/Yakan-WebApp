<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatPayment;
use App\Models\ChatMessage;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatPaymentController extends Controller
{
    /**
     * Admin sends payment request to user in chat
     */
    public function sendPaymentRequest(Chat $chat, Request $request)
    {
        // Admin is verified by middleware
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Create a pending chat payment
        $payment = ChatPayment::create([
            'chat_id' => $chat->id,
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        // Store payment request as system message
        ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => null,
            'sender_type' => 'admin',
            'message' => "Payment request sent: ₱" . number_format($validated['amount'], 2) . "\n\nPayment ID: #" . $payment->id,
        ]);

        return redirect()->back()->with('success', 'Payment request sent to customer');
    }

    /**
     * User submits payment proof
     */
    public function submitPaymentProof(Chat $chat, Request $request)
    {
        // Check if user owns this chat
        if ($chat->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_id' => 'required|exists:chat_payments,id',
            'payment_method' => 'required|in:online_banking,bank_transfer',
            'payment_proof' => 'required|image|max:5120',
        ]);

        $payment = ChatPayment::findOrFail($validated['payment_id']);

        // Verify payment belongs to this chat
        if ($payment->chat_id !== $chat->id) {
            abort(403);
        }

        // Store payment proof
        if ($request->hasFile('payment_proof')) {
            try {
                $file = $request->file('payment_proof');
                $filename = 'payment_proof_' . $payment->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Ensure directory exists
                $dir = storage_path('app/public/payments');
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                // Store file directly in storage folder
                $file->move($dir, $filename);
                
                // Build the URL path using dedicated chat-image route
                $imageUrl = route('chat.image', ['folder' => 'payments', 'filename' => $filename]);
                
                $payment->update([
                    'payment_proof' => $imageUrl,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'paid',
                ]);

                // Add message to chat
                ChatMessage::create([
                    'chat_id' => $chat->id,
                    'user_id' => Auth::id(),
                    'sender_type' => 'user',
                    'message' => "Payment proof uploaded for Payment ID: #" . $payment->id . "\n\nPayment Method: " . ucfirst(str_replace('_', ' ', $validated['payment_method'])),
                    'image_path' => $imageUrl,
                ]);
                
                \Log::info('Payment proof uploaded', [
                    'filename' => $filename,
                    'url' => $imageUrl,
                    'payment_id' => $payment->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Chat payment proof upload failed', [
                    'error' => $e->getMessage(),
                    'chat_id' => $chat->id,
                    'payment_id' => $payment->id,
                    'user_id' => Auth::id(),
                ]);
                return back()->withErrors(['payment_proof' => 'Failed to upload payment proof: ' . $e->getMessage()]);
            }
        }

        return redirect()->back()->with('success', 'Payment proof submitted. Waiting for admin verification.');
    }

    /**
     * Admin verifies payment and auto-creates custom order
     */
    public function verifyPayment(ChatPayment $payment, Request $request)
    {
        // Admin is verified by middleware
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['action'] === 'approve') {
            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'admin_notes' => $validated['notes'],
            ]);

            // Auto-create custom order from chat
            $this->autoCreateCustomOrder($payment);

            // Add verification message to chat
            ChatMessage::create([
                'chat_id' => $payment->chat_id,
                'user_id' => null,
                'sender_type' => 'admin',
                'message' => "✅ Payment verified!\n\nYour order has been created and is now in our system. You can track it in the Custom Orders section.\n\n" . ($validated['notes'] ? "Admin Notes: " . $validated['notes'] : ''),
            ]);

            return redirect()->back()->with('success', 'Payment verified and custom order created');
        } else {
            // Reject payment
            $payment->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $validated['notes'],
            ]);

            // Add rejection message to chat
            ChatMessage::create([
                'chat_id' => $payment->chat_id,
                'user_id' => null,
                'sender_type' => 'admin',
                'message' => "❌ Payment verification failed.\n\nReason: " . ($validated['notes'] ?? 'Payment proof does not match requirements.') . "\n\nPlease resubmit with valid payment proof.",
            ]);

            return redirect()->back()->with('warning', 'Payment rejected');
        }
    }

    /**
     * Auto-create custom order from verified payment
     */
    private function autoCreateCustomOrder(ChatPayment $payment)
    {
        $chat = $payment->chat;

        // Get latest admin message with design/pattern info
        $designMessage = $chat->messages()
            ->where('sender_type', 'admin')
            ->orderBy('created_at', 'desc')
            ->first();

        $imageDesign = null;
        if ($chat->messages()->where('sender_type', 'user')->where('image_path', '!=', null)->exists()) {
            $imageDesign = $chat->messages()
                ->where('sender_type', 'user')
                ->where('image_path', '!=', null)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // Create custom order linked to chat and payment
        $customOrder = CustomOrder::create([
            'chat_id' => $chat->id,
            'user_id' => $chat->user_id,
            'design_upload' => $imageDesign ? $imageDesign->image_path : null,
            'specifications' => $chat->subject,
            'status' => 'pending',
            'payment_status' => 'paid',
            'estimated_price' => $payment->amount,
            'final_price' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'transaction_id' => 'CHAT_' . $payment->id . '_' . time(),
            'payment_receipt' => $payment->payment_proof,
            'payment_notes' => 'Payment received and verified from chat negotiation',
            'additional_notes' => $designMessage ? $designMessage->message : 'Created from chat payment',
            'quantity' => 1, // Default quantity
            'delivery_type' => 'delivery',
            'delivery_address' => $chat->user->address ?? '',
        ]);

        // Update payment with custom order reference
        $payment->update(['custom_order_id' => $customOrder->id]);

        return $customOrder;
    }
}
