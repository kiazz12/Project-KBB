@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <div class="bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] rounded-2xl p-8 text-white relative overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/[0.03] rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-1/3 w-96 h-96 bg-white/[0.02] rounded-full translate-y-1/2"></div>
        <div class="absolute top-1/2 left-10 w-4 h-4 bg-white/10 rounded-full"></div>
        <div class="absolute top-20 right-20 w-2 h-2 bg-white/10 rounded-full"></div>
        <div class="absolute bottom-16 right-1/4 w-3 h-3 bg-white/10 rounded-full"></div>
        <svg class="absolute right-0 top-0 h-full opacity-[0.04]" viewBox="0 0 400 200" fill="white">
            <path d="M0 100 Q 50 20 100 100 T 200 100 T 300 100 T 400 100 L 400 200 L 0 200 Z"/>
        </svg>
        <div class="relative">
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Dashboard</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Dashboard Admin</h1>
                    <p class="text-kbb-200/80 mt-1 text-sm">Overview sistem Formulir Online KBB</p>
                </div>
                <div class="text-sm text-white/60 mt-1">{{ now()->format('d F Y') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="group bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Users</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1.5">{{ $totalUsers }}</p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:shadow-md transition-all duration-200">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="group bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Forms</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1.5">{{ $totalForms }}</p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:shadow-md transition-all duration-200">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>
    <div class="group bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Published</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1.5">{{ $totalPublished }}</p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:shadow-md transition-all duration-200">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="group bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Submissions</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1.5">{{ $totalSubmissions }}</p>
                <p class="text-xs text-gray-400 mt-1.5">{{ $submissionsToday }} hari ini</p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:shadow-md transition-all duration-200">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="lg:col-span-2">
        @livewire('activity-chart')
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5">Forms Terbaru</h2>
        @if ($recentForms->isEmpty())
            <p class="text-sm text-gray-400 text-center py-10">Belum ada form.</p>
        @else
            <div class="space-y-1.5">
                @foreach ($recentForms as $form)
                    <a href="{{ route('admin.forms.show', $form) }}" class="flex items-center justify-between p-3.5 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-all group">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-kbb-50 to-kbb-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-kbb-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate group-hover:text-kbb-700 transition-colors">{{ $form->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    <span class="font-medium text-gray-600 dark:text-gray-300">{{ $form->submissions_count }}</span> pengiriman
                                    <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                                    {{ $form->user?->name ?? 'Unknown' }}
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-semibold flex-shrink-0 {{ $form->status->value === 'published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : ($form->status->value === 'closed' ? 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400' : 'bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-gray-300') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $form->status->value === 'published' ? 'bg-emerald-500' : ($form->status->value === 'closed' ? 'bg-red-500' : 'bg-gray-400') }}"></span>
                            {{ $form->status }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5">Users by Role</h2>
        @if ($usersByRole->isEmpty())
            <p class="text-sm text-gray-400 text-center py-10">Tidak ada data.</p>
        @else
            <div class="space-y-5">
                @foreach ($usersByRole as $item)
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                @php $roleVal = $item->role instanceof \App\Enums\UserRole ? $item->role->value : $item->role; @endphp
                                <div class="w-2 h-2 rounded-full {{ $roleVal === 'super_admin' ? 'bg-amber-500' : 'bg-blue-500' }}"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">{{ str_replace('_', ' ', $roleVal) }}</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $item->total }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden shadow-inner">
                            <div class="h-full rounded-full transition-all duration-500 {{ $roleVal === 'super_admin' ? 'bg-gradient-to-r from-amber-400 to-amber-300' : 'bg-gradient-to-r from-blue-500 to-blue-400' }}" style="width: {{ $totalUsers > 0 ? ($item->total / $totalUsers) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
