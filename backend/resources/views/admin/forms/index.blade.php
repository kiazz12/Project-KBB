@extends('admin.layouts.app')

@section('title', 'Forms')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Forms</h1>
        <p class="text-sm text-gray-500 mt-1">Semua form dari seluruh OPD</p>
    </div>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari form..."
        class="flex-1 min-w-[200px] px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
    <select name="status" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
        <option value="">Semua Status</option>
        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
    </select>
    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm transition">Filter</button>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-gray-500 text-left">
                <th class="px-5 py-3 font-medium">Judul</th>
                <th class="px-5 py-3 font-medium">Pembuat</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Pengiriman</th>
                <th class="px-5 py-3 font-medium">Dibuat</th>
                <th class="px-5 py-3 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($forms as $form)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4 font-medium text-gray-900 max-w-[200px] truncate">{{ $form->title }}</td>
                    <td class="px-5 py-4 text-gray-500">{{ $form->user?->name ?? 'Deleted' }}</td>
                    <td class="px-5 py-4">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $form->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($form->status === 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                            {{ $form->status }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-gray-500">{{ $form->submissions_count }}</td>
                    <td class="px-5 py-4 text-gray-400">{{ $form->created_at->format('d/m/Y') }}</td>
                    <td class="px-5 py-4 text-right">
                        <a href="{{ route('admin.forms.show', $form) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">Tidak ada form.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if ($forms->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $forms->links() }}</div>
    @endif
</div>
@endsection
