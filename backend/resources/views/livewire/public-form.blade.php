<div class="w-full max-w-2xl mx-auto">
    @if ($error && !$form)
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-8 text-center border border-gray-100 dark:border-slate-700">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-500/15 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $error }}</p>
        </div>
    @endif

    @if ($form && !$submitted)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden border border-gray-100 dark:border-slate-700">
            {{-- Header --}}
            <div class="relative bg-gradient-to-br from-kbb-700 via-[#004a9c] to-kbb-700 px-8 py-8">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white rounded-full translate-y-1/2 -translate-x-1/2"></div>
                </div>
                <div class="relative flex flex-col items-center text-center">
                    <img src="{{ asset('images/kbb-logo.png') }}" alt="Logo KBB" class="w-16 h-16 mb-3 drop-shadow-lg">
                    <p class="text-[11px] font-medium text-white/70 tracking-[0.15em] uppercase font-sans">Pemerintah Kabupaten Bandung Barat</p>
                    <div class="w-10 h-[2px] bg-[#C8A45C]/50 my-3 rounded-full"></div>
                    <h1 class="font-display text-[22px] font-extrabold text-white leading-tight max-w-md tracking-tight">{{ $form->title }}</h1>
                    <div class="w-10 h-[2px] bg-[#C8A45C]/50 my-3 rounded-full"></div>
                    <p class="text-[10px] font-medium text-white/50 tracking-wider uppercase font-sans">Dinas Komunikasi, Informatika, Persandian dan Statistik</p>
                </div>
            </div>

            {{-- Progress (multi-step) --}}
            @if ($totalSteps > 1 && ($form->settings['show_progress_bar'] ?? true))
                <div class="px-8 pt-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Langkah {{ $currentStep }} dari {{ $totalSteps }}</span>
                        <span class="text-xs font-semibold text-kbb-700 dark:text-kbb-400">{{ round(($currentStep / $totalSteps) * 100) }}%</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-kbb-700 to-[kbb-500] rounded-full transition-all duration-300" style="width: {{ ($currentStep / $totalSteps) * 100 }}%"></div>
                    </div>
                </div>
            @endif

            {{-- Form Body --}}
            <div class="px-8 py-5">
                <form wire:submit="submitForm">
                    <div aria-hidden="true" style="position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden;">
                        <label>Website</label>
                        <input type="text" tabindex="-1" autocomplete="off" wire:model="company_website">
                    </div>
                    @php $step = $steps[$currentStep - 1] ?? null; @endphp
                    @if ($step)
                        <div class="space-y-5">
                            @foreach ($step['fields'] as $field)
                                @if ($field->is_admin_only) @continue @endif
                                <div class="group">
                                    @if ($field->type->value !== 'computed')
                                        <label for="field-{{ $field->id }}" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1.5 font-sans">
                                            {{ $field->label }}
                                            @if ($field->required) <span class="text-red-400">*</span> @endif
                                        </label>
                                    @endif
                                    @if ($field->help_text)
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-1.5 -mt-0.5">{{ $field->help_text }}</p>
                                    @endif

                                    {{-- Text / Email / Number --}}
                                    @if (in_array($field->type->value, ['text', 'email', 'number']))
                                        <input id="field-{{ $field->id }}" type="{{ $field->type->value === 'email' ? 'email' : ($field->type->value === 'number' ? 'number' : 'text') }}"
                                            wire:model="responses.{{ $field->id }}"
                                            @required($field->required)
                                            @if($field->default_value) readonly @endif
                                            aria-invalid="@error('responses.' . $field->id) true @enderror"
                                            placeholder="{{ $field->placeholder }}"
                                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-800 dark:text-gray-100 placeholder-gray-300 dark:placeholder-gray-500
                                                focus:ring-2 focus:ring-kbb-700/20 focus:border-kbb-700 outline-none transition-all duration-200
                                                {{ $field->default_value ? 'bg-gray-50 dark:bg-slate-900 text-gray-500 dark:text-gray-400 cursor-not-allowed' : 'bg-white dark:bg-slate-800 hover:border-gray-300 dark:hover:border-slate-500' }}">
                                    @endif

                                    {{-- Textarea --}}
                                    @if ($field->type->value === 'textarea')
                                        <textarea id="field-{{ $field->id }}" wire:model="responses.{{ $field->id }}" @required($field->required) placeholder="{{ $field->placeholder }}"
                                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-800 dark:text-gray-100 placeholder-gray-300 dark:placeholder-gray-500
                                            focus:ring-2 focus:ring-kbb-700/20 focus:border-kbb-700 outline-none transition-all duration-200 bg-white dark:bg-slate-800 hover:border-gray-300 dark:hover:border-slate-500" rows="3"></textarea>
                                    @endif

                                    {{-- Select --}}
                                    @if ($field->type->value === 'select')
                                        <select id="field-{{ $field->id }}" wire:model="responses.{{ $field->id }}" @required($field->required)
                                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-800 dark:text-gray-100
                                            focus:ring-2 focus:ring-kbb-700/20 focus:border-kbb-700 outline-none transition-all duration-200 bg-white dark:bg-slate-800 hover:border-gray-300 dark:hover:border-slate-500 appearance-none
                                            bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%236b7280%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20d%3D%22M5.293%207.293a1%201%200%20011.414%200L10%2010.586l3.293-3.293a1%201%200%20111.414%201.414l-4%204a1%201%200%2001-1.414%200l-4-4a1%201%200%20010-1.414z%22%20clip-rule%3D%22evenodd%22%2F%3E%3C%2Fsvg%3E')] bg-[length:20px] bg-[right_8px_center] bg-no-repeat">
                                            <option value="">Pilih...</option>
                                            @foreach ($field->options ?? [] as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @endif

                                    {{-- Radio --}}
                                    @if ($field->type->value === 'radio')
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            @foreach ($field->options ?? [] as $opt)
                                                <label class="relative cursor-pointer">
                                                    <input type="radio" wire:model="responses.{{ $field->id }}" value="{{ $opt }}" class="peer sr-only">
                                                    <div class="px-4 py-2 rounded-xl border-2 border-gray-200 dark:border-slate-600 text-sm font-medium text-gray-500 dark:text-gray-400
                                                        peer-checked:border-kbb-700 peer-checked:bg-kbb-700/5 peer-checked:text-kbb-700 dark:peer-checked:text-kbb-400
                                                        hover:border-gray-300 dark:hover:border-slate-500 transition-all duration-200">{{ $opt }}</div>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Checkbox --}}
                                    @if ($field->type->value === 'checkbox')
                                        <div class="space-y-2 mt-1">
                                            @foreach ($field->options ?? [] as $opt)
                                                <label class="flex items-center gap-3 cursor-pointer p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700/40 transition-colors">
                                                    <input type="checkbox" wire:model="responses.{{ $field->id }}" value="{{ $opt }}"
                                                        class="w-4 h-4 rounded border-gray-300 dark:border-slate-600 text-kbb-700 focus:ring-kbb-700/20">
                                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $opt }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Date --}}
                                    @if ($field->type->value === 'date')
                                        <input id="field-{{ $field->id }}" type="date" wire:model="responses.{{ $field->id }}" @required($field->required)
                                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-800 dark:text-gray-100
                                            focus:ring-2 focus:ring-kbb-700/20 focus:border-kbb-700 outline-none transition-all duration-200 bg-white dark:bg-slate-800 hover:border-gray-300 dark:hover:border-slate-500">
                                    @endif

                                    {{-- Time --}}
                                    @if ($field->type->value === 'time')
                                        <input id="field-{{ $field->id }}" type="time" wire:model="responses.{{ $field->id }}" @required($field->required)
                                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-800 dark:text-gray-100
                                            focus:ring-2 focus:ring-kbb-700/20 focus:border-kbb-700 outline-none transition-all duration-200 bg-white dark:bg-slate-800 hover:border-gray-300 dark:hover:border-slate-500">
                                    @endif

                                    {{-- File --}}
                                    @if ($field->type->value === 'file')
                                        <input id="field-{{ $field->id }}" type="file" wire:model="responses.{{ $field->id }}" @required($field->required) accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-kbb-700/10 file:text-kbb-700 hover:file:bg-kbb-700/20 transition">
                                        @error("responses.{$field->id}") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                    @endif

                                    {{-- Signature --}}
                                    @if ($field->type->value === 'signature')
                                        @php $fid = $field->id; @endphp
                                        <div class="border-2 border-dashed border-gray-200 rounded-xl overflow-hidden bg-gray-50/50 hover:border-gray-300 transition-colors" x-data="{}">
                                            <canvas id="sig-{{ $fid }}" class="w-full h-36 cursor-crosshair"></canvas>
                                        </div>
                                        <input type="hidden" wire:model="responses.{{ $fid }}" id="sig-input-{{ $fid }}">
                                        <div class="flex items-center justify-between mt-1.5">
                                            <span class="text-xs text-gray-400">Tanda tangan di area di atas</span>
                                            <button type="button" onclick="clearSig(this)" data-fid="{{ $fid }}"
                                                class="text-xs text-red-400 hover:text-red-600 font-medium transition-colors">Hapus</button>
                                        </div>
                                        <script>
                                            (function() {
                                                var fid = "{{ $fid }}";
                                                var canvas = document.getElementById('sig-' + fid);
                                                if (!canvas) return;
                                                var ctx = canvas.getContext('2d');
                                                var drawing = false;
                                                var imageData = null;

                                                function resizeCanvas() {
                                                    imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                                                    canvas.width = canvas.offsetWidth;
                                                    canvas.height = canvas.offsetHeight;
                                                    ctx.putImageData(imageData, 0, 0);
                                                    ctx.strokeStyle = '#1f2937';
                                                    ctx.lineWidth = 2.5;
                                                    ctx.lineCap = 'round';
                                                    ctx.lineJoin = 'round';
                                                }
                                                resizeCanvas();
                                                window.addEventListener('resize', resizeCanvas);
                                                canvas.addEventListener('mousedown', function() { drawing = true; });
                                                canvas.addEventListener('mouseup', function() { drawing = false; ctx.beginPath(); saveSig(fid); });
                                                canvas.addEventListener('mouseleave', function() { if (drawing) { drawing = false; ctx.beginPath(); saveSig(fid); } });
                                                canvas.addEventListener('mousemove', function(e) {
                                                    if (!drawing) return;
                                                    var rect = canvas.getBoundingClientRect();
                                                    ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
                                                    ctx.stroke();
                                                    ctx.beginPath();
                                                    ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
                                                });
                                                canvas.addEventListener('touchstart', function(e) { e.preventDefault(); drawing = true; });
                                                canvas.addEventListener('touchend', function(e) { e.preventDefault(); drawing = false; ctx.beginPath(); saveSig(fid); });
                                                canvas.addEventListener('touchmove', function(e) {
                                                    e.preventDefault();
                                                    if (!drawing) return;
                                                    var rect = canvas.getBoundingClientRect();
                                                    var touch = e.touches[0];
                                                    ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
                                                    ctx.stroke();
                                                    ctx.beginPath();
                                                    ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
                                                });
                                            })();
                                            function saveSig(id) {
                                                var canvas = document.getElementById('sig-' + id);
                                                document.getElementById('sig-input-' + id).value = canvas.toDataURL();
                                            }
                                            function clearSig(el) {
                                                var id = el.getAttribute('data-fid');
                                                var canvas = document.getElementById('sig-' + id);
                                                var ctx = canvas.getContext('2d');
                                                ctx.clearRect(0, 0, canvas.width, canvas.height);
                                                document.getElementById('sig-input-' + id).value = '';
                                            }
                                        </script>
                                    @endif

                                    {{-- Heading --}}
                                    @if ($field->type->value === 'heading')
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 font-display">{{ $field->label }}</h3>
                                    @endif

                                    {{-- Paragraph --}}
                                    @if ($field->type->value === 'paragraph')
                                        <div class="bg-blue-50/50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-xl px-4 py-3">
                                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $field->label }}</p>
                                        </div>
                                    @endif

                                    {{-- Computed --}}
                                    @if ($field->type->value === 'computed')
                                        <div class="bg-gradient-to-r from-kbb-700/5 to-transparent dark:from-kbb-400/10 border border-kbb-700/10 dark:border-kbb-400/20 rounded-xl px-4 py-3">
                                            <span class="text-xs text-gray-400 dark:text-gray-500 block mb-0.5">{{ $field->label }}</span>
                                            <span class="text-base font-bold text-kbb-700 dark:text-kbb-400 font-display">
                                                Rp {{ number_format((float) ($responses[$field->id] ?? 0), 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif

                                    @error("responses.{$field->id}")
                                        <div class="flex items-center gap-1.5 mt-1.5">
                                            <svg class="w-3.5 h-3.5 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                            <p class="text-xs text-red-500">{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Error --}}
                    @if ($error)
                        <div class="flex items-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-500/15 border border-red-200 dark:border-red-500/20 text-red-600 dark:text-red-400 rounded-xl text-sm mb-4">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ $error }}
                        </div>
                    @endif

                    {{-- Navigation --}}
                    <div class="flex items-center justify-between pt-5 mt-5 border-t border-gray-100 dark:border-slate-700">
                        <div>
                            @if ($currentStep > 1)
                                <button type="button" wire:click="prevStep"
                                    class="flex items-center gap-1.5 bg-gray-50 dark:bg-slate-700 hover:bg-gray-100 dark:hover:bg-slate-600 text-gray-500 dark:text-gray-400 font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 text-sm border border-gray-200 dark:border-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Sebelumnya
                                </button>
                            @endif
                        </div>
                        <div>
                            @if ($currentStep < $totalSteps)
                                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                                    class="flex items-center gap-1.5 bg-kbb-700 hover:bg-[#002a5c] disabled:opacity-50 text-white font-semibold px-6 py-2.5 rounded-xl transition-all duration-200 text-sm shadow-md shadow-kbb-700/20">
                                    <span wire:loading.remove wire:target="nextStep">Selanjutnya</span>
                                    <span wire:loading wire:target="nextStep">Memuat...</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            @else
                                <button type="submit" wire:loading.attr="disabled"
                                    class="flex items-center gap-2 bg-gradient-to-r from-kbb-700 to-[#004a9c] hover:from-[#002a5c] hover:to-kbb-700 disabled:opacity-50 text-white font-bold px-8 py-2.5 rounded-xl transition-all duration-200 text-sm shadow-lg shadow-kbb-700/25">
                                    <span wire:loading.remove wire:target="submitForm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        Kirim
                                    </span>
                                    <span wire:loading wire:target="submitForm">Mengirim...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Success --}}
    @if ($submitted)
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-10 text-center border border-gray-100 dark:border-slate-700">
            <div class="w-20 h-20 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-200">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="font-display text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-2 tracking-tight">Terima Kasih!</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto leading-relaxed">{{ $form?->confirmation_message ?? 'Jawaban Anda telah berhasil dikirim dan dicatat.' }}</p>
            <div class="mt-6 w-16 h-[2px] bg-[#C8A45C]/40 rounded-full mx-auto"></div>
        </div>
    @endif
</div>
