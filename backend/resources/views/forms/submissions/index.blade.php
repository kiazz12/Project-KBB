@extends('layouts.app')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('forms.show', $form) }}" class="text-gray-400 hover:text-gray-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Data: {{ $form->title }}</h1>
</div>

<div class="flex gap-2 mb-6">
    <a href="{{ url('/api/v1/forms/' . $form->id . '/export/csv') }}" class="bg-kbb-700 hover:bg-kbb-800 text-white text-sm px-4 py-2 rounded-lg transition">Export CSV</a>
    <a href="{{ url('/api/v1/forms/' . $form->id . '/export/pdf') }}" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition">Export PDF</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    @if($submissions->isEmpty())
        <p class="text-sm text-gray-400 text-center py-12">Belum ada pengiriman.</p>
    @else
        @foreach($submissions as $submission)
            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                <div class="flex items-center justify-between mb-2">
                    <a href="{{ route('forms.submissions.show', [$form, $submission]) }}" class="text-sm font-medium text-kbb-700 hover:underline">
                        {{ $submission->uuid ?: '#' . $submission->id }}
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
