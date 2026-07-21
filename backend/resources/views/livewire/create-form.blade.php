<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('forms.index') }}" class="text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Buat Form Baru</h1>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
        <div class="bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] px-6 py-5 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/[0.04] rounded-full -translate-y-1/2 translate-x-1/3"></div>
            <div class="relative flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div>
                    <h2 class="font-semibold">Mulai dari dasar</h2>
                    <p class="text-kbb-200/80 text-xs mt-0.5">Isi judul dan deskripsi, lalu tambahkan field di editor.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="p-6">
            <div class="space-y-5">
                <div>
                    <label for="form-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Judul Form</label>
                    <input id="form-title" type="text" wire:model="title" required
                        class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition" placeholder="Mis: Form Kehadiran Rapat Bulanan">
                    @error('title') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="form-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
                    <textarea id="form-description" wire:model="description" rows="4"
                        class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition resize-none" placeholder="Jelaskan tujuan form ini (opsional)"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('forms.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 px-4 py-2.5 transition">Batal</a>
                    <button type="submit" wire:loading.attr="disabled"
                        class="bg-kbb-700 hover:bg-kbb-800 disabled:opacity-50 text-white font-medium px-6 py-2.5 rounded-lg transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span wire:loading.remove wire:target="save">Buat & Lanjutkan</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
