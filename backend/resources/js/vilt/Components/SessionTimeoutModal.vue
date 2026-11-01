<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const TIMEOUT_SECONDS = 300; // 5 menit
const WARNING_SECONDS = 120; // 2 menit

const remaining = ref(TIMEOUT_SECONDS);
const showWarning = ref(false);
const isExtending = ref(false);

let countdownInterval = null;
let lastPingTime = 0;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function formatCountdown(sec) {
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return `${m}:${s < 10 ? '0' : ''}${s}`;
}

async function pingBackend() {
    try {
        await fetch('/session/ping', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
        });
    } catch {
        // ignore background ping error
    }
}

function recordUserActivity() {
    if (!user.value) return;

    remaining.value = TIMEOUT_SECONDS;
    showWarning.value = false;

    const now = Date.now();
    if (now - lastPingTime > 30000) { // Throttle ping ke backend maksimal setiap 30 detik
        lastPingTime = now;
        pingBackend();
    }
}

async function extendSession() {
    isExtending.value = true;
    try {
        await fetch('/session/extend', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
        });
        remaining.value = TIMEOUT_SECONDS;
        showWarning.value = false;
        lastPingTime = Date.now();
    } finally {
        isExtending.value = false;
    }
}

function handleTimeout() {
    if (countdownInterval) clearInterval(countdownInterval);
    router.post('/logout');
}

onMounted(() => {
    if (!user.value) return;

    const events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
    events.forEach((ev) => window.addEventListener(ev, recordUserActivity, { passive: true }));

    countdownInterval = setInterval(() => {
        if (!user.value) return;

        remaining.value--;

        if (remaining.value <= WARNING_SECONDS && remaining.value > 0) {
            showWarning.value = true;
        } else if (remaining.value <= 0) {
            showWarning.value = false;
            handleTimeout();
        }
    }, 1000);
});

onUnmounted(() => {
    if (countdownInterval) clearInterval(countdownInterval);
    const events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
    events.forEach((ev) => window.removeEventListener(ev, recordUserActivity));
});
</script>

<template>
    <div
        v-if="showWarning"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs"
    >
        <div class="w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl dark:bg-slate-800 sm:p-8">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-gray-100">Peringatan Waktu Sesi</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Sesi kerja Anda akan berakhir dalam:
            </p>

            <div class="my-4 font-mono text-3xl font-extrabold text-amber-600 dark:text-amber-400">
                {{ formatCountdown(remaining) }}
            </div>

            <p class="text-[11px] text-gray-400">
                Karena tidak ada aktivitas, Anda akan otomatis logout demi keamanan data.
            </p>

            <div class="mt-6">
                <button
                    type="button"
                    :disabled="isExtending"
                    class="w-full rounded-xl bg-kbb-700 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-kbb-800 disabled:opacity-50 dark:bg-kbb-600"
                    @click="extendSession"
                >
                    {{ isExtending ? 'Memperpanjang…' : 'Perpanjang Sesi Sekarang' }}
                </button>
            </div>
        </div>
    </div>
</template>
