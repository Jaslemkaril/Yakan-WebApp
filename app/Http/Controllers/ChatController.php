<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\CustomOrder;
use App\Models\UserAddress;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    private function calculateCanonicalShippingFeeFromAddress(?UserAddress $address): float
    {
        if (!$address) {
            return 0.0;
        }

        $cityLower = strtolower((string) ($address->city ?? ''));
        $regionLower = strtolower((string) ($address->province ?? ($address->region ?? '')));
        $addrLower = strtolower(implode(' ', array_filter([
            $address->street_name ?? null,
            $address->barangay ?? null,
            $address->city ?? null,
            $address->province ?? ($address->region ?? null),
        ])));
        $haystack = trim($addrLower . ' ' . $cityLower . ' ' . $regionLower);

        if (
            str_contains($haystack, 'zamboanga') ||
            str_contains($regionLower, 'barmm') ||
            str_contains($regionLower, 'bangsamoro') ||
            in_array($cityLower, ['dipolog city', 'dapitan city', 'pagadian city', 'isabela city', 'jolo', 'bongao', 'cotabato city', 'marawi city', 'lamitan'])
        ) {
            return 100.0;
        }

        if (
            str_contains($haystack, 'mindanao') ||
            str_contains($haystack, 'davao') ||
            str_contains($haystack, 'soccsksargen') ||
            str_contains($haystack, 'caraga') ||
            str_contains($haystack, 'northern mindanao') ||
            str_contains($haystack, 'cagayan de oro') ||
            str_contains($haystack, 'general santos')
        ) {
            return 180.0;
        }

        if (
            str_contains($haystack, 'visayas') ||
            str_contains($haystack, 'cebu') ||
            str_contains($haystack, 'iloilo') ||
            str_contains($haystack, 'bacolod') ||
            str_contains($haystack, 'tacloban') ||
            str_contains($haystack, 'leyte')
        ) {
            return 250.0;
        }

        if (
            str_contains($haystack, 'ncr') ||
            str_contains($haystack, 'metro manila') ||
            str_contains($haystack, 'manila') ||
            str_contains($haystack, 'calabarzon') ||
            str_contains($haystack, 'central luzon')
        ) {
            return 300.0;
        }

        return 350.0;
    }

    /**
     * Helper to build redirect URL with auth_token if present
     */
    private function redirectWithToken($route, $parameters = [])
    {
        $url = route($route, $parameters);
        $token = request()->input('auth_token') ?? request()->query('auth_token') ?? session('auth_token');
        
        if ($token) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . 'auth_token=' . urlencode($token);
        }
        
        return redirect($url);
    }

    /**
     * Show user's chat list
     */
    public function index()
    {
        \Log::info('ChatController index: Starting', [
            'auth_check' => auth()->check(),
            'user_id' => auth()->id(),
            'has_token' => request()->has('auth_token'),
            'token' => request()->get('auth_token') ? substr(request()->get('auth_token'), 0, 10) . '...' : 'none'
        ]);
        
        try {
            if (!auth()->check()) {
                \Log::error('ChatController index: User not authenticated, redirecting to login');
                $url = route('login.user.form');
                $token = request()->input('auth_token') ?? request()->query('auth_token') ?? session('auth_token');
                if ($token) {
                    $url .= '?auth_token=' . urlencode($token);
                }
                return redirect($url)->with('error', 'Please log in to view your chats.');
            }
            
            \Log::info('ChatController index: User authenticated, loading chats', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email
            ]);
            
            $chats = Chat::where('user_id', auth()->id())
                ->with(['messages' => function($query) {
                    $query->latest('created_at')->limit(1);
                }])
                ->orderBy('updated_at', 'desc')
                ->paginate(10);

            \Log::info('ChatController index: Chats loaded successfully', ['count' => $chats->count()]);

            return view('chats.index', compact('chats'));
        } catch (\Throwable $e) {
            \Log::error('Chat index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'none',
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Always show detailed error for debugging
            return response(
                "<html><head><title>Chat Error</title></head><body>" .
                "<h2 style='color:red'>Chat Page Error</h2>" .
                "<div style='background:#f5f5f5;padding:20px;border-radius:8px;margin:20px;font-family:monospace'>" .
                "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>" .
                "<strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "<br><br>" .
                "<strong>Auth Status:</strong> " . (auth()->check() ? 'Authenticated (User ID: ' . auth()->id() . ')' : 'Not Authenticated') . "<br><br>" .
                "<strong>Token Present:</strong> " . (request()->has('auth_token') ? 'Yes' : 'No') . "<br><br>" .
                "<strong>Stack Trace:</strong><pre style='white-space:pre-wrap'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>" .
                "</div>" .
                "<a href='/dashboard?auth_token=" . request()->get('auth_token', '') . "' style='margin:20px;display:inline-block;padding:10px 20px;background:#800000;color:white;text-decoration:none;border-radius:5px'>Back to Dashboard</a>" .
                "</body></html>",
                500
            );
        }
    }

    /**
     * Show specific chat
     */
    public function show(Chat $chat)
    {
        try {
            \Log::info('ChatController show: Starting', [
                'chat_id' => $chat->id,
                'auth_check' => auth()->check(),
                'user_id' => auth()->id(),
                'chat_user_id' => $chat->user_id
            ]);
            
            // Check if user owns this chat
            if ($chat->user_id !== auth()->id()) {
                \Log::warning('ChatController show: Unauthorized access attempt', [
                    'chat_id' => $chat->id,
                    'auth_user_id' => auth()->id(),
                    'chat_user_id' => $chat->user_id
                ]);
                abort(403);
            }

            // Mark messages as read
            $chat->messages()
                ->where('sender_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messages = $chat->messages()->get();

            // Keep pending chat-order totals aligned with canonical shipping zones.
            $userDefaultAddress = UserAddress::where('user_id', auth()->id())
                ->where('is_default', true)
                ->first();

            if ($userDefaultAddress) {
                $shippingFee = $this->calculateCanonicalShippingFeeFromAddress($userDefaultAddress);
                $pendingOrders = CustomOrder::where('chat_id', $chat->id)
                    ->where('user_id', auth()->id())
                    ->where('payment_status', 'pending')
                    ->get();

                foreach ($pendingOrders as $pendingOrder) {
                    $estimatedPrice = (float) ($pendingOrder->estimated_price ?? 0);
                    $expectedTotal = $estimatedPrice + $shippingFee;
                    $currentShipping = (float) ($pendingOrder->shipping_fee ?? 0);
                    $currentTotal = (float) ($pendingOrder->final_price ?? 0);

                    if (abs($currentShipping - $shippingFee) > 0.009 || abs($currentTotal - $expectedTotal) > 0.009) {
                        $pendingOrder->update([
                            'shipping_fee' => $shippingFee,
                            'final_price' => $expectedTotal,
                            'delivery_city' => $userDefaultAddress->city,
                            'delivery_province' => ($userDefaultAddress->province ?? $userDefaultAddress->region),
                        ]);
                    }
                }
            }

            \Log::info('ChatController show: Messages loaded', ['count' => $messages->count()]);

            return view('chats.show', compact('chat', 'messages'));
        } catch (\Throwable $e) {
            \Log::error('Chat show error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'chat_id' => $chat->id ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            
            if (config('app.debug')) {
                return response(
                    "<h2 style='color:red'>Chat View Error</h2>" .
                    "<pre style='background:#f5f5f5;padding:15px;border-radius:8px;overflow:auto'>" .
                    htmlspecialchars($e->getMessage()) . "\n\n" .
                    "File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "\n\n" .
                    htmlspecialchars($e->getTraceAsString()) . "</pre>",
                    500
                );
            }
            
            return $this->redirectWithToken('chats.index')
                ->with('error', 'Unable to load chat. Please try again.');
        }
    }

    /**
     * Create a new chat
     */
    public function create()
    {
        try {
            $html = view('chats.create')->render();
            return response($html);
        } catch (\Throwable $e) {
            \Log::error('Chat create error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response(
                "<h2 style='color:red'>Chat Create Error</h2>" .
                "<pre style='background:#f5f5f5;padding:15px;border-radius:8px;overflow:auto'>" .
                htmlspecialchars($e->getMessage()) . "\n\n" .
                "File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "\n\n" .
                htmlspecialchars($e->getTraceAsString()) . "</pre>",
                500
            );
        }
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

        if ($request->expectsJson() || $request->ajax()) {
            $redirectUrl = $this->redirectWithToken('chats.show', $chat)->getTargetUrl();
            return response()->json([
                'success' => true,
                'message' => 'Chat created successfully!',
                'chat_id' => $chat->id,
                'redirect_url' => $redirectUrl
            ]);
        }

        return $this->redirectWithToken('chats.show', $chat)->with('success', 'Chat created successfully!');
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
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide either a message or an image'
                ], 400);
            }
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
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload image: ' . $e->getMessage()
                    ], 500);
                }
                return back()->withErrors(['image' => 'Failed to upload image: ' . $e->getMessage()]);
            }
        }

        $newMessage = ChatMessage::create($messageData);
        $chat->update(['updated_at' => now()]);

        if ($request->expectsJson() || $request->ajax()) {
            // Format image_path to proper URL for JavaScript
            $imageUrl = null;
            if ($newMessage->image_path) {
                $imagePath = $newMessage->image_path;
                if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
                    // Already a full URL
                    $imageUrl = $imagePath;
                } elseif (str_starts_with($imagePath, 'data:image')) {
                    // Base64 data URL
                    $imageUrl = $imagePath;
                } elseif (str_starts_with($imagePath, 'storage/')) {
                    // Storage path
                    $imageUrl = asset($imagePath);
                } else {
                    // Default fallback - add storage prefix
                    $imageUrl = asset('storage/' . $imagePath);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $newMessage->id,
                    'message' => $newMessage->message,
                    'image_path' => $imageUrl,
                    'sender_type' => $newMessage->sender_type,
                    'created_at' => $newMessage->created_at->toISOString(),
                ]
            ]);
        }

        return $this->redirectWithToken('chats.show', $chat);
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

        return $this->redirectWithToken('chats.index')->with('success', 'Chat closed successfully!');
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
            'delivery_type' => 'nullable|in:delivery,pickup',
        ]);

        $quoteMessage = ChatMessage::where('id', $validated['quote_message_id'])
            ->where('chat_id', $chat->id)
            ->where('sender_type', 'admin')
            ->first();

        if (!$quoteMessage || !str_contains(strtoupper((string) $quoteMessage->message), 'PRICE QUOTE')) {
            return $this->redirectWithToken('chats.show', $chat)
                ->with('error', 'The selected quote is invalid.');
        }

        $selectedQuoteData = is_array($quoteMessage->form_data) ? $quoteMessage->form_data : [];
        $selectedQuoteStatus = strtolower((string) ($selectedQuoteData['quote_status'] ?? 'active'));

        if (in_array($selectedQuoteStatus, ['void', 'closed'], true)) {
            return $this->redirectWithToken('chats.show', $chat)
                ->with('error', 'This quote is already closed/void. Please review the latest quote from support.');
        }

        $latestActiveQuote = ChatMessage::where('chat_id', $chat->id)
            ->where('sender_type', 'admin')
            ->where('message', 'like', '%PRICE QUOTE%')
            ->orderByDesc('created_at')
            ->get()
            ->first(function ($msg) {
                $meta = is_array($msg->form_data) ? $msg->form_data : [];
                $status = strtolower((string) ($meta['quote_status'] ?? 'active'));
                return !in_array($status, ['void', 'closed', 'accepted', 'declined'], true);
            });

        if ($latestActiveQuote && (int) $latestActiveQuote->id !== (int) $quoteMessage->id) {
            return $this->redirectWithToken('chats.show', $chat)
                ->with('error', 'This quote has been replaced. Please respond to the latest quote.');
        }

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

        // Persist status on the quote itself for auditability and UI state.
        $quoteMeta = is_array($quoteMessage->form_data) ? $quoteMessage->form_data : [];
        $quoteMeta['quote_status'] = $response;
        $quoteMeta['responded_at'] = now()->toISOString();
        $quoteMeta['response_message_id'] = $responseMessage->id;
        $quoteMeta['responded_by_user_id'] = auth()->id();
        $quoteMessage->form_data = $quoteMeta;
        $quoteMessage->save();

        // If accepted, create order (Pending Payment status)
        if ($response === 'accepted') {
            $selectedDeliveryType = strtolower((string) ($validated['delivery_type'] ?? 'delivery'));
            if (!in_array($selectedDeliveryType, ['delivery', 'pickup'], true)) {
                $selectedDeliveryType = 'delivery';
            }
            
            // Extract quoted price from message
            $quotedPrice = 0;
            if (preg_match('/Total:\s*₱?([\d,]+\.?\d*)/i', $quoteMessage->message, $matches)) {
                $quotedPrice = floatval(str_replace(',', '', $matches[1]));
            }
            
            // Get user's default address for shipping calculation
            $userAddress = UserAddress::where('user_id', auth()->id())
                ->where('is_default', true)
                ->first();

            if ($selectedDeliveryType === 'delivery' && !$userAddress) {
                return $this->redirectWithToken('chats.show', $chat)
                    ->with('error', 'Please add a delivery address first, or choose Pick up.');
            }

            $shippingFee = $selectedDeliveryType === 'pickup'
                ? 0.0
                : $this->calculateCanonicalShippingFeeFromAddress($userAddress);
            
            $totalAmount = $quotedPrice + $shippingFee;
            
            // Get formatted address
            $formattedAddress = $selectedDeliveryType === 'pickup'
                ? 'Tuwas Yakan Weaving Center, Yakan Village, Upper Calarian, Labuan-Limpapa Road, National Road, Zamboanga City, Philippines 7000'
                : ($userAddress ? $userAddress->formatted_address : 'Address not provided');
            
            // Get design images from quote message (only if column exists)
            $designImages = [];
            try {
                if (method_exists($quoteMessage, 'getAttribute')) {
                    $designImages = $quoteMessage->reference_images ?? [];
                }
            } catch (\Exception $e) {
                \Log::warning('reference_images column might not exist yet', ['error' => $e->getMessage()]);
            }
            
            // Get form response data from chat messages
            $formResponseMessage = ChatMessage::where('chat_id', $chat->id)
                ->where('sender_type', 'user')
                ->where('message_type', 'form_response')
                ->latest()
                ->first();
            
            $formData = [];
            $orderName = null;
            $quantityMeters = 1;
            $fabricType = null;
            $customerNotes = null;
            
            if ($formResponseMessage && !empty($formResponseMessage->form_data['responses'])) {
                $formData = $formResponseMessage->form_data['responses'];
                $orderName = $formData['order_name'] ?? null;
                $quantityMeters = $formData['meters'] ?? ($formData['quantity_meters'] ?? 1);
                $fabricType = $formData['fabric_type'] ?? null;
                $customerNotes = $formData['additional_notes'] ?? null;
            }
            
            // Build detailed specifications
            $orderNotes = 'Custom order from chat ID: ' . $chat->id;
            
            if ($orderName) {
                $orderNotes .= "\n\nOrder Name: " . $orderName;
            }
            
            if ($quantityMeters) {
                $orderNotes .= "\nQuantity: " . $quantityMeters . " meters";
            }
            
            if ($fabricType) {
                $orderNotes .= "\nFabric Type: " . $fabricType;
            }
            
            if (!empty($designImages)) {
                $orderNotes .= "\n\nDesign References:\n";
                foreach ($designImages as $index => $imageUrl) {
                    $orderNotes .= "- Image " . ($index + 1) . ": " . $imageUrl . "\n";
                }
            }
            
            // Create custom order in custom_orders table
            try {
                $order = CustomOrder::create([
                    'user_id' => auth()->id(),
                    'chat_id' => $chat->id,
                    'specifications' => $orderNotes,
                    'quantity' => (int)$quantityMeters ?: 1,
                    'product_type' => $fabricType,
                    'estimated_price' => $quotedPrice,
                    'final_price' => $totalAmount,
                    'shipping_fee' => $shippingFee,
                    'delivery_address' => $formattedAddress,
                    'delivery_city' => $selectedDeliveryType === 'pickup' ? null : ($userAddress->city ?? null),
                    'delivery_province' => $selectedDeliveryType === 'pickup' ? null : ($userAddress->province ?? $userAddress->region ?? null),
                    'delivery_type' => $selectedDeliveryType,
                    'phone' => $userAddress ? ($userAddress->phone_number ?? (auth()->user()->phone ?? '')) : (auth()->user()->phone ?? ''),
                    'email' => auth()->user()->email,
                    'payment_status' => 'pending',
                    'status' => 'approved',
                    'approved_at' => now(),
                    'additional_notes' => ($customerNotes ? "Customer Notes: " . $customerNotes . "\n\n" : '') . 'Delivery Type: ' . ucfirst($selectedDeliveryType) . "\nShipping Fee: ₱" . number_format($shippingFee, 2) . "\nQuoted Price: ₱" . number_format($quotedPrice, 2) . "\nTotal: ₱" . number_format($totalAmount, 2),
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
                return $this->redirectWithToken('chats.show', $chat)->with('error', 'Database Error: ' . $e->getMessage());
            }
            
            $chat->update(['updated_at' => now()]);
        } else {
            $chat->update(['updated_at' => now()]);
        }

        return $this->redirectWithToken('chats.show', $chat)->with('success', 'Response sent to admin!');
    }
    
    /**
     * Set payment method for chat order
     */
    public function setPaymentMethod(Request $request, $orderId)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:maya,bank_transfer',
        ]);
        
        $order = \App\Models\CustomOrder::findOrFail($orderId);
        
        // Verify ownership
        if ($order->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        // Update payment method
        $order->update([
            'payment_method' => $validated['payment_method'],
            'status' => 'approved' // Change status to show payment details instead of buttons
        ]);
        
        return response()->json(['success' => true, 'message' => 'Payment method set successfully!']);
    }
    
    /**
     * Upload payment receipt for chat order
     */
    public function uploadReceipt(Request $request, $orderId)
    {
        // Extract auth_token for redirect preservation
        $authToken = $request->input('auth_token') ?? $request->query('auth_token');
        $redirectUrl = $authToken ? url()->previous() . (strpos(url()->previous(), '?') !== false ? '&' : '?') . 'auth_token=' . urlencode($authToken) : url()->previous();
        
        $validated = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,jpg,png|max:5120', // 5MB max
        ]);
        
        $order = \App\Models\CustomOrder::findOrFail($orderId);
        
        // Verify ownership
        if ($order->user_id !== auth()->id()) {
            return redirect($redirectUrl)->with('error', 'Unauthorized access.');
        }
        
        // Verify order has payment method set
        if (!$order->payment_method) {
            return redirect($redirectUrl)->with('error', 'Please select a payment method first.');
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
                        'custom_order_id' => $order->id,
                    ]);
                }
            }
            
            // Fallback to local storage if Cloudinary fails
            if (!$storedPath) {
                $filename = 'receipt_custom_order_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $storedPath = $file->storeAs('receipts', $filename, 'public');
                \Log::warning('Payment receipt uploaded to local storage (will be lost on redeploy)', [
                    'path' => $storedPath,
                    'custom_order_id' => $order->id,
                ]);
            }
            
            // Update custom order - store receipt
            $updateData = [
                'payment_receipt' => $storedPath,
                'payment_status' => 'paid',
                'status' => 'processing',
            ];
            
            $order->update($updateData);
            
            // Create admin notification message in chat
            $chat = $order->chat_id ? \App\Models\Chat::find($order->chat_id) : null;
            if ($chat) {
                \App\Models\ChatMessage::create([
                    'chat_id' => $chat->id,
                    'user_id' => auth()->id(),
                    'sender_type' => 'user',
                    'message' => '✅ Payment proof submitted for Custom Order #' . $order->id . '. Awaiting admin verification.'
                ]);
            }
            
            return redirect($redirectUrl)->with('success', 'Payment proof uploaded successfully! We will verify your payment shortly.');
        }
        
        return redirect($redirectUrl)->with('error', 'Failed to upload payment proof.');
    }
    
    /**
     * Submit custom order details form response
     */
    public function submitFormResponse(Request $request, Chat $chat)
    {
        // Check ownership
        if ($chat->user_id !== auth()->id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'original_message_id' => 'required|exists:chat_messages,id',
        ]);
        
        // Get all form fields from request (excluding _token and original_message_id)
        $formResponses = $request->except(['_token', 'original_message_id']);
        
        // Get the original form request message
        $originalMessage = ChatMessage::find($validated['original_message_id']);
        
        // Create formatted message showing the details
        $messageText = "✅ Custom Order Details Submitted:\n\n";
        
        if ($originalMessage && !empty($originalMessage->form_data['fields'])) {
            foreach ($originalMessage->form_data['fields'] as $field) {
                $fieldName = $field['name'];
                $fieldLabel = $field['label'];
                $fieldValue = $formResponses[$fieldName] ?? 'N/A';
                
                $messageText .= "{$fieldLabel}: {$fieldValue}\n";
            }
        }
        
        // Store the form response as a message
        ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'sender_type' => 'user',
            'message_type' => 'form_response',
            'message' => $messageText,
            'form_data' => [
                'original_message_id' => $validated['original_message_id'],
                'responses' => $formResponses,
                'submitted_at' => now()->toDateTimeString(),
            ],
            'is_read' => false,
        ]);
        
        $chat->update(['updated_at' => now()]);
        
        return $this->redirectWithToken('chats.show', $chat)->with('success', 'Details submitted successfully! The admin will review and send you a price quote.');
    }
}
