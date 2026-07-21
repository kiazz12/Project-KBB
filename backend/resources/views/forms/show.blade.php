@extends('layouts.app')

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
                        <a href="{{ route('forms.index') }}" class="text-kbb-200 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Detail Form</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $form->title }}</h1>
                    <p class="text-kbb-200/80 mt-1 text-sm">
                        <span class="{{ $form->status->value === 'published' ? 'text-emerald-300' : ($form->status->value === 'closed' ? 'text-red-300' : 'text-kbb-300') }}">{{ $form->status }}</span>
                        · {{ $form->fields_count }} fields · {{ $form->submissions_count }} responses
                        @if($form->sections_count > 0) · {{ $form->sections_count }} sections @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($form->status->value === 'published')
                        <a href="{{ url('/form/' . $form->slug) }}" target="_blank" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-5 py-2.5 rounded-xl backdrop-blur-sm border border-white/10 transition-all hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Public Link
                        </a>
                    @endif
                    @if($form->trashed())
                        <span class="text-xs text-red-300">(Deleted)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3 space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Fields</h2>
                <span class="text-sm text-gray-400">{{ $form->fields->count() }} field</span>
            </div>
            @if($form->fields->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">
                    Belum ada field. <a href="{{ route('forms.edit', $form) }}" class="text-kbb-700 dark:text-kbb-400 hover:underline">Tambahkan field</a>
                </p>
            @else
                <div class="space-y-2">
                    @foreach($form->fields as $field)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-slate-900/60">
                            <span class="text-xs font-mono text-kbb-600 dark:text-kbb-400 w-16">{{ $field->type->value }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100 flex-1">{{ $field->label }}</span>
                            @if($field->required)<span class="text-xs text-red-500">*wajib</span>@endif
                            <span class="text-xs text-gray-400">Urutan {{ $field->order }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Aksi</h3>
            <div class="space-y-2">
                <a href="{{ route('forms.edit', $form) }}" class="block w-full text-center bg-kbb-700 hover:bg-kbb-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Edit Form</a>
                <form method="POST" action="{{ route('forms.duplicate', $form) }}" onsubmit="return confirm('Gandakan form ini sebagai draft?')">
                    @csrf
                    <button type="submit" class="block w-full text-center bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-200 text-sm font-medium px-4 py-2 rounded-lg transition">Gandakan Form</button>
                </form>
                <a href="{{ route('forms.submissions.index', $form) }}" class="block w-full text-center bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-200 text-sm font-medium px-4 py-2 rounded-lg transition">Lihat Data</a>
                <a href="{{ route('forms.analytics', $form) }}" class="block w-full text-center bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-200 text-sm font-medium px-4 py-2 rounded-lg transition">Analytics</a>
                <hr class="border-gray-200 dark:border-slate-700">
                <a href="{{ route('forms.export.csv', $form) }}" class="block w-full text-center bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-200 text-sm font-medium px-4 py-2 rounded-lg transition">Download CSV</a>
                <a href="{{ route('forms.export.pdf', $form) }}" class="block w-full text-center bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-200 text-sm font-medium px-4 py-2 rounded-lg transition">Download PDF</a>
                @if(str_contains($form->slug, 'uang-saku'))
                    <a href="{{ route('forms.export.uang-saku', $form) }}" class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Download Tanda Terima</a>
                @endif
                @if(str_contains($form->slug, 'presensi') || str_contains($form->slug, 'transfer-knowledge'))
                    <a href="{{ route('forms.export.presensi', $form) }}" class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Download Daftar Hadir</a>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Slug</dt><dd class="text-gray-900 dark:text-gray-100 text-right text-xs">/form/{{ $form->slug }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Batas</dt><dd class="text-gray-900 dark:text-gray-100">{{ $form->max_submissions ?: 'Tak terbatas' }}</dd></div>
                @if($form->ends_at)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Berakhir</dt><dd class="text-gray-900 dark:text-gray-100">{{ $form->ends_at->format('d/m/Y') }}</dd></div>@endif
            </dl>
        </div>
    </div>
</div>
@endsection
