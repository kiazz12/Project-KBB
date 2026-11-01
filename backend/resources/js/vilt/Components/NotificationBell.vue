<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const open = ref(false);
const loading = ref(false);
const count = ref(0);
const notifications = ref([]);
let timer = null;

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

async function refresh() {
    loading.value = true;

    try {
        const response = await fetch('/notifications/unread', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        count.value = payload.data?.count ?? 0;
        notifications.value = payload.data?.notifications ?? [];
    } finally {
        loading.value = false;
    }
}

async function markRead(id) {
    await fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        credentials: 'same-origin',
    });

    await refresh();
}

async function markAllRead() {
    await fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        credentials: 'same-origin',
    });

    await refresh();
}

function formatDate(value) {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

onMounted(() => {
    refresh();
    timer = setInterval(refresh, 10000);
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="relative rounded-xl p-2 text-gray-500 transition hover:bg-gray-100 hover:text-kbb-700 dark:text-gray-400 dark:hover:bg-slate-800 dark:hover:text-kbb-300"
            aria-label="Notifikasi"
            @click="open = !open"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="count > 0"
                class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
            >
                {{ count > 9 ? '9+' : count }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 z-40 mt-2 w-80 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-800"
        >
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-slate-700">
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifikasi</p>
                <button
                    v-if="count > 0"
                    type="button"
                    class="text-xs font-medium text-kbb-700 hover:text-kbb-800 dark:text-kbb-400"
                    @click="markAllRead"
                >
                    Tandai semua
                </button>
            </div>

            <div class="max-h-80 overflow-y-auto">
                <p v-if="loading && notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-400">
                    Memuat…
                </p>
                <p v-else-if="notifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-400">
                    Tidak ada notifikasi baru
                </p>
                <button
                    v-for="notification in notifications"
                    :key="notification.id"
                    type="button"
                    class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-gray-50 dark:hover:bg-slate-700/50"
                    @click="markRead(notification.id)"
                >
                    <span class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full bg-kbb-500"></span>
                    <span class="min-w-0">
                        <span class="block text-sm text-gray-700 dark:text-gray-300">{{ notification.message }}</span>
                        <span class="mt-0.5 block text-xs text-gray-400">{{ formatDate(notification.created_at) }}</span>
                    </span>
                </button>
            </div>

            <a
                href="/notifications"
                class="block border-t border-gray-100 px-4 py-2.5 text-center text-xs font-medium text-kbb-700 hover:bg-gray-50 dark:border-slate-700 dark:text-kbb-400 dark:hover:bg-slate-700/50"
            >
                Lihat semua notifikasi
            </a>
        </div>
    </div>
</template>
