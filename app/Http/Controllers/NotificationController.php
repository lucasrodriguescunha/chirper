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

        return view('notifications.index', ['notifications' => $notifications]);
    }

    public function destroy(Request $request, string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Notification removed.');
    }

    public function clear()
    {
        auth()->user()->notifications()->delete();

        return back()->with('success', 'All notifications cleared.');
    }
}
