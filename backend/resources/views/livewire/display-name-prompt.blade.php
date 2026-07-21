<div>
@if($show)
<div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-sm" wire:click.self="">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6" style="animation: bIn 0.3s ease-out">
        <div class="text-center mb-6">
            <div class="w-14 h-14 mx-auto mb-4 bg-kbb-100 dark:bg-kbb-500/20 rounded-2xl flex items-center justify-center">
                <svg class="w-7 h-7 text-kbb-700 dark:text-kbb-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">Selamat Datang!</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Masukkan nama tampilan Anda untuk melanjutkan. Nama ini akan digunakan untuk melacak aktivitas akun Anda.</p>
        </div>

        <form wire:submit.prevent="saveDisplayName" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Tampilan</label>
                <input type="text" wire:model="displayName" required autofocus
                    class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-kbb-500/20 focus:border-kbb-500 outline-none transition text-sm text-gray-900 dark:text-gray-100" placeholder="Masukkan nama Anda">
                @error('displayName') <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit"
                class="w-full bg-gradient-to-r from-kbb-700 to-kbb-800 hover:from-kbb-800 hover:to-kbb-900 text-white font-semibold py-3 rounded-xl transition-all duration-200 shadow-lg shadow-kbb-700/30">
                Simpan & Lanjutkan
            </button>
        </form>
    </div>
</div>
@endif
</div>
