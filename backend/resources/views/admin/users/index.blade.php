@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola akun pengguna</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        + Tambah User
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-left">
                    <th class="px-5 py-3 font-medium">Nama</th>
                    <th class="px-5 py-3 font-medium">Email</th>
                    <th class="px-5 py-3 font-medium">Role</th>
                    <th class="px-5 py-3 font-medium">OPD</th>
                    <th class="px-5 py-3 font-medium">Tgl Dibuat</th>
                    <th class="px-5 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $user->email }}</td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $user->role === 'super_admin' ? 'bg-amber-100 text-amber-700' : ($user->role === 'admin' ? 'bg-blue-100 text-blue-700' : ($user->role === 'operator' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500')) }}">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-500">{{ $user->opd ?? '-' }}</td>
                        <td class="px-5 py-4 text-gray-400">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium mr-3">Edit</a>
                            @if ($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">Tidak ada user.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
