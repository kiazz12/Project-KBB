@extends('admin.layouts.app')

@section('title', 'Forms')

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
            <div class="flex items-center gap-2 mb-2">
                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Forms</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Forms</h1>
            <p class="text-kbb-200/80 mt-1 text-sm">Semua form dari seluruh OPD</p>
        </div>
    </div>
</div>

<form method="GET" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
    <div class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-[200px] relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari form..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none text-sm transition">
        </div>
        <select name="status" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-kbb-500 outline-none text-sm bg-white">
            <option value="">Semua Status</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
        </select>
        <button type="submit" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filter
        </button>
        @if(request()->anyFilled(['search', 'status']))
            <a href="{{ route('admin.forms.index') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 px-4 py-2.5 text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Reset
            </a>
        @endif
    </div>
</form>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 text-gray-500 text-left border-b border-gray-100">
                    <th class="px-5 py-3.5 font-semibold">Judul</th>
                    <th class="px-5 py-3.5 font-semibold">Pembuat</th>
                    <th class="px-5 py-3.5 font-semibold">Status</th>
                    <th class="px-5 py-3.5 font-semibold text-center">Pengiriman</th>
                    <th class="px-5 py-3.5 font-semibold">Dibuat</th>
                    <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($forms as $form)
                    <tr class="hover:bg-gray-50/80 transition-all group">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-kbb-50 to-kbb-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-kbb-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 truncate max-w-[200px]">{{ $form->title }}</p>
                                    @if($form->description)
                                        <p class="text-xs text-gray-400 truncate max-w-[200px]">{{ $form->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-sm text-gray-500">{{ $form->user?->name ?? 'Deleted' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-semibold {{ $form->status === 'published' ? 'bg-emerald-50 text-emerald-700' : ($form->status === 'closed' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $form->status === 'published' ? 'bg-emerald-500' : ($form->status === 'closed' ? 'bg-red-500' : 'bg-gray-400') }}"></span>
                                {{ $form->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="text-sm font-semibold text-gray-900">{{ $form->submissions_count }}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-400 text-sm">{{ $form->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.forms.show', $form) }}"
                               class="inline-flex items-center gap-1.5 text-kbb-700 hover:text-kbb-800 text-sm font-medium px-3 py-1.5 rounded-lg hover:bg-kbb-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-sm text-gray-400">Tidak ada form.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($forms->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $forms->links() }}
        </div>
    @endif
</div>
@endsection
