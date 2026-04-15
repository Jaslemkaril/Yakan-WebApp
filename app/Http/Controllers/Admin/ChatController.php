<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Show all chats
     */
    public function index(Request $request)
    {
        $query = Chat::query()->with('user');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by user name or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $chats = $query->orderBy('updated_at', 'desc')->paginate(15);
        $stats = [
            'total' => Chat::count(),
            'open' => Chat::where('status', 'open')->count(),
            'closed' => Chat::where('status', 'closed')->count(),
            'pending' => Chat::where('status', 'pending')->count(),
        ];

        return view('admin.chats.index', compact('chats', 'stats'));
    }

    /**
     * Show specific chat
     */
    public function show(Chat $chat)
    {
        // Mark all user messages as read
        $chat->messages()
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $chat->messages()->get();
        $hasAcceptedQuote = $chat->hasAcceptedQuote();

        return view('admin.chats.show', compact('chat', 'messages', 'hasAcceptedQuote'));
    }

    /**
     * Send admin reply
     */
    public function sendReply(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'image' => 'nullable|image|max:5120',
            'reference_images' => 'nullable|array',
            'reference_images.*' => 'nullable|string',
        ]);

        $messageData = [
            'chat_id' => $chat->id,
            'sender_type' => 'admin',
            'message' => $validated['message'],
            'is_read' => false, // Mark as unread for the user to see
        ];

        // Add reference images if provided (for quotes)
        if (!empty($validated['reference_images'])) {
            $messageData['reference_images'] = $validated['reference_images'];
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $cloudinary = new CloudinaryService();
            $storedPath = null;
            
            // Try Cloudinary first (persistent storage)
            if ($cloudinary->isEnabled()) {
                $result = $cloudinary->uploadFile($image, 'admin-chats');
                if ($result) {
                    $storedPath = $result['url'];
                    \Log::info('Admin chat image uploaded to Cloudinary', [
                        'url' => $storedPath,
                        'chat_id' => $chat->id,
                    ]);
                }
            }
            
            // Fallback to local storage
            if (!$storedPath) {
                $storedPath = $image->store('chat-images', 'public');
                \Log::info('Admin chat image uploaded to local storage', [
                    'path' => $storedPath,
                    'chat_id' => $chat->id,
                ]);
            }
            
            $messageData['image_path'] = $storedPath;
        }

        $newMessage = ChatMessage::create($messageData);

        // If this reply is a price quote, close/void older active quotes in this chat.
        $isPriceQuote = str_contains(strtoupper((string) $validated['message']), 'PRICE QUOTE');
        if ($isPriceQuote) {
            $nowIso = now()->toISOString();

            $newQuoteData = is_array($newMessage->form_data) ? $newMessage->form_data : [];
            $newQuoteData['quote_status'] = 'active';
            $newQuoteData['quoted_at'] = $nowIso;
            $newMessage->form_data = $newQuoteData;
            $newMessage->save();

            $previousQuotes = ChatMessage::where('chat_id', $chat->id)
                ->where('sender_type', 'admin')
                ->where('id', '!=', $newMessage->id)
                ->where('message', 'like', '%PRICE QUOTE%')
                ->get();

            foreach ($previousQuotes as $previousQuote) {
                $quoteData = is_array($previousQuote->form_data) ? $previousQuote->form_data : [];
                $currentStatus = strtolower((string) ($quoteData['quote_status'] ?? 'active'));

                // Keep historical outcomes intact.
                if (in_array($currentStatus, ['accepted', 'declined', 'void', 'closed'], true)) {
                    continue;
                }

                $quoteData['quote_status'] = 'void';
                $quoteData['voided_at'] = $nowIso;
                $quoteData['void_reason'] = 'Replaced by a newer quote';
                $quoteData['replaced_by_quote_message_id'] = $newMessage->id;
                $previousQuote->form_data = $quoteData;
                $previousQuote->save();
            }
        }

        $chat->update(['updated_at' => now(), 'status' => 'open']); // Set status to open when admin replies

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // Include auth_token in redirect for Railway session handling
        $authToken = $request->input('auth_token') ?? $request->query('auth_token');
        $redirectUrl = route('admin.chats.show', $chat);
        if ($authToken) {
            $redirectUrl .= '?auth_token=' . urlencode($authToken);
        }
        return redirect($redirectUrl)->with('success', 'Reply sent successfully!');
    }

    /**
     * Update chat status
     */
    public function updateStatus(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,closed,pending',
        ]);

        $chat->update(['status' => $validated['status']]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $chat->status]);
        }

        // Include auth_token in redirect for Railway session handling
        $authToken = $request->input('auth_token') ?? $request->query('auth_token');
        $redirectUrl = route('admin.chats.show', $chat);
        if ($authToken) {
            $redirectUrl .= '?auth_token=' . urlencode($authToken);
        }
        return redirect($redirectUrl)->with('success', 'Chat status updated!');
    }
    
    /**
     * Request custom order details from customer
     */
    public function requestDetails(Chat $chat, $messageId)
    {
        try {
            \Log::info('Request details started', [
                'chat_id' => $chat->id,
                'message_id' => $messageId
            ]);
            
            // Verify the message exists and belongs to this chat
            $originalMessage = ChatMessage::where('id', $messageId)
                ->where('chat_id', $chat->id)
                ->first();
            
            if (!$originalMessage) {
                \Log::warning('Original message not found', [
                    'chat_id' => $chat->id,
                    'message_id' => $messageId
                ]);
                return response()->json(['success' => false, 'message' => 'Message not found'], 404);
            }
            
            // Create form request message
            $formData = [
                'original_message_id' => $messageId,
                'fields' => [
                    [
                        'name' => 'order_name',
                        'label' => 'Order Name',
                        'type' => 'text',
                        'placeholder' => 'e.g., Custom Yakan Bag',
                        'required' => true
                    ],
                    [
                        'name' => 'meters',
                        'label' => 'Meters',
                        'type' => 'number',
                        'placeholder' => 'e.g., 5',
                        'required' => true,
                        'min' => 0.1,
                        'step' => 0.1
                    ],
                    [
                        'name' => 'fabric_type',
                        'label' => 'Fabric Type (optional)',
                        'type' => 'text',
                        'placeholder' => 'e.g., Cotton, Polyester',
                        'required' => false
                    ],
                    [
                        'name' => 'additional_notes',
                        'label' => 'Additional Details (optional)',
                        'type' => 'textarea',
                        'placeholder' => 'Any specific requirements or preferences',
                        'required' => false
                    ]
                ]
            ];
            
            \Log::info('Creating form request message', ['form_data' => $formData]);
            
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_type' => 'admin',
                'message_type' => 'form_request',
                'message' => '📋 Please provide the following details for your custom order request:',
                'form_data' => $formData,
                'is_read' => false,
            ]);
            
            \Log::info('Form request message created', ['message_id' => $message->id]);
            
            $chat->update(['updated_at' => now()]);
            
            \Log::info('Request details completed successfully');
            
            return response()->json(['success' => true, 'message' => 'Details request sent to customer']);
            
        } catch (\Exception $e) {
            \Log::error('Error in requestDetails', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'chat_id' => $chat->id ?? 'unknown',
                'message_id' => $messageId ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete chat
     */
    public function destroy(Request $request, Chat $chat)
    {
        $chat->delete();

        // Include auth_token in redirect for Railway session handling
        $authToken = $request->input('auth_token') ?? $request->query('auth_token');
        $redirectUrl = route('admin.chats.index');
        if ($authToken) {
            $redirectUrl .= '?auth_token=' . urlencode($authToken);
        }
        return redirect($redirectUrl)->with('success', 'Chat deleted successfully!');
    }

    /**
     * Get unread chats count
     */
    public function unreadCount()
    {
        $count = Chat::whereHas('messages', function ($query) {
            $query->where('sender_type', 'user')
                  ->where('is_read', false);
        })->count();

        return response()->json(['unread_count' => $count]);
    }
}
