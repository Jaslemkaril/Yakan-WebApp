<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Show all chats
     */
    public function index(Request $request)
    {
        $query = Chat::query();

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
                  ->orWhere('subject', 'like', "%{$search}%");
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

        return view('admin.chats.show', compact('chat', 'messages'));
    }

    /**
     * Send admin reply
     */
    public function sendReply(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $messageData = [
            'chat_id' => $chat->id,
            'sender_type' => 'admin',
            'message' => $validated['message'],
            'is_read' => false, // Mark as unread for the user to see
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat-images', 'public');
            $messageData['image_path'] = $path;
        }

        ChatMessage::create($messageData);
        $chat->update(['updated_at' => now(), 'status' => 'open']); // Set status to open when admin replies

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.chats.show', $chat)->with('success', 'Reply sent successfully!');
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

        return redirect()->route('admin.chats.show', $chat)->with('success', 'Chat status updated!');
    }

    /**
     * Delete chat
     */
    public function destroy(Chat $chat)
    {
        $chat->delete();

        return redirect()->route('admin.chats.index')->with('success', 'Chat deleted successfully!');
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
