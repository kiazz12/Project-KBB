<div class="py-16 text-center">
    @if(isset($icon))
        <div class="mb-3">{{ $icon }}</div>
    @else
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
    @endif
    <p class="text-sm text-gray-400 mb-1">{{ $title ?? 'Tidak ada data' }}</p>
    @if(isset($description))
        <p class="text-xs text-gray-300">{{ $description }}</p>
    @endif
    {{ $slot ?? '' }}
</div>
