<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /**
     * Get admin notifications
     */
    public function index(Request $request)
    {
        try {
            $notifications = AdminNotification::where('admin_id', auth()->id())
                ->orWhereNull('admin_id') // Global notifications
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'url' => $notification->url,
                        'icon' => $notification->icon,
                        'color' => $notification->color,
                        'is_read' => $notification->is_read,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'data' => $notification->data,
                    ];
                });

            $unreadCount = AdminNotification::where(function($query) {
                    $query->where('admin_id', auth()->id())
                          ->orWhereNull('admin_id');
                })
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $notification = AdminNotification::where(function($query) use ($id) {
                    $query->where('admin_id', auth()->id())
                          ->orWhereNull('admin_id');
                })
                ->where('id', $id)
                ->firstOrFail();

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            AdminNotification::where(function($query) {
                    $query->where('admin_id', auth()->id())
                          ->orWhereNull('admin_id');
                })
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read',
            ], 500);
        }
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        try {
            $count = AdminNotification::where(function($query) {
                    $query->where('admin_id', auth()->id())
                          ->orWhereNull('admin_id');
                })
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'unread_count' => 0,
            ]);
        }
    }
}
