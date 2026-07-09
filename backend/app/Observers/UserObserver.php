<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        $this->notifySuperAdmins("User baru '{$user->name}' ({$user->email}) telah dibuat.");
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('password')) {
            $user->makeHidden('password');
        }

        $changes = [];
        foreach (['name', 'email', 'role', 'nip', 'opd'] as $field) {
            if ($user->wasChanged($field)) {
                $changes[] = ucfirst($field === 'nip' ? 'NIP' : $field);
            }
        }

        if (!empty($changes)) {
            $this->notifySuperAdmins("User '{$user->name}' telah diperbarui (" . implode(', ', $changes) . ').');
        }
    }

    public function deleted(User $user): void
    {
        $this->notifySuperAdmins("User '{$user->name}' ({$user->email}) telah dihapus.");
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
