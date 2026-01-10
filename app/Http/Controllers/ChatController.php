<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
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
            $path = $request->file('image')->store('chat-images', 'public');
            $messageData['image_path'] = $path;
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
            'message' => 'required|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $messageData = [
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'sender_type' => 'user',
            'message' => $validated['message'],
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat-images', 'public');
            $messageData['image_path'] = $path;
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

        ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'sender_type' => 'user',
            'message' => $message,
        ]);

        $chat->update(['updated_at' => now()]);

        return redirect()->route('chats.show', $chat)->with('success', 'Response sent to admin!');
    }
}
