@extends('layouts.app')

@section('title', 'Detail Submission — ' . $form->title)

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
                        <a href="{{ route('forms.submissions.index', $form) }}" class="text-kbb-200 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Detail Submission</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $form->title }}</h1>
                    <p class="text-kbb-200/80 mt-1 text-sm">
                        #{{ $submission->uuid ? substr($submission->uuid, 0, 8) : $submission->id }}
                        · {{ $submission->submitted_at?->format('d/m/Y H:i') ?: $submission->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <form action="{{ route('forms.submissions.delete', [$form, $submission]) }}" method="POST" onsubmit="return confirm('Hapus submission ini? Data tidak dapat dikembalikan.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-red-500/20 hover:bg-red-500/30 text-white text-sm font-medium px-5 py-2.5 rounded-xl backdrop-blur-sm border border-red-400/20 transition-all hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Responses</h2>
            <div class="space-y-4">
                @foreach($submission->data as $data)
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $data->formField?->label ?? 'Field #' . $data->form_field_id }}</p>
                        <p class="text-sm text-gray-900 dark:text-gray-100 mt-1 bg-gray-50 dark:bg-slate-900/60 rounded-xl p-3">{{ strip_tags($data->value) ?: '(empty)' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">UUID</dt><dd class="text-gray-900 dark:text-gray-200 text-xs font-mono">{{ $submission->uuid ?: 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Dikirim</dt><dd class="text-gray-900 dark:text-gray-200">{{ $submission->submitted_at?->format('d/m/Y H:i') ?: $submission->created_at->format('d/m/Y H:i') }}</dd></div>
                @if($submission->ip_address)<div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">IP</dt><dd class="text-gray-900 dark:text-gray-200 text-xs">{{ $submission->ip_address }}</dd></div>@endif
            </dl>
        </div>
    </div>
</div>
@endsection
