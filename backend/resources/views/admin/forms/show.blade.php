@extends('admin.layouts.app')

@section('title', $form->title)

@section('content')
<div class="mb-8">
    <div class="bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] rounded-2xl p-8 text-white relative overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/[0.03] rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-1/3 w-96 h-96 bg-white/[0.02] rounded-full translate-y-1/2"></div>
        <div class="absolute top-1/2 left-10 w-4 h-4 bg-white/10 rounded-full"></div>
        <div class="absolute top-20 right-20 w-2 h-2 bg-white/10 rounded-full"></div>
        <svg class="absolute right-0 top-0 h-full opacity-[0.04]" viewBox="0 0 400 200" fill="white">
            <path d="M0 100 Q 50 20 100 100 T 200 100 T 300 100 T 400 100 L 400 200 L 0 200 Z"/>
        </svg>
        <div class="relative">
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <a href="{{ route('admin.forms.index') }}" class="text-kbb-200 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Detail Form</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $form->title }}</h1>
                    <p class="text-kbb-200/80 mt-1 text-sm">
                        {{ $form->user?->name ?? 'Deleted' }} ·
                        <span class="{{ $form->status->value === 'published' ? 'text-emerald-300' : ($form->status->value === 'closed' ? 'text-red-300' : 'text-kbb-300') }}">{{ $form->status }}</span> ·
                        {{ $form->submissions_count }} pengiriman
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pengiriman</h2>
            @if ($submissions->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">Belum ada pengiriman.</p>
            @else
                @foreach ($submissions as $submission)
                    <div class="p-4 border border-gray-100 dark:border-slate-700 rounded-xl mb-3 bg-gray-50/50 dark:bg-slate-900/40">
                        <p class="text-xs text-gray-400 mb-2">{{ $submission->created_at->format('d/m/Y H:i') }}</p>
                        @foreach ($submission->data as $data)
                            <div class="text-sm mb-1">
                                <span class="text-gray-500 dark:text-gray-400">{{ $data->formField?->label ?? 'Field' }}:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ Str::limit(strip_tags($data->value), 100) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @if ($submissions->hasPages())
                    <div class="mt-4">{{ $submissions->links() }}</div>
                @endif
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Info Form</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Deskripsi</dt><dd class="text-gray-900 dark:text-gray-100 text-right max-w-[150px] truncate">{{ $form->description ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Slug</dt><dd class="text-gray-900 dark:text-gray-100">/form/{{ $form->slug }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Batas</dt><dd class="text-gray-900 dark:text-gray-100">{{ $form->max_submissions ?: 'Tak terbatas' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Batas Akhir</dt><dd class="text-gray-900 dark:text-gray-100">{{ $form->closed_at?->format('d/m/Y') ?? '-' }}</dd></div>
            </dl>
        </div>
    </div>
</div>
@endsection
