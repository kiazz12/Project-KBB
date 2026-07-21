@extends('layouts.app')

@section('title', 'Data — ' . $form->title)

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
                        <a href="{{ route('forms.show', $form) }}" class="text-kbb-200 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Data Pengiriman</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $form->title }}</h1>
                    <p class="text-kbb-200/80 mt-1 text-sm">{{ $submissions->total() }} total pengiriman</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('forms.export.csv', $form) }}" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-4 py-2.5 rounded-xl backdrop-blur-sm border border-white/10 transition-all hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        CSV
                    </a>
                    <a href="{{ route('forms.export.xlsx', $form) }}" class="inline-flex items-center gap-2 bg-emerald-500/20 hover:bg-emerald-500/30 text-white text-sm font-medium px-4 py-2.5 rounded-xl backdrop-blur-sm border border-emerald-400/20 transition-all hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </a>
                    <a href="{{ route('forms.export.pdf', $form) }}" class="inline-flex items-center gap-2 bg-red-500/20 hover:bg-red-500/30 text-white text-sm font-medium px-4 py-2.5 rounded-xl backdrop-blur-sm border border-red-400/20 transition-all hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari submission..."
            class="w-56 px-4 py-2 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition text-gray-900 dark:text-gray-100">
        <input type="date" name="from" value="{{ request('from') }}"
            class="px-3 py-2 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-kbb-500 outline-none transition text-gray-900 dark:text-gray-100" title="Dari tanggal">
        <input type="date" name="to" value="{{ request('to') }}"
            class="px-3 py-2 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-kbb-500 outline-none transition text-gray-900 dark:text-gray-100" title="Sampai tanggal">
        <button type="submit" class="bg-kbb-700 hover:bg-kbb-800 text-white text-sm px-4 py-2 rounded-xl transition">Filter</button>
        @if(request()->anyFilled(['search', 'from', 'to']))
            <a href="{{ route('forms.submissions.index', $form) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Reset</a>
        @endif
    </form>
</div>

<form id="bulk-form" method="POST" action="{{ route('forms.submissions.bulk-delete', $form) }}">
    @csrf
    @if(!$submissions->isEmpty())
    <div class="flex items-center gap-3 mb-3">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            <input type="checkbox" id="select-all" class="rounded border-gray-300 dark:border-slate-600 text-kbb-700 focus:ring-kbb-500">
            Pilih semua di halaman
        </label>
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-xl transition"
            onclick="return confirm('Hapus submission terpilih?')">Hapus Terpilih</button>
    </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
        @if($submissions->isEmpty())
            <x-empty-state title="Belum ada pengiriman" />
        @else
            @foreach($submissions as $submission)
                <div class="flex items-start gap-3 p-4 border-b border-gray-100 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/40 transition">
                    <input type="checkbox" name="ids[]" value="{{ $submission->id }}" class="row-check mt-1 rounded border-gray-300 dark:border-slate-600 text-kbb-700 focus:ring-kbb-500">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <a href="{{ route('forms.submissions.show', [$form, $submission]) }}" class="text-sm font-medium text-kbb-700 dark:text-kbb-400 hover:underline">
                                {{ $submission->uuid ? '#' . substr($submission->uuid, 0, 8) : '#' . $submission->id }}
                            </a>
                            <span class="text-xs text-gray-400">{{ $submission->submitted_at?->format('d/m/Y H:i') ?: $submission->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @foreach($submission->data->take(3) as $data)
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                <span class="text-gray-400">{{ $data->formField?->label ?? 'Field' }}:</span> {{ Str::limit(strip_tags($data->value), 80) }}
                            </div>
                        @endforeach
                        @if($submission->data->count() > 3)
                            <p class="text-xs text-gray-400 mt-1">+{{ $submission->data->count() - 3 }} field lainnya</p>
                        @endif
                    </div>
                </div>
            @endforeach
            @if ($submissions->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-slate-700">{{ $submissions->links() }}</div>
            @endif
        @endif
    </div>
</form>

@push('scripts')
<script>
    document.getElementById('select-all')?.addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
@endsection
