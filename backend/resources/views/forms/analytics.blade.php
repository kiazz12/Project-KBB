@extends('layouts.app')

@section('title', 'Analytics — ' . $form->title)

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
                        <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Analytics</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $form->title }}</h1>
                    <p class="text-kbb-200/80 mt-1 text-sm">{{ $totalSubmissions }} total responses</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Responses</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $totalSubmissions }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
        <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1 capitalize">{{ $form->status }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
        <p class="text-sm text-gray-500 dark:text-gray-400">Fields</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $form->fields->count() }}</p>
    </div>
</div>

@if($submissionsByDate->isNotEmpty())
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Submissions per Day</h2>
    <div class="space-y-2">
        @foreach($submissionsByDate as $item)
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 dark:text-gray-400 w-24">{{ \Carbon\Carbon::parse($item->date)->format('d/m') }}</span>
                <div class="flex-1 bg-gray-100 dark:bg-slate-700 rounded-full h-4 overflow-hidden">
                    @php $max = $submissionsByDate->max('count'); @endphp
                    <div class="h-full bg-kbb-600 rounded-full" style="width: {{ $max > 0 ? ($item->count / $max) * 100 : 0 }}%"></div>
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 w-8 text-right">{{ $item->count }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif

@foreach($fieldAnalytics as $fa)
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5 mb-4">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ $fa['field_label'] }} <span class="text-gray-400 font-normal">({{ $fa['field_type'] }})</span></h3>
    @if(empty($fa['counts']))
        <p class="text-sm text-gray-400">Belum ada data.</p>
    @else
        <div class="space-y-2">
            @php $maxCount = max($fa['counts']); @endphp
            @foreach($fa['counts'] as $value => $count)
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-700 dark:text-gray-300 w-48 truncate">{{ strip_tags($value) ?: '(empty)' }}</span>
                    <div class="flex-1 bg-gray-100 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                        <div class="h-full bg-gold-400 rounded-full" style="width: {{ $maxCount > 0 ? ($count / $maxCount) * 100 : 0 }}%"></div>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 w-8 text-right">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endforeach
@endsection
