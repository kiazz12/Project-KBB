<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        $displayName = $user->currentDisplayName();
        $this->notifySuperAdmins("User baru '{$displayName}' ({$user->email}) telah dibuat.");
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('password')) {
            $user->makeHidden('password');
        }

        $changes = [];
        foreach (['name', 'display_name', 'email', 'role', 'nip', 'opd'] as $field) {
            if ($user->wasChanged($field)) {
                $changes[] = ucfirst($field === 'nip' ? 'NIP' : ($field === 'display_name' ? 'nama tampilan' : $field));
            }
        }

        if (! empty($changes)) {
            $displayName = $user->currentDisplayName();
            $this->notifySuperAdmins("User '{$displayName}' telah diperbarui (".implode(', ', $changes).').');
        }
    }

    public function deleted(User $user): void
    {
        $displayName = $user->currentDisplayName();
        $this->notifySuperAdmins("User '{$displayName}' ({$user->email}) telah dihapus.");
    }

    private function notifySuperAdmins(string $message): void
    {
        $superAdmins = User::where('role', 'super_admin')->get();

        foreach ($superAdmins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'user_activity',
                'message' => $message,
                'data' => null,
            ]);
        }
    }
}
