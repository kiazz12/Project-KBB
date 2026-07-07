@extends('layouts.app')

@section('title', $form->title)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('forms.index') }}" class="text-gray-400 hover:text-gray-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">{{ $form->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">
            <span class="{{ $form->status === 'published' ? 'text-emerald-600' : ($form->status === 'closed' ? 'text-red-500' : 'text-gray-400') }}">{{ $form->status }}</span>
            · {{ $form->fields_count }} fields · {{ $form->submissions_count }} responses
            @if($form->sections_count > 0) · {{ $form->sections_count }} sections @endif
        </p>
    </div>
    <div class="flex items-center gap-2">
        @if($form->status === 'published')
            <a href="{{ url('/form/' . $form->slug) }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg transition">Public Link</a>
        @endif
        @if($form->trashed())
            <span class="text-xs text-red-500">(Deleted)</span>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Fields</h2>
                <span class="text-sm text-gray-400">{{ $form->fields->count() }} field</span>
            </div>
            @if($form->fields->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">
                    Belum ada field. <a href="{{ route('forms.edit', $form) }}" class="text-kbb-700 hover:underline">Tambahkan field</a>
                </p>
            @else
                <div class="space-y-2">
                    @foreach($form->fields as $field)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                            <span class="text-xs font-mono text-kbb-600 w-16">{{ $field->type->value }}</span>
                            <span class="text-sm font-medium text-gray-900 flex-1">{{ $field->label }}</span>
                            @if($field->required)<span class="text-xs text-red-500">*wajib</span>@endif
                            <span class="text-xs text-gray-400">Urutan {{ $field->order }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Aksi</h3>
            <div class="space-y-2">
                <a href="{{ route('forms.edit', $form) }}" class="block w-full text-center bg-kbb-700 hover:bg-kbb-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Edit Form</a>
                <a href="{{ route('forms.submissions.index', $form) }}" class="block w-full text-center bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">Lihat Data</a>
                <a href="{{ route('forms.analytics', $form) }}" class="block w-full text-center bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">Analytics</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Slug</dt><dd class="text-gray-900 text-right text-xs">/form/{{ $form->slug }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Batas</dt><dd class="text-gray-900">{{ $form->max_submissions ?: 'Tak terbatas' }}</dd></div>
                @if($form->ends_at)<div class="flex justify-between"><dt class="text-gray-500">Berakhir</dt><dd class="text-gray-900">{{ $form->ends_at->format('d/m/Y') }}</dd></div>@endif
            </dl>
        </div>
    </div>
</div>
@endsection
