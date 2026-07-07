@extends('admin.layouts.app')

@section('title', 'Login Admin')

@section('content')
<div class="w-full max-w-sm">
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-8">
            <img src="{{ asset('images/kbb-logo.png') }}" alt="KBB" class="w-14 h-14 mx-auto mb-3">
            <h1 class="text-xl font-bold text-gray-900">KBB Admin Panel</h1>
            <p class="text-sm text-gray-500 mt-1">Super Admin — Kabupaten Bandung Barat</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                {{ $errors->first('email') }}
            </div>
        @endif

        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="admin@dinas.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <input id="password-field" type="password" name="password" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition pr-10" placeholder="••••••••">
                    <button type="button" id="toggle-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg id="eye-closed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-600">Ingat saya</span>
                </label>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition duration-200">
                Masuk
            </button>
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
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">Hanya untuk Super Admin KBB</p>
    </div>
    <p class="text-center text-white/60 text-xs mt-4">&copy; {{ date('Y') }} Pemerintah Kabupaten Bandung Barat</p>
</div>
@endsection
