@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="w-full max-w-sm relative z-10">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-[0_8px_40px_rgba(0,0,0,0.3)] overflow-hidden">
            <div class="bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] px-8 pt-8 pb-6 text-white relative overflow-hidden">
                <div class="absolute top-0 -right-8 w-32 h-32 bg-white/[0.04] rounded-full"></div>
                <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-white/[0.03] rounded-full"></div>
                <div class="absolute top-6 left-12 w-2 h-2 bg-white/10 rounded-full"></div>
                <div class="absolute top-3 right-16 w-1.5 h-1.5 bg-white/10 rounded-full"></div>
                <svg class="absolute right-0 bottom-0 w-32 opacity-[0.05]" viewBox="0 0 200 100" fill="white">
                    <path d="M0 50 Q 25 10 50 50 T 100 50 T 150 50 T 200 50 L 200 100 L 0 100 Z"/>
                </svg>
                <div class="relative text-center">
                    <div class="w-16 h-16 mx-auto mb-3 bg-white/15 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg ring-4 ring-white/20">
                        <img src="{{ asset('images/kbb-logo.png') }}" alt="KBB" class="w-10 h-10">
                    </div>
                    <h1 class="text-xl font-bold">Formulir Online</h1>
                    <p class="text-kbb-200/80 text-sm mt-0.5">Pemerintah Kabupaten Bandung Barat</p>
                </div>
            </div>

            <div class="p-8">
                @if (session('login_limit_error'))
                    <div id="login-limit-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-sm w-full mx-4 p-6 text-center" style="animation: bIn 0.3s ease-out">
                            <div class="w-14 h-14 mx-auto mb-4 bg-red-100 dark:bg-red-500/15 rounded-2xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Session Login Terbatas</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">{{ session('login_limit_error') }}</p>
                            <button onclick="this.closest('#login-limit-modal').remove()" class="w-full bg-kbb-700 hover:bg-kbb-800 text-white font-medium py-2.5 rounded-xl transition">Mengerti</button>
                        </div>
                    </div>
                    <style>
                        @keyframes bIn { 0%{transform:scale(0.9) translateY(10px);opacity:0} 100%{transform:scale(1) translateY(0);opacity:1} }
                    </style>
                @endif

                @if ($errors->any())
                    <div class="mb-5 px-4 py-3 bg-red-50 dark:bg-red-500/15 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $errors->first('email') ?: 'Email atau password salah.' }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-5 px-4 py-3 bg-red-50 dark:bg-red-500/15 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-kbb-500/20 focus:border-kbb-500 outline-none transition text-sm placeholder:text-gray-400 text-gray-900 dark:text-gray-100" placeholder="admin@dinas.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                        <div class="relative">
                            <input id="password-field" type="password" name="password" required
                                class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-kbb-500/20 focus:border-kbb-500 outline-none transition pr-12 text-sm placeholder:text-gray-400 text-gray-900 dark:text-gray-100" placeholder="••••••••">
                            <button type="button" id="toggle-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                                <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg id="eye-closed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 dark:border-slate-600 text-kbb-700 focus:ring-kbb-500/20 focus:ring-offset-0">
                            <span class="text-sm text-gray-600 dark:text-gray-300">Ingat saya</span>
                        </label>
                    </div>
                <button type="submit" class="w-full bg-gradient-to-r from-kbb-700 to-kbb-800 hover:from-kbb-800 hover:to-kbb-900 text-white font-semibold py-3 rounded-xl transition-all duration-200 shadow-lg shadow-kbb-700/30 hover:shadow-kbb-700/50 hover:-translate-y-0.5 active:translate-y-0">
                    Masuk
                </button>
            </form>
        </div>
    </div>
    <p class="text-center text-white/40 text-xs mt-5">&copy; {{ date('Y') }} Pemerintah Kabupaten Bandung Barat</p>
</div>
<script>
    document.getElementById('toggle-password')?.addEventListener('click', function() {
        const field = document.getElementById('password-field');
        const open = document.getElementById('eye-open');
        const closed = document.getElementById('eye-closed');
        if (field.type === 'password') {
            field.type = 'text';
            open.style.display = 'none';
            closed.style.display = '';
        } else {
            field.type = 'password';
            open.style.display = '';
            closed.style.display = 'none';
        }
    });
</script>
@endsection
