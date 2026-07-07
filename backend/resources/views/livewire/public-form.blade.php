<div class="w-full max-w-2xl">
    @if ($error && !$form)
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <p class="text-gray-500">{{ $error }}</p>
        </div>
    @endif

    @if ($form && !$submitted)
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-kbb-700 px-8 py-6">
                @if ($form->show_kbb_logo)
                    <div class="flex justify-center mb-4">
                        <img src="{{ asset('images/kbb-logo.png') }}" alt="KBB" class="w-12 h-12">
                    </div>
                @endif
                <h1 class="text-2xl font-bold text-white text-center">{{ $form->title }}</h1>
                @if ($form->description)
                    <p class="text-kbb-200 text-sm mt-1 text-center">{{ $form->description }}</p>
                @endif
            </div>
            <div class="p-8 space-y-6">
                <form wire:submit="submitForm">
                    @foreach ($form->fields as $field)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span>{{ $field->label }}</span>
                                @if ($field->required) <span class="text-red-500">*</span> @endif
                            </label>
                            @if ($field->help_text)
                                <p class="text-xs text-gray-400 mb-1">{{ $field->help_text }}</p>
                            @endif

                            @if (in_array($field->type->value, ['text', 'email', 'number']))
                                <input type="{{ $field->type->value === 'email' ? 'email' : ($field->type->value === 'number' ? 'number' : 'text') }}"
                                    wire:model="responses.{{ $field->id }}"
                                    @required($field->required)
                                    placeholder="{{ $field->placeholder }}"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition">
                            @endif

                            @if ($field->type->value === 'textarea')
                                <textarea wire:model="responses.{{ $field->id }}" @required($field->required) placeholder="{{ $field->placeholder }}"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition" rows="3"></textarea>
                            @endif

                            @if ($field->type->value === 'select')
                                <select wire:model="responses.{{ $field->id }}" @required($field->required)
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition">
                                    <option value="">Pilih...</option>
                                    @foreach ($field->options ?? [] as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @endif

                            @if ($field->type->value === 'radio')
                                <div class="space-y-2 mt-1">
                                    @foreach ($field->options ?? [] as $opt)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" wire:model="responses.{{ $field->id }}" value="{{ $opt }}"
                                                class="text-kbb-700 focus:ring-kbb-500">
                                            <span class="text-sm text-gray-700">{{ $opt }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            @if ($field->type->value === 'checkbox')
                                <div class="space-y-2 mt-1">
                                    @foreach ($field->options ?? [] as $opt)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" wire:model="responses.{{ $field->id }}" value="{{ $opt }}"
                                                class="text-kbb-700 focus:ring-kbb-500 rounded">
                                            <span class="text-sm text-gray-700">{{ $opt }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            @if ($field->type->value === 'date')
                                <input type="date" wire:model="responses.{{ $field->id }}" @required($field->required)
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition">
                            @endif

                            @if ($field->type->value === 'time')
                                <input type="time" wire:model="responses.{{ $field->id }}" @required($field->required)
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 outline-none transition">
                            @endif

                            @if ($field->type->value === 'file')
                                <input type="file" wire:model="responses.{{ $field->id }}" @required($field->required) accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-kbb-50 file:text-kbb-700 hover:file:bg-kbb-100 transition">
                                @error("responses.{$field->id}") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            @endif

                            @if ($field->type->value === 'signature')
                                <div class="border border-gray-300 rounded-lg overflow-hidden" x-data="{}">
                                    <canvas id="sig-{{ $field->id }}" class="w-full h-32 cursor-crosshair"></canvas>
                                </div>
                                <input type="hidden" wire:model="responses.{{ $field->id }}" id="sig-input-{{ $field->id }}">
                                <div class="flex gap-2 mt-1">
                                    <button type="button" onclick="clearSig({{ $field->id }})" class="text-xs text-gray-500 hover:text-gray-700">Hapus</button>
                                </div>
                                <script>
                                    (function() {
                                        let canvas = document.getElementById('sig-{{ $field->id }}');
                                        if (!canvas) return;
                                        let ctx = canvas.getContext('2d');
                                        let drawing = false;
                                        canvas.width = canvas.offsetWidth;
                                        canvas.height = canvas.offsetHeight;
                                        ctx.strokeStyle = '#1f2937';
                                        ctx.lineWidth = 2;
                                        ctx.lineCap = 'round';
                                        canvas.addEventListener('mousedown', () => drawing = true);
                                        canvas.addEventListener('mouseup', () => { drawing = false; ctx.beginPath(); saveSig({{ $field->id }}); });
                                        canvas.addEventListener('mouseleave', () => { if (drawing) { drawing = false; ctx.beginPath(); saveSig({{ $field->id }}); }});
                                        canvas.addEventListener('mousemove', (e) => {
                                            if (!drawing) return;
                                            let rect = canvas.getBoundingClientRect();
                                            ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
                                            ctx.stroke();
                                            ctx.beginPath();
                                            ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
                                        });
                                        canvas.addEventListener('touchstart', (e) => { e.preventDefault(); drawing = true; });
                                        canvas.addEventListener('touchend', (e) => { e.preventDefault(); drawing = false; ctx.beginPath(); saveSig({{ $field->id }}); });
                                        canvas.addEventListener('touchmove', (e) => {
                                            e.preventDefault();
                                            if (!drawing) return;
                                            let rect = canvas.getBoundingClientRect();
                                            let touch = e.touches[0];
                                            ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
                                            ctx.stroke();
                                            ctx.beginPath();
                                            ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
                                        });
                                    })();
                                    function saveSig(id) {
                                        let canvas = document.getElementById('sig-' + id);
                                        document.getElementById('sig-input-' + id).value = canvas.toDataURL();
                                    }
                                    function clearSig(id) {
                                        let canvas = document.getElementById('sig-' + id);
                                        let ctx = canvas.getContext('2d');
                                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                                        document.getElementById('sig-input-' + id).value = '';
                                    }
                                </script>
                            @endif

                            @if ($field->type->value === 'heading')
                                <h3 class="text-lg font-semibold text-gray-900">{{ $field->label }}</h3>
                            @endif

                            @if ($field->type->value === 'paragraph')
                                <p class="text-sm text-gray-600">{{ $field->label }}</p>
                            @endif

                            @error("responses.{$field->id}")
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    @if ($error)
                        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm mb-4">{{ $error }}</div>
                    @endif

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full bg-kbb-700 hover:bg-kbb-800 disabled:opacity-50 text-white font-medium py-3 rounded-lg transition text-lg">
                        <span wire:loading.remove wire:target="submitForm">Kirim</span>
                        <span wire:loading wire:target="submitForm">Mengirim...</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($submitted)
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Terima Kasih!</h2>
            <p class="text-gray-500">{{ $form?->confirmation_message ?? 'Jawaban Anda telah dicatat.' }}</p>
        </div>
    @endif
</div>
