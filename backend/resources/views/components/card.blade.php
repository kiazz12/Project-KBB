<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700']) }}>
    @if(isset($header))
        <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700">
            {{ $header }}
        </div>
    @endif
    <div class="{{ isset($header) ? 'p-5' : 'p-5' }}">
        {{ $slot }}
    </div>
</div>
