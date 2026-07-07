<div>
    @if ($message)
        <div class="mb-4 px-4 py-3 rounded-lg text-sm {{ $messageType === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-red-50 border border-red-200 text-red-700' }}">
            {{ $message }}
            <button wire:click="$set('message', '')" class="float-right">&times;</button>
        </div>
    @endif

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('forms.show', $form) }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Edit: {{ $form->title }}</h1>
    </div>

    <div class="flex gap-1 mb-6 bg-gray-100 p-1 rounded-lg w-fit">
        <button wire:click="selectTab('fields')"
            class="px-5 py-2 rounded-md text-sm font-medium transition {{ $tab === 'fields' ? 'bg-white shadow text-kbb-700' : 'text-gray-500 hover:text-gray-700' }}">
            Fields
        </button>
        <button wire:click="selectTab('settings')"
            class="px-5 py-2 rounded-md text-sm font-medium transition {{ $tab === 'settings' ? 'bg-white shadow text-kbb-700' : 'text-gray-500 hover:text-gray-700' }}">
            Settings
        </button>
    </div>

    @if ($tab === 'fields')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                @forelse ($form->fields as $index => $field)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 {{ $editingFieldId === $field->id ? 'ring-2 ring-kbb-500' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="flex flex-col items-center gap-1 text-gray-300 pt-1">
                                <button wire:click="moveFieldUp({{ $field->id }})" @disabled($loop->first) class="hover:text-gray-500 disabled:opacity-30">▲</button>
                                <span class="text-xs font-mono">{{ $loop->iteration }}</span>
                                <button wire:click="moveFieldDown({{ $field->id }})" @disabled($loop->last) class="hover:text-gray-500 disabled:opacity-30">▼</button>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded bg-kbb-50 text-kbb-700 uppercase">{{ $field->type->value }}</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $field->label }}</span>
                                    @if ($field->required) <span class="text-xs text-red-500">*</span> @endif
                                </div>
                                @if ($field->placeholder)
                                    <p class="text-xs text-gray-400">Placeholder: {{ $field->placeholder }}</p>
                                @endif
                                @if (in_array($field->type->value, ['select', 'radio', 'checkbox']) && $field->options)
                                    <div class="mt-2">
                                        @foreach ($field->options as $opt)
                                            <span class="inline-block text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded mr-1 mb-1">{{ $opt }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <button wire:click="editField({{ $field->id }})" class="text-gray-400 hover:text-kbb-600 p-1" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="deleteField({{ $field->id }})" wire:confirm="Hapus field ini?" class="text-gray-400 hover:text-red-600 p-1" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl shadow-sm border border-dashed border-gray-300 p-12 text-center">
                        <p class="text-gray-400">Belum ada field. Tambahkan field dari panel sebelah kanan.</p>
                    </div>
                @endforelse
            </div>

            <div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $editingFieldId ? 'Edit Field' : 'Tambah Field' }}</h3>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Field</label>
                        <select wire:model="fieldType" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none">
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="email">Email</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="time">Time</option>
                            <option value="select">Select</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="file">File</option>
                            <option value="heading">Heading</option>
                            <option value="paragraph">Paragraph</option>
                            <option value="signature">Signature</option>
                        </select>
                        @error('fieldType') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Label</label>
                        <input type="text" wire:model="fieldLabel" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none">
                        @error('fieldLabel') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Placeholder</label>
                        <input type="text" wire:model="fieldPlaceholder" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Help Text</label>
                        <input type="text" wire:model="fieldHelpText" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none">
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="fieldRequired" class="text-kbb-700 focus:ring-kbb-500 rounded">
                        <span class="text-sm text-gray-700">Wajib diisi</span>
                    </label>

                    @if (in_array($fieldType, ['select', 'radio', 'checkbox']))
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Options (satu per baris)</label>
                            <textarea wire:model="fieldOptionsText" rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none"
                                placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"></textarea>
                        </div>
                    @endif

                    <div class="flex gap-2 pt-2">
                        <button wire:click="saveField"
                            class="flex-1 bg-kbb-700 hover:bg-kbb-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                            {{ $editingFieldId ? 'Simpan' : 'Tambah' }}
                        </button>
                        @if ($editingFieldId)
                            <button wire:click="cancelEdit"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-4 py-2 rounded-lg transition">Batal</button>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-2 mt-4">
                    @if (in_array($form->status->value, ['draft', 'closed']))
                        <button wire:click="publishForm" wire:loading.attr="disabled"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                            <span wire:loading.remove wire:target="publishForm">Publikasikan</span>
                            <span wire:loading wire:target="publishForm">Mempublikasikan...</span>
                        </button>
                    @endif
                    @if ($form->status->value === 'published')
                        <button wire:click="closeForm" wire:loading.attr="disabled"
                            class="w-full bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                            <span wire:loading.remove wire:target="closeForm">Tutup Form</span>
                            <span wire:loading wire:target="closeForm">Menutup...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($tab === 'settings')
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form wire:submit="saveSettings">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Form</label>
                            <input type="text" wire:model="settingsTitle" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 outline-none transition">
                            @error('settingsTitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea wire:model="settingsDescription" rows="3"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 outline-none transition"></textarea>
                        </div>

                        <hr class="border-gray-200">

                        <h4 class="font-medium text-gray-900">Schedule & Limits</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mulai</label>
                                <input type="datetime-local" wire:model="settingsStartsAt"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Berakhir</label>
                                <input type="datetime-local" wire:model="settingsEndsAt"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 outline-none transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Submissions</label>
                            <input type="number" wire:model="settingsMaxSubmissions" min="0"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 outline-none transition" placeholder="0 = unlimited">
                        </div>

                        <hr class="border-gray-200">

                        <h4 class="font-medium text-gray-900">Confirmation</h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Konfirmasi</label>
                            <select wire:model="settingsConfirmationType"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 outline-none transition">
                                <option value="message">Pesan</option>
                                <option value="redirect">Redirect URL</option>
                            </select>
                        </div>
                        @if ($settingsConfirmationType === 'message')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pesan Konfirmasi</label>
                                <textarea wire:model="settingsConfirmationMessage" rows="2"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 outline-none transition"></textarea>
                            </div>
                        @endif
                        @if ($settingsConfirmationType === 'redirect')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Redirect URL</label>
                                <input type="url" wire:model="settingsRedirectUrl"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 outline-none transition">
                            </div>
                        @endif

                        <hr class="border-gray-200">

                        <h4 class="font-medium text-gray-900">Privacy</h4>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="settingsRequireAuth" class="text-kbb-700 focus:ring-kbb-500 rounded">
                                <span class="text-sm text-gray-700">Require authentication</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="settingsCollectIp" class="text-kbb-700 focus:ring-kbb-500 rounded">
                                <span class="text-sm text-gray-700">Collect IP address</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="settingsLimitOneResponse" class="text-kbb-700 focus:ring-kbb-500 rounded">
                                <span class="text-sm text-gray-700">Limit to one response per user</span>
                            </label>
                        </div>

                        <hr class="border-gray-200">

                        <h4 class="font-medium text-gray-900">Tampilan</h4>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="settingsShowKbbLogo" class="text-kbb-700 focus:ring-kbb-500 rounded">
                            <span class="text-sm text-gray-700">Tampilkan logo KBB</span>
                        </label>

                        <div class="flex justify-end pt-4">
                            <button type="submit" wire:loading.attr="disabled"
                                class="bg-kbb-700 hover:bg-kbb-800 disabled:opacity-50 text-white font-medium px-6 py-2.5 rounded-lg transition">
                                <span wire:loading.remove wire:target="saveSettings">Simpan Pengaturan</span>
                                <span wire:loading wire:target="saveSettings">Menyimpan...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
