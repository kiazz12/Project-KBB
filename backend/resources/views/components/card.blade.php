<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-gray-100']) }}>
    @if(isset($header))
        <div class="px-5 py-4 border-b border-gray-100">
            {{ $header }}
        </div>
    @endif
    <div class="{{ isset($header) ? 'p-5' : 'p-5' }}">
        {{ $slot }}
    </div>
</div>
