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

    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();
        return back()->with('success', 'Notification deleted');
    }

    public function clear()
    {
        Auth::user()->notifications()->delete();
        return back()->with('success', 'All notifications cleared');
    }
}
