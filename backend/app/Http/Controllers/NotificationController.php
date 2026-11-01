<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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

        $notifications->through(fn (Notification $notification): array => [
            'id' => $notification->id,
            'type' => $notification->type,
            'message' => $notification->message,
            'is_read' => $notification->read_at !== null,
            'created_at' => $notification->created_at?->toIso8601String(),
            'created_at_human' => $notification->created_at?->diffForHumans(),
        ]);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
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
