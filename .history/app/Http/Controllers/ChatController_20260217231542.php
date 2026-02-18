<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Show user's chat list
     */
    public function index()
    {
        $chats = Chat::where('user_id', auth()->id())
            ->with(['messages' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('chats.index', compact('chats'));
    }

    /**
     * Show specific chat
     */
    public function show(Chat $chat)
    {
        // Check if user owns this chat
        if ($chat->user_id !== auth()->id()) {
            abort(403);
        }

        // Mark messages as read
        $chat->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $chat->messages()->get();

        return view('chats.show', compact('chat', 'messages'));
    }

    /**
     * Create a new chat
     */
    public function create()
    {
        return view('chats.create');
    }

    /**
     * Store new chat
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $chat = Chat::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'user_email' => auth()->user()->email,
            'user_phone' => auth()->user()->phone ?? '',
            'subject' => $validated['subject'],
            'status' => 'open',
        ]);

        // Store first message
        $messageData = [
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'sender_type' => 'user',
            'message' => $validated['message'],
        ];

        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $cloudinary = new CloudinaryService();
                $storedPath = null;
                
                // Try Cloudinary first (persistent storage)
                if ($cloudinary->isEnabled()) {
                    $result = $cloudinary->uploadFile($image, 'chats');
                    if ($result) {
                        $storedPath = $result['url'];
                        \Log::info('Chat image uploaded to Cloudinary', [
                            'url' => $storedPath,
                            'chat_id' => $chat->id,
                        ]);
                    }
                }
                
                // Fallback to local storage
                if (!$storedPath) {
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $dir = storage_path('app/public/chats');
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $image->move($dir, $filename);
                    $storedPath = route('chat.image', ['folder' => 'chats', 'filename' => $filename]);
                    \Log::info('Chat image uploaded to local storage', [
                        'url' => $storedPath,
                        'chat_id' => $chat->id,
                    ]);
                }
                
                $messageData['image_path'] = $storedPath;
            } catch (\Exception $e) {
                \Log::error('Chat image upload failed on chat creation', [
                    'error' => $e->getMessage(),
                    'chat_id' => $chat->id,
                    'user_id' => auth()->id(),
                ]);
                // Still create chat even if image upload fails
            }
        }

        ChatMessage::create($messageData);

        return redirect()->route('chats.show', $chat)->with('success', 'Chat created successfully!');
    }

    /**
     * Send message in chat
     */
    public function sendMessage(Request $request, Chat $chat)
    {
        // Check if user owns this chat
        if ($chat->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
        ]);

        // Require either message or image
        if (empty($validated['message']) && !$request->hasFile('image')) {
            return back()->withErrors(['message' => 'Please provide either a message or an image']);
        }

        $messageData = [
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'sender_type' => 'user',
            'message' => $validated['message'] ?? '',
        ];

        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $cloudinary = new CloudinaryService();
                $storedPath = null;
                
                // Try Cloudinary first (persistent storage)
                if ($cloudinary->isEnabled()) {
                    $result = $cloudinary->uploadFile($image, 'chats');
                    if ($result) {
                        $storedPath = $result['url'];
                        \Log::info('Chat image uploaded to Cloudinary', [
                            'url' => $storedPath,
                            'chat_id' => $chat->id,
                        ]);
                    }
                }
                
                // Fallback to local storage
                if (!$storedPath) {
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $dir = storage_path('app/public/chats');
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $image->move($dir, $filename);
                    $storedPath = route('chat.image', ['folder' => 'chats', 'filename' => $filename]);
                    \Log::info('Chat image uploaded to local storage', [
                        'url' => $storedPath,
                        'chat_id' => $chat->id,
                    ]);
                }
                
                $messageData['image_path'] = $storedPath;
            } catch (\Exception $e) {
                \Log::error('Chat image upload failed', [
                    'error' => $e->getMessage(),
                    'chat_id' => $chat->id,
                    'user_id' => auth()->id(),
                ]);
                return back()->withErrors(['image' => 'Failed to upload image: ' . $e->getMessage()]);
            }
        }

        ChatMessage::create($messageData);
        $chat->update(['updated_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('chats.show', $chat);
    }

    /**
     * Close chat
     */
    public function close(Chat $chat)
    {
        if ($chat->user_id !== auth()->id()) {
            abort(403);
        }

        $chat->update(['status' => 'closed']);

        return redirect()->route('chats.index')->with('success', 'Chat closed successfully!');
    }

    /**
     * Respond to price quote
     */
    public function respondToQuote(Request $request, Chat $chat)
    {
        if ($chat->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'response' => 'required|in:accepted,declined',
            'quote_message_id' => 'required|exists:chat_messages,id',
        ]);

        $response = $validated['response'];
        $emoji = $response === 'accepted' ? '✅' : '❌';
        $action = $response === 'accepted' ? 'accepted' : 'declined';
        
        $message = "{$emoji} Customer {$action} the price quote.\n\n" . 
                   "Customer will " . ($response === 'accepted' ? 'proceed with the custom order.' : 'not proceed with this quote.');

        // Create response message
        $responseMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'sender_type' => 'user',
            'message' => $message,
        ]);

        // If accepted, create order (Pending Payment status)
        if ($response === 'accepted') {
            $quoteMessage = ChatMessage::find($validated['quote_message_id']);
            
            // Extract quoted price from message
            $quotedPrice = 0;
            if (preg_match('/Total:\s*₱?([\d,]+\.?\d*)/i', $quoteMessage->message, $matches)) {
                $quotedPrice = floatval(str_replace(',', '', $matches[1]));
            }
            
            // Get user's default address for shipping calculation
            $userAddress = \App\Models\UserAddress::where('user_id', auth()->id())
                ->where('is_default', true)
                ->first();
            
            // Calculate shipping fee (same logic as chat view)
            $shippingFee = 0;
            if ($userAddress) {
                $cityLower = strtolower($userAddress->city ?? '');
                $regionLower = strtolower($userAddress->province ?? '');
                $postalCode = $userAddress->postal_code ?? '';
                
                if (str_contains($cityLower, 'zamboanga') && str_starts_with($postalCode, '7')) {
                    $shippingFee = 0;
                } elseif (str_contains($regionLower, 'zamboanga') || in_array($cityLower, ['isabela', 'dipolog', 'dapitan', 'pagadian'])) {
                    $shippingFee = 80;
                } elseif (in_array($cityLower, ['basilan', 'sulu', 'tawi-tawi', 'cotabato', 'maguindanao']) || str_contains($regionLower, 'barmm') || str_contains($regionLower, 'armm')) {
                    $shippingFee = 120;
                } elseif (str_contains($regionLower, 'mindanao') || in_array($cityLower, ['davao', 'cagayan de oro', 'iligan', 'general santos', 'butuan', 'koronadal'])) {
                    $shippingFee = 150;
                } elseif (str_contains($regionLower, 'visayas') || in_array($cityLower, ['cebu', 'iloilo', 'bacolod', 'tacloban', 'dumaguete', 'tagbilaran', 'ormoc'])) {
                    $shippingFee = 180;
                } elseif (str_contains($cityLower, 'manila') || str_contains($regionLower, 'ncr') || in_array($cityLower, ['quezon city', 'makati', 'pasig', 'taguig', 'caloocan', 'cavite', 'laguna', 'bulacan', 'rizal', 'pampanga'])) {
                    $shippingFee = 220;
                } elseif (str_contains($regionLower, 'luzon') || in_array($cityLower, ['baguio', 'tuguegarao', 'laoag', 'santiago', 'vigan'])) {
                    $shippingFee = 250;
                } else {
                    $shippingFee = 280;
                }
            }
            
            $totalAmount = $quotedPrice + $shippingFee;
            
            // Generate unique order reference
            $orderRef = 'CHAT-' . strtoupper(substr(md5(uniqid()), 0, 8));
            
            // Get formatted address
            $formattedAddress = $userAddress ? $userAddress->formatted_address : 'Address not provided';
            
            // Get design images from quote message (only if column exists)
            $designImages = [];
            try {
                if (method_exists($quoteMessage, 'getAttribute')) {
                    $designImages = $quoteMessage->reference_images ?? [];
                }
            } catch (\Exception $e) {
                \Log::warning('reference_images column might not exist yet', ['error' => $e->getMessage()]);
            }
            
            $orderNotes = 'Custom order from chat ID: ' . $chat->id;
            
            if (!empty($designImages)) {
                $orderNotes .= "\n\nDesign References:\n";
                foreach ($designImages as $index => $imageUrl) {
                    $orderNotes .= "- Image " . ($index + 1) . ": " . $imageUrl . "\n";
                }
            }
            
            // Create custom order in custom_orders table
            try {
                $order = \App\Models\CustomOrder::create([
                    'user_id' => auth()->id(),
                    'chat_id' => $chat->id,
                    'specifications' => $orderNotes,
                    'estimated_price' => $quotedPrice,
                    'final_price' => $totalAmount,
                    'delivery_address' => $formattedAddress,
                    'delivery_type' => 'deliver',
                    'phone' => $userAddress ? ($userAddress->phone_number ?? (auth()->user()->phone ?? '')) : (auth()->user()->phone ?? ''),
                    'email' => auth()->user()->email,
                    'payment_status' => 'pending',
                    'status' => 'price_quoted',
                    'additional_notes' => 'Shipping Fee: ₱' . number_format($shippingFee, 2) . "\nQuoted Price: ₱" . number_format($quotedPrice, 2) . "\nTotal: ₱" . number_format($totalAmount, 2),
                    'design_upload' => !empty($designImages) ? (is_array($designImages) ? implode(',', $designImages) : $designImages) : null,
                ]);
                
                \Log::info('Chat custom order created successfully', [
                    'custom_order_id' => $order->id,
                    'chat_id' => $chat->id,
                    'user_id' => auth()->id()
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create chat custom order', [
                    'error' => $e->getMessage(),
                    'chat_id' => $chat->id,
                    'user_id' => auth()->id(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Show the actual error to user for debugging
                return redirect()->route('chats.show', $chat)->with('error', 'Database Error: ' . $e->getMessage());
            }
            
            $chat->update(['updated_at' => now()]);
        } else {
            $chat->update(['updated_at' => now()]);
        }

        return redirect()->route('chats.show', $chat)->with('success', 'Response sent to admin!');
    }
    
    /**
     * Set payment method for chat order
     */
    public function setPaymentMethod(Request $request, $orderId)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:gcash,bank_transfer',
        ]);
        
        $order = \App\Models\Order::findOrFail($orderId);
        
        // Verify ownership
        if ($order->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        // Update payment method
        $order->update([
            'payment_method' => $validated['payment_method'],
            'status' => 'confirmed' // Change status to show payment details instead of buttons
        ]);
        
        return response()->json(['success' => true, 'message' => 'Payment method set successfully!']);
    }
    
    /**
     * Upload payment receipt for chat order
     */
    public function uploadReceipt(Request $request, $orderId)
    {
        $validated = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,jpg,png|max:5120', // 5MB max
        ]);
        
        $order = \App\Models\Order::findOrFail($orderId);
        
        // Verify ownership
        if ($order->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }
        
        // Verify order has payment method set
        if (!$order->payment_method) {
            return redirect()->back()->with('error', 'Please select a payment method first.');
        }
        
        // Handle file upload
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            
            // Upload to Cloudinary for persistent storage
            $cloudinary = new CloudinaryService();
            $storedPath = null;
            
            if ($cloudinary->isEnabled()) {
                $result = $cloudinary->uploadFile($file, 'receipts');
                if ($result) {
                    $storedPath = $result['url'];
                    \Log::info('Payment receipt uploaded to Cloudinary', [
                        'url' => $storedPath,
                        'order_ref' => $order->order_ref,
                    ]);
                }
            }
            
            // Fallback to local storage if Cloudinary fails
            if (!$storedPath) {
                $filename = 'receipt_' . $order->order_ref . '_' . time() . '.' . $file->getClientOriginalExtension();
                $storedPath = $file->storeAs('receipts', $filename, 'public');
                \Log::warning('Payment receipt uploaded to local storage (will be lost on redeploy)', [
                    'path' => $storedPath,
                    'order_ref' => $order->order_ref,
                ]);
            }
            
            // Update order - store in the correct receipt field based on payment method
            $updateData = [
                'payment_proof_path' => $storedPath,
                'payment_status' => 'paid',
                'status' => 'processing',
            ];
            
            // Also save to the specific receipt field so admin view displays correctly
            if ($order->payment_method === 'gcash') {
                $updateData['gcash_receipt'] = $storedPath;
            } elseif ($order->payment_method === 'bank_transfer') {
                $updateData['bank_receipt'] = $storedPath;
            }
            
            $order->update($updateData);
            
            // Create admin notification message in chat
            $chat = \App\Models\Chat::where('id', (int) str_replace('chat ID: ', '', $order->customer_notes))->first();
            if ($chat) {
                \App\Models\ChatMessage::create([
                    'chat_id' => $chat->id,
                    'sender_id' => auth()->id(),
                    'sender_type' => 'user',
                    'message' => '✅ Payment proof submitted for Order ' . $order->order_ref . '. Awaiting admin verification.'
                ]);
            }
            
            return redirect()->back()->with('success', 'Payment proof uploaded successfully! We will verify your payment shortly.');
        }
        
        return redirect()->back()->with('error', 'Failed to upload payment proof.');
    }
}
