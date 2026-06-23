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
                <input type="password" name="password" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition duration-200">
                Masuk
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">Hanya untuk Super Admin KBB</p>
    </div>
    <p class="text-center text-white/60 text-xs mt-4">&copy; {{ date('Y') }} Pemerintah Kabupaten Bandung Barat</p>
</div>
@endsection
