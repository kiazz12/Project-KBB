<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public static function notifySuperAdmins(string $type, string $message, array $data = []): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $roleLabel = $user->isSuperAdmin() ? 'Super Admin' : 'Admin';
        $displayName = $user->currentDisplayName();

        User::where('role', 'super_admin')
            ->where('id', '!=', $user->id)
            ->get()
            ->each(fn ($sa) => Notification::create([
                'user_id' => $sa->id,
                'type' => $type,
                'message' => "{$roleLabel} {$displayName} ({$user->email}) {$message}",
                'data' => array_merge($data, [
                    'actor_id' => $user->id,
                    'actor_name' => $displayName,
                    'actor_email' => $user->email,
                    'actor_role' => $user->role->value,
                ]),
            ]));
    }
}
