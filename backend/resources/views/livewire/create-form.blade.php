<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('forms.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Buat Form Baru</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form wire:submit="save">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul Form</label>
                    <input type="text" wire:model="title" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition" placeholder="Nama form">
                    @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea wire:model="description" rows="3"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition" placeholder="Deskripsi form (opsional)"></textarea>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" wire:loading.attr="disabled"
                        class="bg-kbb-700 hover:bg-kbb-800 disabled:opacity-50 text-white font-medium px-6 py-2.5 rounded-lg transition">
                        <span wire:loading.remove wire:target="save">Buat & Lanjutkan</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
