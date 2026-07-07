<div>
    @if ($message)
        <div class="mb-6 px-4 py-3 rounded-lg text-sm {{ $messageType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
            {{ $message }}
            <button wire:click="$set('message', '')" class="float-right font-bold">&times;</button>
        </div>
    @endif

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('forms.show', $form) }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Edit: {{ $form->title }}</h1>
    </div>

    <div class="flex gap-4 mb-6 border-b border-gray-200">
        <button wire:click="selectTab('fields')" class="px-4 py-2.5 text-sm font-medium transition border-b-2 -mb-px {{ $tab === 'fields' ? 'text-kbb-700 border-kbb-700' : 'text-gray-500 border-transparent hover:text-gray-700' }}">
            Fields
        </button>
        <button wire:click="selectTab('settings')" class="px-4 py-2.5 text-sm font-medium transition border-b-2 -mb-px {{ $tab === 'settings' ? 'text-kbb-700 border-kbb-700' : 'text-gray-500 border-transparent hover:text-gray-700' }}">
            Pengaturan
        </button>
    </div>

    @if ($tab === 'fields')
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <div class="lg:col-span-3 space-y-4">
                @forelse ($form->sections as $section)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-3 bg-kbb-50 border-b border-kbb-100">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <svg class="w-4 h-4 text-kbb-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                <span class="text-sm font-semibold text-kbb-800 truncate">{{ $section->title }}</span>
                                @if ($section->description)
                                    <span class="text-xs text-kbb-500 truncate">— {{ $section->description }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <button wire:click="moveSectionUp({{ $section->id }})" class="p-1 text-kbb-400 hover:text-kbb-700 transition" title="Naik">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button wire:click="moveSectionDown({{ $section->id }})" class="p-1 text-kbb-400 hover:text-kbb-700 transition" title="Turun">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button wire:click="editSection({{ $section->id }})" class="p-1 text-gray-400 hover:text-gray-700 transition" title="Edit Section">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="deleteSection({{ $section->id }})" wire:confirm="Hapus section '{{ $section->title }}'?" class="p-1 text-red-400 hover:text-red-600 transition" title="Hapus Section">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="p-5 space-y-3">
                            @php $sectionFields = $form->fields->where('section_id', $section->id); @endphp
                            @forelse ($sectionFields as $field)
                                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 rounded-lg {{ $editingFieldId === $field->id ? 'ring-2 ring-kbb-500' : '' }}">
                                    <div class="flex flex-col gap-0.5">
                                        <button wire:click="moveFieldUp({{ $field->id }})" class="text-gray-300 hover:text-gray-600 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        </button>
                                        <button wire:click="moveFieldDown({{ $field->id }})" class="text-gray-300 hover:text-gray-600 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $field->label }}</p>
                                        <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5">
                                            <span class="capitalize">{{ $field->type->value }}</span>
                                            @if ($field->required) <span class="text-red-500">wajib</span> @endif
                                        </div>
                                    </div>
                                    <button wire:click="editField({{ $field->id }})" class="text-xs text-kbb-700 hover:text-kbb-800 font-medium">Edit</button>
                                    <button wire:click="deleteField({{ $field->id }})" wire:confirm="Hapus field '{{ $field->label }}'?" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 text-center py-4">Belum ada field di section ini.</p>
                            @endforelse
                            <button wire:click="editField(-1)" wire:key="add-field-{{ $section->id }}" class="w-full text-sm text-kbb-700 hover:text-kbb-800 font-medium py-2 border-2 border-dashed border-gray-200 rounded-lg hover:border-kbb-300 transition">
                                + Tambah Field di "{{ $section->title }}"
                            </button>
                        </div>
                    </div>
                @endforeach

                @php $noSectionFields = $form->fields->whereNull('section_id'); @endphp
                @if ($noSectionFields->isNotEmpty() || $form->sections->isEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        @if ($form->sections->isNotEmpty())
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Tanpa Section</h4>
                        @endif
                        <div class="space-y-3">
                            @foreach ($noSectionFields as $field)
                                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 rounded-lg {{ $editingFieldId === $field->id ? 'ring-2 ring-kbb-500' : '' }}">
                                    <div class="flex flex-col gap-0.5">
                                        <button wire:click="moveFieldUp({{ $field->id }})" class="text-gray-300 hover:text-gray-600 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        </button>
                                        <button wire:click="moveFieldDown({{ $field->id }})" class="text-gray-300 hover:text-gray-600 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $field->label }}</p>
                                        <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5">
                                            <span class="capitalize">{{ $field->type->value }}</span>
                                            @if ($field->required) <span class="text-red-500">wajib</span> @endif
                                        </div>
                                    </div>
                                    <button wire:click="editField({{ $field->id }})" class="text-xs text-kbb-700 hover:text-kbb-800 font-medium">Edit</button>
                                    <button wire:click="deleteField({{ $field->id }})" wire:confirm="Hapus field '{{ $field->label }}'?" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                </div>
                            @endforeach
                            @if ($form->sections->isEmpty())
                                <button wire:click="editField(-1)" class="w-full text-sm text-kbb-700 hover:text-kbb-800 font-medium py-2 border-2 border-dashed border-gray-200 rounded-lg hover:border-kbb-300 transition">
                                    + Tambah Field
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                <button wire:click="addSection" class="w-full text-sm bg-kbb-50 hover:bg-kbb-100 text-kbb-700 font-medium px-4 py-3 rounded-xl border border-dashed border-kbb-300 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Section Baru
                </button>
            </div>

            <div class="lg:col-span-2 space-y-4">
                @if ($showSectionForm)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ $editingSectionId ? 'Edit Section' : 'Buat Section Baru' }}</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Judul Section</label>
                                <input type="text" wire:model="sectionTitle" placeholder="Mis: Data Pribadi"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none transition">
                                @error('sectionTitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Deskripsi (opsional)</label>
                                <textarea wire:model="sectionDescription" rows="2" placeholder="Penjelasan section ini"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none transition"></textarea>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button wire:click="saveSection" class="bg-kbb-700 hover:bg-kbb-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                                    Simpan
                                </button>
                                <button wire:click="cancelSection" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg transition">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ $editingFieldId ? 'Edit Field' : 'Tambah Field Baru' }}</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipe</label>
                            <select wire:model="fieldType"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none transition">
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
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Label</label>
                            <input type="text" wire:model="fieldLabel" placeholder="Nama field"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none transition">
                            @error('fieldLabel') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Placeholder</label>
                            <input type="text" wire:model="fieldPlaceholder" placeholder="Text placeholder"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Help Text</label>
                            <textarea wire:model="fieldHelpText" rows="2" placeholder="Bantuan untuk pengisi form"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none transition"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Section</label>
                            <select wire:model="fieldSectionId"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none transition">
                                <option value="">Tanpa Section</option>
                                @foreach ($form->sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if (in_array($fieldType, ['select', 'radio', 'checkbox']))
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Opsi (satu per baris)</label>
                                <textarea wire:model="fieldOptionsText" rows="4" placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-kbb-500 outline-none transition"></textarea>
                            </div>
                        @endif

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="fieldRequired" class="text-kbb-700 focus:ring-kbb-500 rounded">
                            <span class="text-sm text-gray-700">Wajib diisi</span>
                        </label>

                        <button wire:click="saveField"
                            class="w-full bg-kbb-700 hover:bg-kbb-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                            {{ $editingFieldId ? 'Simpan Perubahan' : 'Tambah Field' }}
                        </button>

                        @if ($editingFieldId)
                            <button wire:click="cancelEdit" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg transition">
                                Batal
                            </button>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-2">
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

    @if ($showPublishModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:click.self="closePublishModal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 p-6 relative">
            <button wire:click="closePublishModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="text-center mb-6">
                <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Form Berhasil Dipublikasikan!</h3>
                <p class="text-sm text-gray-500 mt-1">Bagikan link berikut untuk mengumpulkan respons</p>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-500 mb-1">Link Form</p>
                        <p class="text-sm font-medium text-kbb-700 truncate" id="publish-link">{{ $publishedUrl }}</p>
                    </div>
                    <button onclick="copyPublishLink()"
                        class="flex-shrink-0 bg-kbb-700 hover:bg-kbb-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        Salin
                    </button>
                </div>
            </div>

            <div class="flex justify-center mb-4">
                <div id="qrcode" data-url="{{ $publishedUrl }}" class="bg-white p-3 rounded-xl border border-gray-200"></div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('forms.show', $form) }}"
                   class="flex-1 text-center bg-kbb-700 hover:bg-kbb-800 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
                    Lihat Detail Form
                </a>
                <button wire:click="closePublishModal"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-lg transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    <script>
    function copyPublishLink() {
        var input = document.createElement('input');
        input.value = document.getElementById('publish-link').textContent;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        var btn = event.currentTarget;
        btn.textContent = 'Tersalin!';
        setTimeout(function() { btn.textContent = 'Salin'; }, 2000);
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var observer = new MutationObserver(function () {
            var el = document.getElementById('qrcode');
            if (el && !el.hasAttribute('data-qr-initialized')) {
                el.setAttribute('data-qr-initialized', '1');
                el.innerHTML = '';
                var url = el.getAttribute('data-url');
                if (url) {
                    new QRCode(el, { text: url, width: 180, height: 180, colorDark: '#003778', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H });
                }
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
    </script>
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
