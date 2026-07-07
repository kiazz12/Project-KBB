<div class="flex items-center gap-3 mb-6">
    @if(isset($back))
        <a href="{{ $back }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
    @endif
    <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
        @if(isset($description))
            <p class="text-sm text-gray-500 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    {{ $slot ?? '' }}
</div>
