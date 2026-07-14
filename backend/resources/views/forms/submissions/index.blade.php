@extends('layouts.app')

@section('title', 'Data — ' . $form->title)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('forms.show', $form) }}" class="text-gray-400 hover:text-gray-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Data: {{ $form->title }}</h1>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <form method="GET" class="flex items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari submission..."
            class="w-64 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition">
        <button type="submit" class="bg-kbb-700 hover:bg-kbb-800 text-white text-sm px-4 py-2 rounded-lg transition">Cari</button>
        @if(request('search'))
            <a href="{{ route('forms.submissions.index', $form) }}" class="text-sm text-gray-500 hover:text-gray-700">Reset</a>
        @endif
    </form>
    <div class="flex gap-2">
        <a href="{{ route('forms.export.csv', $form) }}" class="bg-kbb-700 hover:bg-kbb-800 text-white text-sm px-4 py-2 rounded-lg transition">Export CSV</a>
        <a href="{{ route('forms.export.pdf', $form) }}" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition">Export PDF</a>
        @if(str_contains($form->slug, 'uang-saku'))
            <a href="{{ route('forms.export.uang-saku', $form) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg transition">Download Tanda Terima</a>
        @endif
        @if(str_contains($form->slug, 'presensi') || str_contains($form->slug, 'transfer-knowledge'))
            <a href="{{ route('forms.export.presensi', $form) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg transition">Download Daftar Hadir</a>
        @endif
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    @if($submissions->isEmpty())
        <div class="py-16 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm text-gray-400">Belum ada pengiriman.</p>
        </div>
    @else
        @foreach($submissions as $submission)
            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                <div class="flex items-center justify-between mb-2">
                    <a href="{{ route('forms.submissions.show', [$form, $submission]) }}" class="text-sm font-medium text-kbb-700 hover:underline">
                        {{ $submission->uuid ? '#' . substr($submission->uuid, 0, 8) : '#' . $submission->id }}
                    </a>
                    <span class="text-xs text-gray-400">{{ $submission->submitted_at?->format('d/m/Y H:i') ?: $submission->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @foreach($submission->data->take(3) as $data)
                    <div class="text-xs text-gray-500 truncate">
                        <span class="text-gray-400">{{ $data->formField?->label ?? 'Field' }}:</span> {{ Str::limit(strip_tags($data->value), 80) }}
                    </div>
                @endforeach
                @if($submission->data->count() > 3)
                    <p class="text-xs text-gray-400 mt-1">+{{ $submission->data->count() - 3 }} field lainnya</p>
                @endif
            </div>
        @endforeach
        @if ($submissions->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $submissions->links() }}</div>
        @endif
    @endif
</div>
@endsection
