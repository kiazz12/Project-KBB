@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola akun pengguna</p>
    </div>
    <a href="{{ route('admin.users.create') }}"
       class="inline-flex items-center gap-2 bg-kbb-700 hover:bg-kbb-800 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah User
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 text-gray-500 text-left border-b border-gray-100">
                    <th class="px-5 py-3.5 font-semibold">Nama</th>
                    <th class="px-5 py-3.5 font-semibold">Email</th>
                    <th class="px-5 py-3.5 font-semibold">Role</th>
                    <th class="px-5 py-3.5 font-semibold">OPD</th>
                    <th class="px-5 py-3.5 font-semibold">Tgl Dibuat</th>
                    <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50/80 transition-all group">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $user->isSuperAdmin() ? 'from-amber-50 to-amber-100' : 'from-kbb-50 to-kbb-100' }} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <span class="text-sm font-bold {{ $user->isSuperAdmin() ? 'text-amber-700' : 'text-kbb-700' }}">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                    @if($user->nip)
                                        <p class="text-xs text-gray-400">NIP. {{ $user->nip }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-500">{{ $user->email }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-semibold {{ $user->isSuperAdmin() ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->isSuperAdmin() ? 'bg-amber-500' : 'bg-blue-500' }}"></span>
                                {{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-500">{{ $user->opd ?? '-' }}</td>
                        <td class="px-5 py-4 text-gray-400 text-sm">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="inline-flex items-center gap-1.5 text-kbb-700 hover:text-kbb-800 text-sm font-medium px-3 py-1.5 rounded-lg hover:bg-kbb-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            @if ($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                       class="inline-flex items-center gap-1.5 text-red-500 hover:text-red-700 text-sm font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                            </div>
                            <p class="text-sm text-gray-400">Tidak ada user.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
