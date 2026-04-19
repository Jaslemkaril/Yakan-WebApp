<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\CustomOrder;
use App\Models\UserAddress;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const MESSAGE_WINDOW = 250;

    private function generateSafeUploadFilename($file, string $prefix = 'chat'): string
    {
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg'));
        return $prefix . '_' . now()->format('YmdHis') . '_' . Str::random(16) . '.' . $extension;
    }

    /**
     * Load a bounded, chronological message list to avoid DB sort-memory errors.
     */
    private function loadRecentMessages(int $chatId)
    {
        return ChatMessage::query()
            ->where('chat_id', $chatId)
            ->orderByDesc('id')
            ->limit(self::MESSAGE_WINDOW)
            ->get()
            ->sortBy('id')
            ->values();
    }

    private function buildChatImageUrl(string $folder, string $filename): string
    {
        $folder = trim($folder, '/');
        $filename = ltrim($filename, '/');

        try {
            return route('chat.image', ['folder' => $folder, 'filename' => $filename]);
        } catch (\Throwable $e) {
            \Log::warning('chat.image route unavailable, using direct URL fallback', [
                'folder' => $folder,
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            return url('/chat-image/' . rawurlencode($folder) . '/' . rawurlencode($filename));
        }
    }

    private function buildInlineImageDataFromFile(string $filePath): ?string
    {
        if (!is_file($filePath)) {
            return null;
        }

        $mime = @mime_content_type($filePath) ?: null;
        if (!$mime || !str_starts_with($mime, 'image/')) {
            return null;
        }

        $binary = @file_get_contents($filePath);
        if ($binary === false) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode($binary);
    }

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

    private function normalizePaymentTypeLabel(?string $value): ?string
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        return match ($normalized) {
            'full', 'full_payment' => 'Full Payment',
            'downpayment', 'down_payment' => 'Downpayment',
            'bank_transfer' => 'Bank Transfer',
            'online_banking', 'online', 'paymongo', 'maya' => 'Maya',
            'gcash' => 'GCash',
            default => ucwords(str_replace('_', ' ', $normalized)),
        };
    }

    private function resolveQuotePaymentTypeLabel(ChatMessage $quoteMessage, int $chatId, int $userId, ?string $preferred = null): ?string
    {
        $preferredLabel = $this->normalizePaymentTypeLabel($preferred);
        if ($preferredLabel) {
            return $preferredLabel;
        }

        $quoteMeta = is_array($quoteMessage->form_data) ? $quoteMessage->form_data : [];
        foreach (['payment_type', 'payment_option', 'payment_plan', 'payment_method'] as $key) {
            $metaLabel = $this->normalizePaymentTypeLabel((string) ($quoteMeta[$key] ?? ''));
            if ($metaLabel) {
                return $metaLabel;
            }
        }

        if (preg_match('/payment\s*(type|option|method)\s*:\s*([^\n\r]+)/i', (string) $quoteMessage->message, $matches)) {
            $textLabel = $this->normalizePaymentTypeLabel(trim((string) ($matches[2] ?? '')));
            if ($textLabel) {
                return $textLabel;
            }
        }

        $order = $this->findLatestChatCustomOrder($chatId, $userId);

        if ($order) {
            $optionLabel = $this->normalizePaymentTypeLabel((string) ($order->payment_option ?? ''));
            if ($optionLabel) {
                return $optionLabel;
            }

            $methodLabel = $this->normalizePaymentTypeLabel((string) ($order->payment_method ?? ''));
            if ($methodLabel) {
                return $methodLabel;
            }
        }

        return null;
    }

    private function findLatestChatCustomOrder(int $chatId, int $userId): ?CustomOrder
    {
        $order = null;
        $hasChatIdColumn = Schema::hasColumn('custom_orders', 'chat_id');

        if ($hasChatIdColumn) {
            $order = CustomOrder::where('chat_id', $chatId)
                ->where('user_id', $userId)
                ->latest('id')
                ->first();
        }

        if ($order) {
            return $order;
        }

        $chatNeedle = 'Custom order from chat ID: ' . $chatId;
        $order = CustomOrder::where('user_id', $userId)
            ->where('specifications', 'like', '%' . $chatNeedle . '%')
            ->latest('id')
            ->first();

        if ($order && $hasChatIdColumn && empty($order->chat_id)) {
            try {
                $order->chat_id = $chatId;
                $order->save();
            } catch (\Throwable $exception) {
                \Log::warning('Unable to backfill chat_id on recovered custom order', [
                    'custom_order_id' => $order->id,
                    'chat_id' => $chatId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $order;
    }

    private function extractQuotedPriceFromMessage(ChatMessage $quoteMessage): float
    {
        if (preg_match('/Total:\s*₱?([\d,]+\.?\d*)/i', (string) $quoteMessage->message, $matches)) {
            return (float) str_replace(',', '', (string) ($matches[1] ?? '0'));
        }

        return 0.0;
    }

    private function collectQuoteDesignImages(Chat $chat, ChatMessage $quoteMessage): array
    {
        $designImages = [];

        try {
            if (method_exists($quoteMessage, 'getAttribute')) {
                $designImages = $quoteMessage->reference_images ?? [];
            }
        } catch (\Exception $e) {
            \Log::warning('reference_images column might not exist yet', ['error' => $e->getMessage()]);
        }

        if (!is_array($designImages)) {
            $designImages = [$designImages];
        }

        $designImages = collect($designImages)
            ->map(fn($url) => trim((string) $url))
            ->filter(fn($url) => $url !== '' && strtolower($url) !== 'null')
            ->unique()
            ->values()
            ->all();

        if (!empty($designImages)) {
            return $designImages;
        }

        $fallbackImageMessages = ChatMessage::query()
            ->where('chat_id', $chat->id)
            ->where('sender_type', 'user')
            ->whereNotNull('image_path')
            ->where(function ($query) {
                $query->whereNull('message')
                    ->orWhere('message', 'not like', '%Payment proof%');
            })
            ->orderBy('id')
            ->get(['image_path', 'form_data']);

        foreach ($fallbackImageMessages as $fallbackImageMessage) {
            $inlineImage = data_get($fallbackImageMessage->form_data, 'inline_image_data');
            if (is_string($inlineImage) && str_starts_with($inlineImage, 'data:image')) {
                $designImages[] = $inlineImage;
            }

            $imagePath = trim((string) ($fallbackImageMessage->image_path ?? ''));
            if ($imagePath !== '') {
                $designImages[] = $imagePath;
            }
        }

        return collect($designImages)
            ->map(fn($url) => trim((string) $url))
            ->filter(fn($url) => $url !== '' && strtolower($url) !== 'null')
            ->unique()
            ->values()
            ->all();
    }

    private function filterCustomOrderPayloadForExistingColumns(array $payload): array
    {
        try {
            if (!Schema::hasTable('custom_orders')) {
                return $payload;
            }

            $columns = Schema::getColumnListing('custom_orders');
            if (empty($columns)) {
                return $payload;
            }

            $allowed = array_flip($columns);
            return array_intersect_key($payload, $allowed);
        } catch (\Throwable $exception) {
            \Log::warning('Unable to filter custom order payload by table columns', [
                'error' => $exception->getMessage(),
            ]);

            return $payload;
        }
    }

    private function createChatOrderFromQuote(Chat $chat, ChatMessage $quoteMessage, string $selectedDeliveryType): CustomOrder
    {
        $selectedDeliveryType = strtolower(trim($selectedDeliveryType));
        if (!in_array($selectedDeliveryType, ['delivery', 'pickup'], true)) {
            $selectedDeliveryType = 'delivery';
        }

        $authUser = auth()->user();
        if (!$authUser) {
            throw new \RuntimeException('Please log in again and retry quote acceptance.');
        }

        $quotedPrice = $this->extractQuotedPriceFromMessage($quoteMessage);

        $userAddress = UserAddress::where('user_id', $authUser->id)
            ->where('is_default', true)
            ->first();

        if ($selectedDeliveryType === 'delivery' && !$userAddress) {
            throw new \RuntimeException('Please add a delivery address first, or choose Pick up.');
        }

        $shippingFee = $selectedDeliveryType === 'pickup'
            ? 0.0
            : $this->calculateCanonicalShippingFeeFromAddress($userAddress);

        $totalAmount = $quotedPrice + $shippingFee;

        $formattedAddress = $selectedDeliveryType === 'pickup'
            ? 'Tuwas Yakan Weaving Center, Yakan Village, Upper Calarian, Labuan-Limpapa Road, National Road, Zamboanga City, Philippines 7000'
            : ($userAddress ? $userAddress->formatted_address : 'Address not provided');

        $designImages = $this->collectQuoteDesignImages($chat, $quoteMessage);

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
                $orderNotes .= '- Image ' . ($index + 1) . ': ' . $imageUrl . "\n";
            }
        }

        $payload = [
            'user_id' => $authUser->id,
            'chat_id' => $chat->id,
            'specifications' => $orderNotes,
            'quantity' => (int) $quantityMeters ?: 1,
            'product_type' => $fabricType,
            'estimated_price' => $quotedPrice,
            'final_price' => $totalAmount,
            'shipping_fee' => $shippingFee,
            'delivery_address' => $formattedAddress,
            'delivery_city' => $selectedDeliveryType === 'pickup' ? null : ($userAddress->city ?? null),
            'delivery_province' => $selectedDeliveryType === 'pickup' ? null : ($userAddress->province ?? $userAddress->region ?? null),
            'delivery_type' => $selectedDeliveryType,
            'phone' => $userAddress ? ($userAddress->phone_number ?? ($authUser->phone ?? '')) : ($authUser->phone ?? ''),
            'email' => $authUser->email,
            'payment_status' => 'pending',
            'status' => 'approved',
            'approved_at' => now(),
            'additional_notes' => ($customerNotes ? 'Customer Notes: ' . $customerNotes . "\n\n" : '')
                . 'Delivery Type: ' . ucfirst($selectedDeliveryType)
                . "\nShipping Fee: ₱" . number_format($shippingFee, 2)
                . "\nQuoted Price: ₱" . number_format($quotedPrice, 2)
                . "\nTotal: ₱" . number_format($totalAmount, 2),
            'design_upload' => !empty($designImages) ? implode(',', $designImages) : null,
        ];

        $payload = $this->filterCustomOrderPayloadForExistingColumns($payload);

        return CustomOrder::create($payload);
    }

    private function rollbackQuoteAcceptance(ChatMessage $quoteMessage, ?ChatMessage $responseMessage): void
    {
        if ($responseMessage && $responseMessage->exists) {
            try {
                $responseMessage->delete();
            } catch (\Throwable $exception) {
                \Log::warning('Unable to delete response message during quote rollback', [
                    'message_id' => $responseMessage->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        try {
            $quoteMeta = is_array($quoteMessage->form_data) ? $quoteMessage->form_data : [];
            $quoteMeta['quote_status'] = 'active';
            unset(
                $quoteMeta['responded_at'],
                $quoteMeta['response_message_id'],
                $quoteMeta['responded_by_user_id'],
                $quoteMeta['accepted_delivery_type'],
                $quoteMeta['chat_order_id']
            );
            $quoteMessage->form_data = $quoteMeta;
            $quoteMessage->save();
        } catch (\Throwable $exception) {
            \Log::warning('Unable to rollback quote message metadata', [
                'quote_message_id' => $quoteMessage->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function attemptRecoverMissingOrderFromAcceptedQuote(Chat $chat): ?CustomOrder
    {
        $acceptedQuote = ChatMessage::where('chat_id', $chat->id)
            ->where('sender_type', 'admin')
            ->where('message', 'like', '%PRICE QUOTE%')
            ->orderByDesc('created_at')
            ->get()
            ->first(function (ChatMessage $message) {
                $meta = is_array($message->form_data) ? $message->form_data : [];
                return strtolower((string) ($meta['quote_status'] ?? '')) === 'accepted';
            });

        if (!$acceptedQuote) {
            return null;
        }

        $quoteMeta = is_array($acceptedQuote->form_data) ? $acceptedQuote->form_data : [];
        $selectedDeliveryType = strtolower((string) ($quoteMeta['accepted_delivery_type'] ?? ''));

        if (!in_array($selectedDeliveryType, ['delivery', 'pickup'], true)) {
            $hasDefaultAddress = UserAddress::where('user_id', auth()->id())
                ->where('is_default', true)
                ->exists();
            $selectedDeliveryType = $hasDefaultAddress ? 'delivery' : 'pickup';
        }

        try {
            $order = $this->createChatOrderFromQuote($chat, $acceptedQuote, $selectedDeliveryType);
            $quoteMeta['accepted_delivery_type'] = $selectedDeliveryType;
            $quoteMeta['chat_order_id'] = $order->id;
            $acceptedQuote->form_data = $quoteMeta;
            $acceptedQuote->save();

            \Log::info('Recovered missing custom order from accepted quote', [
                'chat_id' => $chat->id,
                'quote_message_id' => $acceptedQuote->id,
                'custom_order_id' => $order->id,
                'user_id' => auth()->id(),
            ]);

            return $order;
        } catch (\Throwable $exception) {
            \Log::warning('Unable to recover missing custom order from accepted quote', [
                'chat_id' => $chat->id,
                'quote_message_id' => $acceptedQuote->id,
                'user_id' => auth()->id(),
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
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
                ->orderBy('updated_at', 'desc')
                ->paginate(10);

            $chatIds = $chats->getCollection()->pluck('id')->all();

            if (!empty($chatIds)) {
                $latestMessageIds = ChatMessage::whereIn('chat_id', $chatIds)
                    ->selectRaw('MAX(id) as id')
                    ->groupBy('chat_id')
                    ->pluck('id');

                $latestMessagesByChat = ChatMessage::whereIn('id', $latestMessageIds)
                    ->get()
                    ->keyBy('chat_id');

                $messageCountsByChat = ChatMessage::whereIn('chat_id', $chatIds)
                    ->selectRaw('chat_id, COUNT(*) as total_count')
                    ->groupBy('chat_id')
                    ->pluck('total_count', 'chat_id');

                $unreadCountsByChat = ChatMessage::whereIn('chat_id', $chatIds)
                    ->where('is_read', false)
                    ->where('sender_type', 'user')
                    ->selectRaw('chat_id, COUNT(*) as unread_count')
                    ->groupBy('chat_id')
                    ->pluck('unread_count', 'chat_id');

                $chats->getCollection()->transform(function (Chat $chat) use ($latestMessagesByChat, $messageCountsByChat, $unreadCountsByChat) {
                    $latestMessage = $latestMessagesByChat->get($chat->id);
                    $chat->setRelation('messages', $latestMessage ? collect([$latestMessage]) : collect());
                    $chat->setAttribute('messages_count_cached', (int) ($messageCountsByChat[$chat->id] ?? 0));
                    $chat->setAttribute('unread_count_cached', (int) ($unreadCountsByChat[$chat->id] ?? 0));

                    return $chat;
                });
            }

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
        if (!auth()->check()) {
            \Log::warning('ChatController show: unauthenticated access attempt', [
                'chat_id' => $chat->id,
                'has_token' => request()->has('auth_token') || session()->has('auth_token'),
            ]);

            return $this->redirectWithToken('login.user.form')
                ->with('error', 'Please log in to open this chat.');
        }

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

                return $this->redirectWithToken('chats.index')
                    ->with('error', 'You are not authorized to open that chat.');
            }

            // Mark messages as read
            $chat->messages()
                ->where('sender_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messages = $this->loadRecentMessages($chat->id);

            // Keep pending chat-order totals aligned with canonical shipping zones.
            $userDefaultAddress = UserAddress::where('user_id', auth()->id())
                ->where('is_default', true)
                ->first();

            if ($userDefaultAddress) {
                $shippingFee = $this->calculateCanonicalShippingFeeFromAddress($userDefaultAddress);
                $pendingOrdersQuery = CustomOrder::where('user_id', auth()->id())
                    ->where('payment_status', 'pending');

                if (Schema::hasColumn('custom_orders', 'chat_id')) {
                    $pendingOrdersQuery->where('chat_id', $chat->id);
                } else {
                    $pendingOrdersQuery->where('specifications', 'like', '%Custom order from chat ID: ' . $chat->id . '%');
                }

                $pendingOrders = $pendingOrdersQuery->get();

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

            $resolvedChatOrder = $this->findLatestChatCustomOrder((int) $chat->id, (int) auth()->id());
            if (!$resolvedChatOrder) {
                $this->attemptRecoverMissingOrderFromAcceptedQuote($chat);
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
        if (!auth()->check()) {
            \Log::warning('ChatController store: unauthenticated request', [
                'has_auth_token' => $request->has('auth_token') || $request->has('auth-token'),
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 180),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please log in again and retry.',
                    'redirect_url' => route('login.user.form'),
                ], 401);
            }

            return $this->redirectWithToken('login.user.form')
                ->with('error', 'Session expired. Please log in again and retry.');
        }

        $authUser = auth()->user();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $chat = Chat::create([
            'user_id' => $authUser->id,
            'user_name' => $authUser->name,
            'user_email' => $authUser->email,
            'user_phone' => $authUser->phone ?? '',
            'subject' => $validated['subject'],
            'status' => 'open',
        ]);

        // Store first message
        $messageData = [
            'chat_id' => $chat->id,
            'user_id' => $authUser->id,
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
                    $filename = $this->generateSafeUploadFilename($image, 'chat_msg');
                    $dir = storage_path('app/public/chats');
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $image->move($dir, $filename);
                    $storedPath = $this->buildChatImageUrl('chats', $filename);

                    $inlineImageData = $this->buildInlineImageDataFromFile($dir . DIRECTORY_SEPARATOR . $filename);
                    if ($inlineImageData) {
                        $currentFormData = is_array($messageData['form_data'] ?? null) ? $messageData['form_data'] : [];
                        $currentFormData['inline_image_data'] = $inlineImageData;
                        $messageData['form_data'] = $currentFormData;
                    }

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
                    'user_id' => $authUser->id,
                ]);
                // Still create chat even if image upload fails
            }
        }

        ChatMessage::create($messageData);

        if ($request->expectsJson() || $request->ajax()) {
            $redirectUrl = $this->redirectWithToken('chats.show', ['chat' => $chat->id])->getTargetUrl();
            return response()->json([
                'success' => true,
                'message' => 'Chat created successfully!',
                'chat_id' => $chat->id,
                'redirect_url' => $redirectUrl
            ]);
        }

        return $this->redirectWithToken('chats.show', ['chat' => $chat->id])->with('success', 'Chat created successfully!');
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
                    $filename = $this->generateSafeUploadFilename($image, 'chat_msg');
                    $dir = storage_path('app/public/chats');
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $image->move($dir, $filename);
                    $storedPath = $this->buildChatImageUrl('chats', $filename);

                    $inlineImageData = $this->buildInlineImageDataFromFile($dir . DIRECTORY_SEPARATOR . $filename);
                    if ($inlineImageData) {
                        $currentFormData = is_array($messageData['form_data'] ?? null) ? $messageData['form_data'] : [];
                        $currentFormData['inline_image_data'] = $inlineImageData;
                        $messageData['form_data'] = $currentFormData;
                    }

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
            $formData = is_array($newMessage->form_data) ? $newMessage->form_data : [];
            $imageInline = isset($formData['inline_image_data']) && is_string($formData['inline_image_data'])
                ? $formData['inline_image_data']
                : null;
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
                    'image_inline' => $imageInline,
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
            'payment_type' => 'nullable|string|max:100',
            'payment_option' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:100',
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

        $preferredPaymentType = (string) (
            $validated['payment_type']
            ?? $validated['payment_option']
            ?? $validated['payment_method']
            ?? ''
        );
        $paymentTypeLabel = $this->resolveQuotePaymentTypeLabel($quoteMessage, (int) $chat->id, (int) auth()->id(), $preferredPaymentType);
        $paymentTypeLine = $response === 'accepted'
            ? "\nPayment Type: " . ($paymentTypeLabel ?? 'To be selected')
            : '';
        
        $message = "{$emoji} Customer {$action} the price quote.\n\n" . 
                   "Customer will " . ($response === 'accepted' ? 'proceed with the custom order.' : 'not proceed with this quote.') .
                   $paymentTypeLine;

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
        $acceptedOrder = null;
        if ($response === 'accepted') {
            $selectedDeliveryType = strtolower((string) ($validated['delivery_type'] ?? 'delivery'));
            if (!in_array($selectedDeliveryType, ['delivery', 'pickup'], true)) {
                $selectedDeliveryType = 'delivery';
            }

            try {
                $order = $this->findLatestChatCustomOrder((int) $chat->id, (int) auth()->id());
                if (!$order) {
                    $order = $this->createChatOrderFromQuote($chat, $quoteMessage, $selectedDeliveryType);
                    \Log::info('Chat custom order created successfully', [
                        'custom_order_id' => $order->id,
                        'chat_id' => $chat->id,
                        'user_id' => auth()->id(),
                    ]);
                }

                $responseMeta = is_array($responseMessage->form_data) ? $responseMessage->form_data : [];
                $responseMeta['chat_order_id'] = $order->id;
                $responseMeta['delivery_type'] = $selectedDeliveryType;
                $responseMessage->form_data = $responseMeta;
                $responseMessage->save();

                $quoteMeta = is_array($quoteMessage->form_data) ? $quoteMessage->form_data : [];
                $quoteMeta['accepted_delivery_type'] = $selectedDeliveryType;
                $quoteMeta['chat_order_id'] = $order->id;
                $quoteMessage->form_data = $quoteMeta;
                $quoteMessage->save();

                $chat->update(['updated_at' => now()]);
                $acceptedOrder = $order;
            } catch (\Throwable $e) {
                $this->rollbackQuoteAcceptance($quoteMessage, $responseMessage);

                \Log::error('Failed to create or recover chat custom order after quote acceptance', [
                    'error' => $e->getMessage(),
                    'chat_id' => $chat->id,
                    'quote_message_id' => $quoteMessage->id,
                    'user_id' => auth()->id(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return $this->redirectWithToken('chats.show', $chat)
                    ->with('error', 'Unable to create custom order right now. Please try accepting the quote again.');
            }
        } else {
            $chat->update(['updated_at' => now()]);
        }

        if ($response === 'accepted' && $acceptedOrder) {
            $paymentUrl = route('custom_orders.payment', ['order' => $acceptedOrder->id]);
            $separator = str_contains($paymentUrl, '?') ? '&' : '?';
            $paymentUrl .= $separator . 'auto_pay=1&from_chat=1';

            $token = $request->input('auth_token') ?? $request->query('auth_token') ?? session('auth_token');
            if ($token) {
                $paymentUrl .= '&auth_token=' . urlencode((string) $token);
            }

            return redirect($paymentUrl)->with('success', 'Quote accepted. Redirecting you to secure checkout...');
        }

        return $this->redirectWithToken('chats.show', $chat)->with('success', 'Response sent to admin!');
    }

    /**
     * Accept a chat quote and immediately redirect to checkout.
     */
    public function acceptQuoteAndCheckout(Request $request, Chat $chat, ChatMessage $quoteMessage)
    {
        if ($chat->user_id !== auth()->id()) {
            abort(403);
        }

        if ((int) $quoteMessage->chat_id !== (int) $chat->id || $quoteMessage->sender_type !== 'admin') {
            return $this->redirectWithToken('chats.show', $chat)
                ->with('error', 'Invalid quote selected.');
        }

        if (!str_contains(strtoupper((string) $quoteMessage->message), 'PRICE QUOTE')) {
            return $this->redirectWithToken('chats.show', $chat)
                ->with('error', 'The selected message is not a valid price quote.');
        }

        $selectedDeliveryType = strtolower((string) $request->input('delivery_type', 'delivery'));
        if (!in_array($selectedDeliveryType, ['delivery', 'pickup'], true)) {
            $selectedDeliveryType = 'delivery';
        }

        $responseMessage = null;

        try {
            $order = $this->findLatestChatCustomOrder((int) $chat->id, (int) auth()->id());
            if (!$order) {
                $order = $this->createChatOrderFromQuote($chat, $quoteMessage, $selectedDeliveryType);
            }

            $quoteMeta = is_array($quoteMessage->form_data) ? $quoteMessage->form_data : [];
            $quoteStatus = strtolower((string) ($quoteMeta['quote_status'] ?? 'active'));

            if ($quoteStatus !== 'accepted') {
                $responseMessage = ChatMessage::create([
                    'chat_id' => $chat->id,
                    'user_id' => auth()->id(),
                    'sender_type' => 'user',
                    'message' => "✅ Customer accepted the price quote.\n\nCustomer will proceed with the custom order.",
                ]);

                $responseMeta = is_array($responseMessage->form_data) ? $responseMessage->form_data : [];
                $responseMeta['chat_order_id'] = $order->id;
                $responseMeta['delivery_type'] = $selectedDeliveryType;
                $responseMessage->form_data = $responseMeta;
                $responseMessage->save();

                $quoteMeta['quote_status'] = 'accepted';
                $quoteMeta['responded_at'] = now()->toISOString();
                $quoteMeta['response_message_id'] = $responseMessage->id;
                $quoteMeta['responded_by_user_id'] = auth()->id();
            }

            $quoteMeta['accepted_delivery_type'] = $selectedDeliveryType;
            $quoteMeta['chat_order_id'] = $order->id;
            $quoteMessage->form_data = $quoteMeta;
            $quoteMessage->save();

            $chat->update(['updated_at' => now()]);

            $paymentUrl = route('custom_orders.payment', ['order' => $order->id]);
            $separator = str_contains($paymentUrl, '?') ? '&' : '?';
            $paymentUrl .= $separator . 'auto_pay=1&from_chat=1';

            $token = $request->input('auth_token') ?? $request->query('auth_token') ?? session('auth_token');
            if ($token) {
                $paymentUrl .= '&auth_token=' . urlencode((string) $token);
            }

            return redirect($paymentUrl)->with('success', 'Quote accepted. Redirecting you to secure checkout...');
        } catch (\Throwable $e) {
            if ($responseMessage && $responseMessage->exists) {
                try {
                    $responseMessage->delete();
                } catch (\Throwable $rollbackError) {
                    \Log::warning('Failed to rollback response message in acceptQuoteAndCheckout', [
                        'chat_id' => $chat->id,
                        'response_message_id' => $responseMessage->id,
                        'error' => $rollbackError->getMessage(),
                    ]);
                }
            }

            \Log::error('acceptQuoteAndCheckout failed', [
                'chat_id' => $chat->id,
                'quote_message_id' => $quoteMessage->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->redirectWithToken('chats.show', $chat)
                ->with('error', 'Unable to start checkout right now. Please try again.');
        }
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
