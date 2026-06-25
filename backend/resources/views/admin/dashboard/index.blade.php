@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Overview sistem KBB Forms</p>
    </div>
    <div class="text-sm text-gray-400">{{ now()->format('d F Y') }}</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalUsers }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Forms</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalForms }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Published</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalPublished }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Submissions</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalSubmissions }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ $submissionsToday }} hari ini</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Forms Terbaru</h2>
        @if ($recentForms->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">Belum ada form.</p>
        @else
            <div class="space-y-3">
                @foreach ($recentForms as $form)
                    <a href="{{ route('admin.forms.show', $form) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $form->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $form->user?->name ?? 'Unknown' }} · {{ $form->submissions_count }} pengiriman
                            </p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $form->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($form->status === 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                            {{ $form->status }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Users by Role</h2>
        @if ($usersByRole->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">Tidak ada data.</p>
        @else
            <div class="space-y-4">
                @foreach ($usersByRole as $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $item->role === 'super_admin' ? 'bg-amber-400' : ($item->role === 'admin' ? 'bg-blue-400' : 'bg-gray-400') }}"></div>
                            <span class="text-sm capitalize text-gray-700">{{ str_replace('_', ' ', $item->role) }}</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ $item->total }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $item->role === 'super_admin' ? 'bg-amber-400' : ($item->role === 'admin' ? 'bg-blue-400' : 'bg-gray-400') }}" style="width: {{ $totalUsers > 0 ? ($item->total / $totalUsers) * 100 : 0 }}%"></div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
