<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get all chats for authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $chats = Chat::where('user_id', $user->id)
                ->with(['messages' => function($query) {
                    $query->latest()->limit(1);
                }])
                ->orderBy('updated_at', 'desc')
                ->get();

            // Transform chats for mobile app
            $transformedChats = $chats->map(function($chat) {
                $latestMessage = $chat->messages->first();
                $unreadCount = $chat->messages()
                    ->where('sender_type', 'admin')
                    ->where('is_read', false)
                    ->count();

                return [
                    'id' => $chat->id,
                    'subject' => $chat->subject,
                    'status' => $chat->status,
                    'created_at' => $chat->created_at->toISOString(),
                    'updated_at' => $chat->updated_at->toISOString(),
                    'latest_message' => $latestMessage ? [
                        'message' => $latestMessage->message,
                        'created_at' => $latestMessage->created_at->toISOString(),
                        'sender_type' => $latestMessage->sender_type,
                    ] : null,
                    'unread_count' => $unreadCount,
                    'messages_count' => $chat->messages()->count(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedChats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific chat with messages
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $chat = Chat::where('id', $id)
                ->where('user_id', $user->id)
                ->with('messages')
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found',
                ], 404);
            }

            // Mark messages as read
            $chat->messages()
                ->where('sender_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            // Transform messages
            $messages = $chat->messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'is_read' => $message->is_read,
                    'image_url' => $message->image_path ? url('storage/' . $message->image_path) : null,
                    'created_at' => $message->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $chat->id,
                    'subject' => $chat->subject,
                    'status' => $chat->status,
                    'created_at' => $chat->created_at->toISOString(),
                    'updated_at' => $chat->updated_at->toISOString(),
                    'messages' => $messages,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new chat
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            // Create chat
            $chat = Chat::create([
                'user_id' => $user->id,
                'subject' => $request->subject,
                'status' => 'open',
            ]);

            // Create first message
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_type' => 'user',
                'message' => $request->message,
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat created successfully',
                'data' => [
                    'id' => $chat->id,
                    'subject' => $chat->subject,
                    'status' => $chat->status,
                    'created_at' => $chat->created_at->toISOString(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create chat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send message to chat
     */
    public function sendMessage(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            
            $chat = Chat::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found',
                ], 404);
            }

            // Create message
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_type' => 'user',
                'message' => $request->message,
                'is_read' => false,
            ]);

            // Update chat timestamp
            $chat->touch();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'created_at' => $message->created_at->toISOString(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update chat status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:open,closed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            
            $chat = Chat::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found',
                ], 404);
            }

            $chat->status = $request->status;
            $chat->save();

            return response()->json([
                'success' => true,
                'message' => 'Chat status updated successfully',
                'data' => [
                    'id' => $chat->id,
                    'status' => $chat->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update chat status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Respond to a price quote (Accept/Decline)
     */
    public function respondToQuote(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'response' => 'required|in:accepted,declined',
                'quote_message_id' => 'required|exists:chat_messages,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            
            $chat = Chat::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found',
                ], 404);
            }

            $response = $request->response;
            $emoji = $response === 'accepted' ? '✅' : '❌';
            $action = $response === 'accepted' ? 'accepted' : 'declined';
            
            $message = "{$emoji} Customer {$action} the price quote.\n\n" . 
                       "Customer will " . ($response === 'accepted' ? 'proceed with the custom order.' : 'not proceed with this quote.');

            // Create response message
            $chatMessage = ChatMessage::create([
                'chat_id' => $chat->id,
                'user_id' => $user->id,
                'sender_type' => 'user',
                'message' => $message,
            ]);

            $chat->update(['updated_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Response sent to admin successfully',
                'data' => [
                    'id' => $chatMessage->id,
                    'message' => $chatMessage->message,
                    'sender_type' => $chatMessage->sender_type,
                    'created_at' => $chatMessage->created_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send response',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
