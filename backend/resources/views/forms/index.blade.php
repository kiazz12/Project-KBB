@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-sm text-gray-500 mt-1">Kelola form Anda</p>
    </div>
    <a href="{{ route('forms.create') }}" class="bg-kbb-700 hover:bg-kbb-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        + Buat Form
    </a>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari form..."
        class="flex-1 min-w-[200px] px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none text-sm">
    <select name="status" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 outline-none text-sm">
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
                @if(auth()->user()->isSuperAdmin())<th class="px-5 py-3 font-medium">Pembuat</th>@endif
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Fields</th>
                <th class="px-5 py-3 font-medium">Responses</th>
                <th class="px-5 py-3 font-medium">Dibuat</th>
                <th class="px-5 py-3 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($forms as $form)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4 font-medium text-gray-900 max-w-[200px] truncate">{{ $form->title }}</td>
                    @if(auth()->user()->isSuperAdmin())<td class="px-5 py-4 text-gray-500">{{ $form->user?->name ?? 'Deleted' }}</td>@endif
                    <td class="px-5 py-4">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $form->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($form->status === 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                            {{ $form->status }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-gray-500">{{ $form->fields_count }}</td>
                    <td class="px-5 py-4 text-gray-500">{{ $form->submissions_count }}</td>
                    <td class="px-5 py-4 text-gray-400">{{ $form->created_at->format('d/m/Y') }}</td>
                    <td class="px-5 py-4 text-right space-x-2">
                        <a href="{{ route('forms.show', $form) }}" class="text-kbb-700 hover:text-kbb-800 text-sm font-medium">Detail</a>
                        <a href="{{ route('forms.edit', $form) }}" class="text-amber-600 hover:text-amber-700 text-sm font-medium">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ auth()->user()->isSuperAdmin() ? 7 : 6 }}" class="px-5 py-12 text-center text-gray-400">Tidak ada form.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if ($forms->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $forms->links() }}</div>
    @endif
</div>
@endsection
