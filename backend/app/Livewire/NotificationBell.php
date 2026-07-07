<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;
    public array $recentNotifications = [];
    public bool $showDropdown = false;

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->unreadCount = Notification::where('user_id', auth()->id())
            ->unread()
            ->count();

        $this->recentNotifications = Notification::where('user_id', auth()->id())
            ->unread()
            ->latest()
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->refresh();
        }
    }

    public function markRead(int $notificationId): void
    {
        $notif = Notification::where('user_id', auth()->id())->findOrFail($notificationId);
        $notif->update(['read_at' => now()]);
        $this->refresh();
    }

    public function markAllRead(): void
    {
        Notification::where('user_id', auth()->id())
            ->unread()
            ->update(['read_at' => now()]);
        $this->refresh();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
