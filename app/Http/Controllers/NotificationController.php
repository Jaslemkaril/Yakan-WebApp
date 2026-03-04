<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $notification = $user->notifications()->findOrFail($id);
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        \Log::info('Mark all as read request', [
            'user_id' => Auth::id(),
            'auth_check' => Auth::check(),
            'has_auth_token' => request()->has('auth_token'),
            'bearer_token' => request()->bearerToken() ? 'present' : 'null',
            'x_auth_token' => request()->header('X-Auth-Token') ? 'present' : 'null',
        ]);
        
        $user = Auth::user();
        if (!$user) {
            \Log::warning('Mark all as read failed - not authenticated');
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $count = $user->notifications()->whereNull('read_at')->count();
        \Log::info('Mark all as read executing', ['user_id' => $user->id, 'unread_count' => $count]);
        
        $user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        \Log::info('Mark all as read completed', ['user_id' => $user->id]);
        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0], 401);
        }
        
        $count = $user->notifications()->whereNull('read_at')->count();
        return response()->json(['count' => $count]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }
            return redirect()->route('login');
        }
        
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();
        
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Notification deleted']);
        }
        return back()->with('success', 'Notification deleted');
    }

    public function clear()
    {
        $user = Auth::user();
        if (!$user) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }
            return redirect()->route('login');
        }
        
        $user->notifications()->delete();
        
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'All notifications cleared']);
        }
        return back()->with('success', 'All notifications cleared');
    }
}
