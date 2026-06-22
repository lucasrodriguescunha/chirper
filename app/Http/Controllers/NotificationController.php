<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(20);

        $user->unreadNotifications->markAsRead();
        $user->forgetUnreadNotificationsCount();

        return view('notifications.index', ['notifications' => $notifications]);
    }

    public function destroy(Request $request, string $id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();
        $user->forgetUnreadNotificationsCount();

        return back()->with('success', 'Notification removed.');
    }

    public function clear()
    {
        $user = auth()->user();
        $user->notifications()->delete();
        $user->forgetUnreadNotificationsCount();

        return back()->with('success', 'All notifications cleared.');
    }
}
