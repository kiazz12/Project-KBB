@extends('layouts.app')

@section('title', $user->name)

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
                        <a href="{{ route('users.index') }}" class="text-kbb-200 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Detail User</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $user->name }}</h1>
                    <p class="text-kbb-200/80 mt-1 text-sm">{{ $user->email }} · {{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Info User</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Email</dt><dd class="text-gray-900 dark:text-gray-200">{{ $user->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Role</dt><dd class="text-gray-900 dark:text-gray-200">{{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">OPD</dt><dd class="text-gray-900 dark:text-gray-200">{{ $user->opd ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">NIP</dt><dd class="text-gray-900 dark:text-gray-200">{{ $user->nip ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Total Forms</dt><dd class="text-gray-900 dark:text-gray-200">{{ $user->forms_count }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="lg:col-span-3">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Forms oleh {{ $user->name }}</h2>
            @if($forms->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">User ini belum membuat form.</p>
            @else
                <div class="space-y-3">
                    @foreach($forms as $form)
                        <a href="{{ route('forms.show', $form) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $form->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $form->submissions_count }} pengiriman · {{ $form->fields_count }} field</p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $form->status->value === 'published' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : ($form->status->value === 'closed' ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400' : 'bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-gray-400') }}">
                                {{ $form->status }}
                            </span>
                        </a>
                    @endforeach
                </div>
                @if ($forms->hasPages())
                    <div class="mt-4">{{ $forms->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
