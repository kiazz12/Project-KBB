<div class="w-full max-w-2xl">
    @if ($error && !$form)
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <p class="text-gray-500">{{ $error }}</p>
        </div>
    @endif

    @if ($form && !$submitted)
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-kbb-700 px-8 py-6">
                <h1 class="text-2xl font-bold text-white">{{ $form->title }}</h1>
                @if ($form->description)
                    <p class="text-kbb-200 text-sm mt-1">{{ $form->description }}</p>
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
