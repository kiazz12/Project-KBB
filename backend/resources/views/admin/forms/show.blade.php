@extends('admin.layouts.app')

@section('title', $form->title)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.forms.index') }}" class="text-gray-400 hover:text-gray-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $form->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $form->user?->name ?? 'Deleted' }} ·
            <span class="{{ $form->status->value === 'published' ? 'text-emerald-600' : ($form->status->value === 'closed' ? 'text-red-500' : 'text-gray-400') }}">{{ $form->status }}</span> ·
            {{ $form->submissions_count }} pengiriman
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Pengiriman</h2>
            @if ($submissions->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">Belum ada pengiriman.</p>
            @else
                @foreach ($submissions as $submission)
                    <div class="p-4 border border-gray-100 rounded-lg mb-3">
                        <p class="text-xs text-gray-400 mb-2">{{ $submission->created_at->format('d/m/Y H:i') }}</p>
                        @foreach ($submission->data as $data)
                            <div class="text-sm mb-1">
                                <span class="text-gray-500">{{ $data->formField?->label ?? 'Field' }}:</span>
                                <span class="text-gray-900">{{ Str::limit(strip_tags($data->value), 100) }}</span>
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Info Form</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Deskripsi</dt><dd class="text-gray-900 text-right max-w-[150px] truncate">{{ $form->description ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Slug</dt><dd class="text-gray-900">/form/{{ $form->slug }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Batas</dt><dd class="text-gray-900">{{ $form->max_submissions ?: 'Tak terbatas' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Batas Akhir</dt><dd class="text-gray-900">{{ $form->closed_at?->format('d/m/Y') ?? '-' }}</dd></div>
            </dl>
        </div>
    </div>
</div>
@endsection
