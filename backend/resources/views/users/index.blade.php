@extends('layouts.app')

@section('title', 'Users')

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
                <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Users</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Kelola Pengguna</h1>
            <p class="text-kbb-200/80 mt-1 text-sm">Semua akun pengguna sistem</p>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 dark:bg-slate-900/60 text-gray-500 dark:text-gray-400 text-left border-b border-gray-100 dark:border-slate-700">
                    <th class="px-5 py-3.5 font-semibold">Nama</th>
                    <th class="px-5 py-3.5 font-semibold">Email</th>
                    <th class="px-5 py-3.5 font-semibold">Role</th>
                    <th class="px-5 py-3.5 font-semibold">OPD</th>
                    <th class="px-5 py-3.5 font-semibold">Dibuat</th>
                    <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-slate-700/40 transition-all group">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $user->isSuperAdmin() ? 'from-amber-50 to-amber-100' : 'from-kbb-50 to-kbb-100' }} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <span class="text-sm font-bold {{ $user->isSuperAdmin() ? 'text-amber-700' : 'text-kbb-700' }}">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                    @if($user->nip)
                                        <p class="text-xs text-gray-400">{{ $user->nip }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-semibold {{ $user->isSuperAdmin() ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->isSuperAdmin() ? 'bg-amber-500' : 'bg-blue-500' }}"></span>
                                {{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $user->opd ?? '-' }}</td>
                        <td class="px-5 py-4 text-gray-400 text-sm">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('users.show', $user) }}"
                               class="inline-flex items-center gap-1.5 text-kbb-700 hover:text-kbb-800 dark:text-kbb-400 dark:hover:text-kbb-300 text-sm font-medium px-3 py-1.5 rounded-lg hover:bg-kbb-50 dark:hover:bg-slate-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="w-14 h-14 bg-gray-50 dark:bg-slate-700 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                            </div>
                            <p class="text-sm text-gray-400">Tidak ada user.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/40">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
