<div wire:poll.30s="checkSession" id="session-timeout-component">
    {{-- Warning Modal --}}
    @if ($showWarning && !$expired)
        <div id="session-timeout-modal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm" style="animation: stFadeIn 0.2s ease-out">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-sm w-full mx-4 overflow-hidden" style="animation: stIn 0.3s ease-out">
                {{-- Header --}}
                <div class="px-6 pt-6 pb-4 text-center">
                    <div class="w-14 h-14 mx-auto mb-4 bg-amber-100 dark:bg-amber-500/15 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">Session Akan Habis</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sesi Anda akan berakhir dalam <span id="session-countdown" class="font-semibold text-amber-600 dark:text-amber-400">{{ floor($remainingSeconds / 60) }}:{{ str_pad($remainingSeconds % 60, 2, '0', STR_PAD_LEFT) }}</span></p>
                </div>

                {{-- Progress Bar --}}
                <div class="px-6 pb-4">
                    <div class="w-full bg-gray-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                        <div id="session-progress-bar" class="h-full rounded-full transition-all duration-1000 ease-linear {{ $remainingSeconds > 60 ? 'bg-amber-400' : 'bg-red-500' }}" style="width: {{ ($remainingSeconds / 300) * 100 }}%"></div>
                    </div>
                </div>

                {{-- Message --}}
                <div class="px-6 pb-4">
                    <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl p-3">
                        <p class="text-sm text-amber-800 dark:text-amber-300 text-center">
                            Anda tidak aktif selama beberapa menit. Untuk menjaga keamanan akun, sesi Anda akan otomatis berakhir jika tidak diperpanjang.
                        </p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="px-6 pb-6 flex gap-3">
                    <button
                        wire:click="logout"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-xl transition"
                    >
                        Logout
                    </button>
                    <button
                        wire:click="extendSession"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-kbb-700 hover:bg-kbb-800 rounded-xl transition shadow-sm"
                    >
                        Perpanjang Sesi
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Expired Modal --}}
    @if ($expired)
        <div id="session-expired-modal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-sm w-full mx-4 p-6 text-center" style="animation: stIn 0.3s ease-out">
                <div class="w-14 h-14 mx-auto mb-4 bg-red-100 dark:bg-red-500/15 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364A9 9 0 1112 3a9 9 0 017.364 4.636z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Session Berakhir</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali untuk melanjutkan.</p>
                <a href="/login" class="block w-full bg-kbb-700 hover:bg-kbb-800 text-white font-medium py-2.5 rounded-xl transition text-center">
                    Login Kembali
                </a>
            </div>
        </div>
    @endif

    <style>
        @keyframes stIn { 0%{transform:scale(0.9) translateY(10px);opacity:0} 100%{transform:scale(1) translateY(0);opacity:1} }
        @keyframes stFadeIn { 0%{opacity:0} 100%{opacity:1} }
    </style>
</div>

@script
<script>
    let remainingSeconds = @js($remainingSeconds);
    let countdownInterval = null;

    function updateCountdown() {
        if (remainingSeconds <= 0) return;

        remainingSeconds--;
        let minutes = Math.floor(remainingSeconds / 60);
        let secs = remainingSeconds % 60;
        let display = minutes + ':' + String(secs).padStart(2, '0');

        let el = document.getElementById('session-countdown');
        if (el) el.textContent = display;

        let bar = document.getElementById('session-progress-bar');
        if (bar) {
            let pct = (remainingSeconds / 300) * 100;
            bar.style.width = pct + '%';
            bar.className = bar.className.replace('bg-amber-400', '').replace('bg-red-500', '');
            bar.classList.add(remainingSeconds > 60 ? 'bg-amber-400' : 'bg-red-500');
        }
    }

    function startCountdown() {
        if (countdownInterval) clearInterval(countdownInterval);
        countdownInterval = setInterval(updateCountdown, 1000);
    }

    function stopCountdown() {
        if (countdownInterval) clearInterval(countdownInterval);
    }

    if (remainingSeconds > 0 && remainingSeconds <= 120) {
        startCountdown();
    }

    Livewire.on('session-extended', () => {
        remainingSeconds = 300;
        stopCountdown();
        startCountdown();
    });

    Livewire.on('session-expired', () => {
        stopCountdown();
        setTimeout(() => { window.location.href = '/login'; }, 1500);
    });

    $wire.$hook('message.processed', () => {
        remainingSeconds = $wire.remainingSeconds;
        if (remainingSeconds > 0 && remainingSeconds <= 120) {
            startCountdown();
        } else {
            stopCountdown();
        }
    });
</script>
@endscript
