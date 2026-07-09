@extends('layouts.app')

@section('title', $user->name)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Info User</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="text-gray-900">{{ $user->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Role</dt><dd class="text-gray-900">{{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">OPD</dt><dd class="text-gray-900">{{ $user->opd ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">NIP</dt><dd class="text-gray-900">{{ $user->nip ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total Forms</dt><dd class="text-gray-900">{{ $user->forms_count }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Forms oleh {{ $user->name }}</h2>
            @if($forms->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">User ini belum membuat form.</p>
            @else
                <div class="space-y-3">
                    @foreach($forms as $form)
                        <a href="{{ route('forms.show', $form) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $form->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $form->submissions_count }} pengiriman · {{ $form->fields_count }} field</p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $form->status->value === 'published' ? 'bg-emerald-100 text-emerald-700' : ($form->status->value === 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
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
