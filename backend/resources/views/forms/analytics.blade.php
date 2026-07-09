@extends('layouts.app')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('forms.show', $form) }}" class="text-gray-400 hover:text-gray-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Analytics: {{ $form->title }}</h1>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Total Responses</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalSubmissions }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Status</p>
        <p class="text-3xl font-bold text-gray-900 mt-1 capitalize">{{ $form->status }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Fields</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $form->fields->count() }}</p>
    </div>
</div>

@if($submissionsByDate->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Submissions per Day</h2>
    <div class="space-y-2">
        @foreach($submissionsByDate as $item)
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 w-24">{{ \Carbon\Carbon::parse($item->date)->format('d/m') }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                    @php $max = $submissionsByDate->max('count'); @endphp
                    <div class="h-full bg-kbb-600 rounded-full" style="width: {{ $max > 0 ? ($item->count / $max) * 100 : 0 }}%"></div>
                </div>
                <span class="text-sm font-semibold text-gray-700 w-8 text-right">{{ $item->count }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif

@foreach($fieldAnalytics as $fa)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ $fa['field_label'] }} <span class="text-gray-400 font-normal">({{ $fa['field_type'] }})</span></h3>
    @if(empty($fa['counts']))
        <p class="text-sm text-gray-400">Belum ada data.</p>
    @else
        <div class="space-y-2">
            @php $maxCount = max($fa['counts']); @endphp
            @foreach($fa['counts'] as $value => $count)
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-700 w-48 truncate">{{ strip_tags($value) ?: '(empty)' }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="h-full bg-gold-400 rounded-full" style="width: {{ $maxCount > 0 ? ($count / $maxCount) * 100 : 0 }}%"></div>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 w-8 text-right">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endforeach
@endsection
