@extends('layouts.app')

@section('title', 'Detail Submission — ' . $form->title)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('forms.submissions.index', $form) }}" class="text-gray-400 hover:text-gray-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Detail Submission</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Responses</h2>
            <div class="space-y-4">
                @foreach($submission->data as $data)
                    <div>
                        <p class="text-sm font-medium text-gray-700">{{ $data->formField?->label ?? 'Field #' . $data->form_field_id }}</p>
                        <p class="text-sm text-gray-900 mt-1 bg-gray-50 rounded-lg p-3">{{ $data->value ?: '(empty)' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">UUID</dt><dd class="text-gray-900 text-xs font-mono">{{ $submission->uuid ?: 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Dikirim</dt><dd class="text-gray-900">{{ $submission->submitted_at?->format('d/m/Y H:i') ?: $submission->created_at->format('d/m/Y H:i') }}</dd></div>
                @if($submission->ip_address)<div class="flex justify-between"><dt class="text-gray-500">IP</dt><dd class="text-gray-900 text-xs">{{ $submission->ip_address }}</dd></div>@endif
            </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Aksi</h3>
            <form action="{{ route('forms.submissions.delete', [$form, $submission]) }}" method="POST" onsubmit="return confirm('Hapus submission ini? Data tidak dapat dikembalikan.')">
                @csrf
                <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Submission
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
