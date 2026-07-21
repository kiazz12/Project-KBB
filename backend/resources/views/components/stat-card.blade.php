<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-5']) }}>
    <p class="text-xs font-medium {{ $color ?? 'text-gray-500' }} uppercase tracking-wider">{{ $label }}</p>
    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $value }}</p>
    @if(isset($subtext))
        <p class="text-xs text-gray-400 mt-1">{{ $subtext }}</p>
    @endif
</div>
