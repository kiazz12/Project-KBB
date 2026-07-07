@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Selamat datang, {{ auth()->user()->name }}</p>
    </div>
    <div class="text-sm text-gray-400">{{ now()->format('d F Y') }}</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Forms</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalForms }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Published</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $publishedForms }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Responses</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalSubmissions }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ $submissionsToday }} hari ini</p>
    </div>
    <a href="{{ route('forms.create') }}" class="bg-white rounded-xl shadow-sm border border-dashed border-gray-300 p-5 flex items-center justify-center hover:border-kbb-500 hover:bg-kbb-50/30 transition group">
        <div class="text-center">
            <svg class="w-8 h-8 mx-auto text-gray-300 group-hover:text-kbb-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <p class="text-sm text-gray-400 group-hover:text-kbb-600 mt-2 font-medium">Buat Form Baru</p>
        </div>
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Forms Terbaru</h2>
        <a href="{{ route('forms.index') }}" class="text-sm text-kbb-700 hover:text-kbb-800 font-medium">Lihat Semua</a>
    </div>
    @if ($recentForms->isEmpty())
        <p class="text-sm text-gray-400 text-center py-8">Belum ada form. <a href="{{ route('forms.create') }}" class="text-kbb-700 hover:underline">Buat form pertama</a></p>
    @else
        <div class="space-y-3">
            @foreach ($recentForms as $form)
                <a href="{{ route('forms.show', $form) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $form->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $form->submissions_count }} pengiriman</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $form->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($form->status === 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                        {{ $form->status }}
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
