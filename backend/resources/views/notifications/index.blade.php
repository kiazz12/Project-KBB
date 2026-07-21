@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="mb-8">
    <div class="bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] rounded-2xl p-8 text-white relative overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/[0.03] rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-1/3 w-96 h-96 bg-white/[0.02] rounded-full translate-y-1/2"></div>
        <div class="absolute top-1/2 left-10 w-4 h-4 bg-white/10 rounded-full"></div>
        <div class="absolute top-20 right-20 w-2 h-2 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Notifikasi</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Notifikasi</h1>
                    <p class="text-kbb-200/80 mt-1 text-sm">{{ $unreadCount }} belum dibaca</p>
                </div>
                @if($unreadCount > 0)
                <form action="{{ route('notifications.markAllRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-5 py-2.5 rounded-xl backdrop-blur-sm border border-white/10 transition-all hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Tandai Semua Dibaca
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
    @forelse($notifications as $notif)
    <div class="flex items-start gap-4 p-5 {{ !$loop->first ? 'border-t border-gray-100 dark:border-slate-700' : '' }} {{ is_null($notif->read_at) ? 'bg-kbb-50/50 dark:bg-kbb-500/10' : '' }}">
        <div class="w-10 h-10 rounded-full {{ is_null($notif->read_at) ? 'bg-kbb-100 dark:bg-kbb-500/20' : 'bg-gray-100 dark:bg-slate-700' }} flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-5 h-5 {{ is_null($notif->read_at) ? 'text-kbb-700 dark:text-kbb-400' : 'text-gray-400 dark:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $notif->message }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
        </div>
        @if(is_null($notif->read_at))
        <form action="{{ route('notifications.read', $notif) }}" method="POST">
            @csrf
            <button type="submit" class="text-xs text-kbb-700 hover:text-kbb-800 dark:text-kbb-400 dark:hover:text-kbb-300 font-medium whitespace-nowrap">Tandai dibaca</button>
        </form>
        @endif
    </div>
    @empty
    <div class="text-center py-12">
        <svg class="w-12 h-12 mx-auto text-gray-200 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <p class="text-sm text-gray-400 mt-2">Tidak ada notifikasi</p>
    </div>
    @endforelse

    @if($notifications->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 dark:border-slate-700">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
