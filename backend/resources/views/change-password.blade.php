@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
<div class="mb-8">
    <div class="bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] rounded-2xl p-8 text-white relative overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/[0.03] rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-1/3 w-96 h-96 bg-white/[0.02] rounded-full translate-y-1/2"></div>
        <div class="absolute top-1/2 left-10 w-4 h-4 bg-white/10 rounded-full"></div>
        <div class="absolute top-20 right-20 w-2 h-2 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                <span class="text-xs font-medium text-kbb-200 uppercase tracking-wider">Akun</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Ubah Password</h1>
            <p class="text-kbb-200/80 mt-1 text-sm">Perbarui password akun Anda</p>
        </div>
    </div>
</div>

<div class="max-w-lg">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-6">
        <form action="{{ route('change-password') }}" method="POST" id="password-form">
            @csrf
            <div class="space-y-4">
                @if(!auth()->user()->isSuperAdmin())
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Saat Ini</label>
                    <div class="relative">
                        <input type="password" name="current_password" required
                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition pr-10 text-gray-900 dark:text-gray-100">
                    </div>
                    @error('current_password') <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru</label>
                    <div class="relative">
                        <input type="password" name="new_password" required minlength="8"
                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition pr-10 text-gray-900 dark:text-gray-100">
                    </div>
                    @error('new_password') <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" required minlength="8"
                        class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition text-gray-900 dark:text-gray-100">
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" id="submit-btn"
                        class="bg-kbb-700 hover:bg-kbb-800 disabled:opacity-50 text-white font-medium px-6 py-2.5 rounded-xl transition">
                        <span id="btn-text">Simpan</span>
                    </button>
                </div>
            </div>
        </form>
        <script>
            document.getElementById('password-form').addEventListener('submit', function() {
                document.getElementById('submit-btn').disabled = true;
                document.getElementById('btn-text').textContent = 'Menyimpan...';
            });
        </script>
    </div>
</div>
@endsection
