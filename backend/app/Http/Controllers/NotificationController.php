<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $notifications,
            ]);
        }

        $unreadCount = Notification::where('user_id', auth()->id())->unread()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function unread(): JsonResponse
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->unread()
            ->latest()
            ->limit(5)
            ->get();

        $count = Notification::where('user_id', auth()->id())->unread()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
                'notifications' => $notifications,
            ],
        ]);
    }

    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllRead()
    {
        Notification::where('user_id', auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
