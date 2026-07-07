@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Notifikasi</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $unreadCount }} belum dibaca</p>
    </div>
    @if($unreadCount > 0)
    <form action="{{ route('notifications.markAllRead') }}" method="POST">
        @csrf
        <button type="submit" class="bg-kbb-700 hover:bg-kbb-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Tandai Semua Dibaca</button>
    </form>
    @endif
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    @forelse($notifications as $notif)
    <div class="flex items-start gap-4 p-5 {{ !$loop->first ? 'border-t border-gray-100' : '' }} {{ is_null($notif->read_at) ? 'bg-kbb-50/50' : '' }}">
        <div class="w-10 h-10 rounded-full {{ is_null($notif->read_at) ? 'bg-kbb-100' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-5 h-5 {{ is_null($notif->read_at) ? 'text-kbb-700' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-900">{{ $notif->message }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
        </div>
        @if(is_null($notif->read_at))
        <form action="{{ route('notifications.read', $notif) }}" method="POST">
            @csrf
            <button type="submit" class="text-xs text-kbb-700 hover:text-kbb-800 font-medium whitespace-nowrap">Tandai dibaca</button>
        </form>
        @endif
    </div>
    @empty
    <div class="text-center py-12">
        <svg class="w-12 h-12 mx-auto text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <p class="text-sm text-gray-400 mt-2">Tidak ada notifikasi</p>
    </div>
    @endforelse

    @if($notifications->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
