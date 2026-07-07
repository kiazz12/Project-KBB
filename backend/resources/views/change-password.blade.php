@extends('layouts.app')

@section('content')
<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Ubah Password</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('change-password') }}" method="POST" id="password-form">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                    <div class="relative">
                        <input type="password" name="current_password" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition pr-10">
                    </div>
                    @error('current_password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <div class="relative">
                        <input type="password" name="new_password" required minlength="8"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition pr-10">
                    </div>
                    @error('new_password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" required minlength="8"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition">
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" id="submit-btn"
                        class="bg-kbb-700 hover:bg-kbb-800 disabled:opacity-50 text-white font-medium px-6 py-2.5 rounded-lg transition">
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
