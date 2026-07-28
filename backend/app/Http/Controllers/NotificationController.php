<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $notifications,
            ]);
        }

        $unreadCount = Notification::where('user_id', Auth::id())->unread()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function unread(): JsonResponse
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->unread()
            ->latest()
            ->limit(5)
            ->get();

        $count = Notification::where('user_id', Auth::id())->unread()->count();

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
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        AuditService::log('notification.read', $notification, 'Notification marked as read');

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        AuditService::log('notification.mark_all_read', null, 'All notifications marked as read');

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
