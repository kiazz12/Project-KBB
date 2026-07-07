@extends('layouts.app')

@section('content')
<div class="mb-8">
    <div class="bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] rounded-2xl p-8 text-white relative overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/[0.03] rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-1/3 w-96 h-96 bg-white/[0.02] rounded-full translate-y-1/2"></div>
        <div class="absolute top-1/2 left-10 w-4 h-4 bg-white/10 rounded-full"></div>
        <div class="absolute top-20 right-20 w-2 h-2 bg-white/10 rounded-full"></div>
        <div class="absolute bottom-16 right-1/4 w-3 h-3 bg-white/10 rounded-full"></div>
        <svg class="absolute right-0 top-0 h-full opacity-[0.04]" viewBox="0 0 400 200" fill="white">
            <path d="M0 100 Q 50 20 100 100 T 200 100 T 300 100 T 400 100 L 400 200 L 0 200 Z"/>
        </svg>
        <div class="relative">
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Dashboard</span>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight">Selamat datang, {{ auth()->user()->name }}</h1>
                    <p class="text-kbb-200/80 mt-1.5 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Admin' }}
                        <span class="text-kbb-200/40">&bull;</span>
                        {{ now()->translatedFormat('l, d F Y') }}
                    </p>
                </div>
                <a href="{{ route('forms.create') }}"
                   class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-5 py-2.5 rounded-xl backdrop-blur-sm border border-white/10 transition-all hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Form Baru
                </a>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Forms</p>
                <p class="text-3xl font-bold text-gray-900 mt-1.5">{{ $totalForms }}</p>
                <div class="flex items-center gap-3 mt-3 text-xs">
                    <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ $publishedForms }} published
                    </span>
                    <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                        {{ $draftForms }} draft
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-kbb-50 to-kbb-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:shadow-md transition-all duration-200">
                <svg class="w-6 h-6 text-kbb-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>

    <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Respons</p>
                <p class="text-3xl font-bold text-gray-900 mt-1.5">{{ $totalSubmissions }}</p>
                <div class="flex items-center gap-3 mt-3 text-xs">
                    <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        {{ $submissionsToday }} hari ini
                    </span>
                    <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-kbb-50 text-kbb-700 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $submissionsThisWeek }} minggu ini
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:shadow-md transition-all duration-200">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            </div>
        </div>
    </div>

    <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Published</p>
                <p class="text-3xl font-bold text-gray-900 mt-1.5">{{ $publishedForms }}</p>
                <div class="mt-3 text-xs space-y-1">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        {{ $totalForms > 0 ? round(($publishedForms / max($totalForms, 1)) * 100) : 0 }}% dari total
                    </span>
                    <p class="text-gray-400 px-1 mt-1">{{ $avgSubsPerForm }} rata-rata/form</p>
                </div>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:shadow-md transition-all duration-200">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    @if(auth()->user()->isSuperAdmin())
    <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pengguna</p>
                <p class="text-3xl font-bold text-gray-900 mt-1.5">{{ $totalUsers }}</p>
                <div class="mt-3 text-xs space-y-1">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ \App\Models\User::where('role', 'admin')->count() }} admin
                    </span>
                </div>
                <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1 text-xs text-kbb-700 hover:text-kbb-800 font-medium mt-1.5 px-1">
                    Kelola Pengguna
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:shadow-md transition-all duration-200">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
    </div>
    @else
    <a href="{{ route('forms.create') }}"
       class="group bg-white rounded-2xl shadow-sm border-2 border-dashed border-gray-200 p-6 flex items-center justify-center hover:border-kbb-400 hover:bg-kbb-50/30 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
        <div class="text-center">
            <div class="w-12 h-12 mx-auto rounded-xl bg-gray-100 group-hover:bg-kbb-100 flex items-center justify-center transition-all duration-200 group-hover:scale-110">
                <svg class="w-6 h-6 text-gray-400 group-hover:text-kbb-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-500 group-hover:text-kbb-700 mt-3 transition-colors">Buat Form Baru</p>
        </div>
    </a>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Aktivitas 7 Hari Terakhir</h2>
            @if($submissionsThisWeek > 0)
            <span class="text-xs bg-gradient-to-r from-kbb-50 to-kbb-100 text-kbb-700 px-3 py-1.5 rounded-full font-semibold shadow-sm">{{ $submissionsThisWeek }} total minggu ini</span>
            @endif
        </div>
        @if(max($weekSubmissions) > 0)
        <div class="flex items-end gap-2 h-36">
            @foreach($weekDays as $i => $day)
            <div class="flex-1 flex flex-col items-center gap-2">
                <span class="text-xs font-semibold text-gray-500">{{ $weekSubmissions[$i] }}</span>
                <div class="w-full bg-gray-50 rounded-xl relative overflow-hidden group/chart" style="height: 90px;">
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-kbb-600 via-kbb-500 to-kbb-400 rounded-xl transition-all duration-500 group-hover/chart:from-kbb-700 group-hover/chart:via-kbb-600 group-hover/chart:to-kbb-500"
                         style="height: {{ max($weekSubmissions) > 0 ? ($weekSubmissions[$i] / max($weekSubmissions)) * 100 : 0 }}%">
                        <div class="absolute inset-0 bg-white/10 rounded-xl opacity-0 group-hover/chart:opacity-100 transition-opacity"></div>
                    </div>
                </div>
                <span class="text-xs font-medium text-gray-500">{{ $day }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            </div>
            <p class="text-sm text-gray-400">Belum ada data respons minggu ini</p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Status Form</h2>
        </div>
        @if($totalForms > 0)
        <div class="space-y-5">
            <div>
                <div class="flex items-center justify-between text-sm mb-1.5">
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-gray-600 font-medium">Published</span>
                    </span>
                    <span class="font-bold text-gray-900">{{ $publishedForms }}</span>
                </div>
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full transition-all duration-500" style="width: {{ ($publishedForms / $totalForms) * 100 }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between text-sm mb-1.5">
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        <span class="text-gray-600 font-medium">Draft</span>
                    </span>
                    <span class="font-bold text-gray-900">{{ $draftForms }}</span>
                </div>
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-gray-400 to-gray-300 rounded-full transition-all duration-500" style="width: {{ ($draftForms / $totalForms) * 100 }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between text-sm mb-1.5">
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-400"></span>
                        <span class="text-gray-600 font-medium">Closed</span>
                    </span>
                    <span class="font-bold text-gray-900">{{ $closedForms }}</span>
                </div>
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-red-400 to-red-300 rounded-full transition-all duration-500" style="width: {{ ($closedForms / $totalForms) * 100 }}%"></div>
                </div>
            </div>
            <div class="pt-3 border-t border-gray-100">
                <div class="flex items-center justify-between text-sm mb-1.5">
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-kbb-500"></span>
                        <span class="text-gray-600 font-medium">Form dengan respons</span>
                    </span>
                    <span class="font-bold text-gray-900">{{ $formsWithSubmissions }} / {{ $publishedForms }}</span>
                </div>
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-kbb-600 to-kbb-500 rounded-full transition-all duration-500" style="width: {{ $publishedForms > 0 ? ($formsWithSubmissions / $publishedForms) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-10">
            <p class="text-sm text-gray-400">Belum ada form</p>
        </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Form Terpopuler</h2>
            <a href="{{ route('forms.index') }}" class="text-sm text-kbb-700 hover:text-kbb-800 font-medium inline-flex items-center gap-1">
                Lihat Semua
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        @if ($topForms->isNotEmpty())
        <div class="space-y-1.5">
            @foreach ($topForms as $i => $form)
            <a href="{{ route('forms.show', $form) }}"
               class="flex items-center justify-between p-3.5 rounded-xl hover:bg-gray-50 transition-all group">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0 {{ $i === 0 ? 'bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-sm' : ($i === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-400 text-white shadow-sm' : ($i === 2 ? 'bg-gradient-to-br from-orange-300 to-orange-400 text-white shadow-sm' : 'bg-gray-100 text-gray-500')) }}">
                        {{ $i + 1 }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-kbb-700 transition-colors">{{ $form->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="font-medium text-gray-600">{{ $form->submissions_count }}</span> respons
                            @if(auth()->user()->isSuperAdmin())
                                <span class="text-gray-300">&middot;</span> {{ $form->user?->name ?? 'Unknown' }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 flex-shrink-0 ml-2">
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $form->status === 'published' ? 'bg-emerald-50 text-emerald-700' : ($form->status === 'closed' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                        {{ $form->status }}
                    </span>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-10">
            <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <p class="text-sm text-gray-400">Belum ada respons masuk</p>
            <a href="{{ route('forms.create') }}" class="text-sm text-kbb-700 hover:text-kbb-800 font-medium mt-2 inline-flex items-center gap-1">
                Buat form dan bagikan
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h2>
        </div>
        @if ($latestSubmissions->isNotEmpty())
        <div class="space-y-1.5">
            @foreach ($latestSubmissions as $submission)
            <div class="flex items-start gap-3.5 p-3 rounded-xl hover:bg-gray-50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-kbb-50 to-kbb-100 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4 text-kbb-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-700">
                        Respons baru di
                        <a href="{{ route('forms.submissions.index', $submission->form_id) }}" class="font-semibold text-kbb-700 hover:text-kbb-800">
                            {{ $submission->form->title }}
                        </a>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $submission->submitted_at->translatedFormat('l, d M Y H:i') }}
                        @if(auth()->user()->isSuperAdmin())
                            <span class="text-gray-300">&middot;</span>
                            <span>{{ $submission->form->user->name ?? 'Unknown' }}</span>
                        @endif
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-10">
            <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm text-gray-400">Belum ada aktivitas terbaru</p>
        </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Forms Terbaru</h2>
            <a href="{{ route('forms.index') }}" class="text-sm text-kbb-700 hover:text-kbb-800 font-medium inline-flex items-center gap-1">
                Lihat Semua
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        @if ($recentForms->isNotEmpty())
        <div class="space-y-1.5">
            @foreach ($recentForms as $form)
            <a href="{{ route('forms.show', $form) }}"
               class="flex items-center justify-between p-3.5 rounded-xl hover:bg-gray-50 transition-all group">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-kbb-50 to-kbb-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:shadow-md transition-all">
                        <svg class="w-5 h-5 text-kbb-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-kbb-700 transition-colors">{{ $form->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="font-medium text-gray-600">{{ $form->submissions_count }}</span> respons
                            <span class="text-gray-300">&middot;</span>
                            <span>{{ $form->fields_count }}</span> field
                        </p>
                    </div>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-semibold flex-shrink-0 {{ $form->status === 'published' ? 'bg-emerald-50 text-emerald-700' : ($form->status === 'closed' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                    {{ $form->status }}
                </span>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-10">
            <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-sm text-gray-400">Belum ada form</p>
            <a href="{{ route('forms.create') }}" class="text-sm text-kbb-700 hover:text-kbb-800 font-medium mt-2 inline-flex items-center gap-1">
                Buat form pertama
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        @endif
    </div>

    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-5">Akses Cepat</h2>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('forms.index') }}"
               class="flex flex-col items-center gap-3 p-5 rounded-2xl bg-gradient-to-br from-kbb-50 to-kbb-100/50 hover:from-kbb-100 hover:to-kbb-200/50 transition-all group border border-kbb-100/50 hover:border-kbb-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-kbb-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <span class="text-sm font-semibold text-kbb-800">Semua Form</span>
            </a>
            <a href="{{ route('forms.create') }}"
               class="flex flex-col items-center gap-3 p-5 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100/50 hover:from-emerald-100 hover:to-emerald-200/50 transition-all group border border-emerald-100/50 hover:border-emerald-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-sm font-semibold text-emerald-800">Buat Form</span>
            </a>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('users.index') }}"
               class="flex flex-col items-center gap-3 p-5 rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100/50 hover:from-purple-100 hover:to-purple-200/50 transition-all group border border-purple-100/50 hover:border-purple-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                </div>
                <span class="text-sm font-semibold text-purple-800">Kelola User</span>
            </a>
            <a href="{{ route('admin.dashboard') }}"
               class="flex flex-col items-center gap-3 p-5 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100/50 hover:from-amber-100 hover:to-amber-200/50 transition-all group border border-amber-100/50 hover:border-amber-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="text-sm font-semibold text-amber-800">Admin Panel</span>
            </a>
            @else
            <a href="{{ route('change-password') }}"
               class="flex flex-col items-center gap-3 p-5 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100/50 hover:from-gray-100 hover:to-gray-200/50 transition-all group border border-gray-100/50 hover:border-gray-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-800">Ubah Password</span>
            </a>
            <a href="{{ route('forms.index') }}?status=published"
               class="flex flex-col items-center gap-3 p-5 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100/50 hover:from-emerald-100 hover:to-emerald-200/50 transition-all group border border-emerald-100/50 hover:border-emerald-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="w-11 h-11 rounded-xl bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-sm font-semibold text-emerald-800">Form Aktif</span>
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
