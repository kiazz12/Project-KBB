<div class="relative" wire:poll.10s="refresh" x-data x-on:focus.window="$wire.refresh()">
    <button wire:click="toggleDropdown" class="relative p-2 text-gray-500 dark:text-gray-400 hover:text-kbb-700 dark:hover:text-kbb-400 transition rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>

    @if ($showDropdown)
        <div class="absolute right-0 top-full mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-gray-200 dark:border-slate-700 z-50">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-slate-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifikasi</h3>
                @if ($unreadCount > 0)
                    <button wire:click="markAllRead" class="text-xs text-kbb-700 hover:text-kbb-800 font-medium">Tandai semua dibaca</button>
                @endif
            </div>
            <div class="max-h-80 overflow-y-auto">
                @forelse ($recentNotifications as $notif)
                    <div class="flex items-start gap-3 px-4 py-3 {{ !$loop->first ? 'border-t border-gray-50 dark:border-slate-700' : '' }} hover:bg-gray-50 dark:hover:bg-slate-700/40 transition">
                        <div class="w-2 h-2 mt-2 rounded-full bg-kbb-500 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $notif['message'] }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($notif['created_at'])->diffForHumans() }}</p>
                        </div>
                        <button wire:click="markRead({{ $notif['id'] }})" class="text-xs text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0">Tutup</button>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada notifikasi baru</p>
                    </div>
                @endforelse
            </div>
            <a href="{{ route('notifications.index') }}" class="block text-center text-sm text-kbb-700 dark:text-kbb-400 hover:text-kbb-800 dark:hover:text-kbb-300 font-medium py-3 border-t border-gray-100 dark:border-slate-700 transition">
                Lihat Semua Notifikasi
            </a>
        </div>
    @endif
</div>
