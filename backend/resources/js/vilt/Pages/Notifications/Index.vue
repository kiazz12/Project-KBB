<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    notifications: { type: Object, required: true },
    unreadCount: { type: Number, default: 0 },
});

const hasUnread = computed(() => props.unreadCount > 0);

function markRead(notification) {
    router.post(`/notifications/${notification.id}/read`, {}, { preserveScroll: true });
}

function markAllRead() {
    router.post('/notifications/mark-all-read', {}, { preserveScroll: true });
}

function paginationLabel(label) {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›');
}
</script>

<template>
    <Head title="Notifikasi" />

    <AppLayout>
        <template #title>Notifikasi</template>

        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] p-6 text-white shadow-2xl sm:p-8">
            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-medium uppercase tracking-wider text-kbb-200">Notifikasi</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Notifikasi</h1>
                    <p class="mt-1 text-sm text-kbb-200/80">{{ unreadCount }} belum dibaca</p>
                </div>

                <button
                    v-if="hasUnread"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/15 px-5 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition-all hover:scale-[1.02] hover:bg-white/25 hover:shadow-lg active:scale-[0.98]"
                    @click="markAllRead"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Tandai Semua Dibaca
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div
                v-for="(notification, index) in notifications.data"
                :key="notification.id"
                class="flex items-start gap-4 p-5"
                :class="[
                    index > 0 ? 'border-t border-gray-100 dark:border-slate-700' : '',
                    !notification.is_read ? 'bg-kbb-50/50 dark:bg-kbb-500/10' : '',
                ]"
            >
                <div
                    class="mt-0.5 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full"
                    :class="notification.is_read
                        ? 'bg-gray-100 dark:bg-slate-700'
                        : 'bg-kbb-100 dark:bg-kbb-500/20'"
                >
                    <svg
                        class="h-5 w-5"
                        :class="notification.is_read ? 'text-gray-400 dark:text-gray-500' : 'text-kbb-700 dark:text-kbb-400'"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ notification.message }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ notification.created_at_human }}</p>
                </div>
                <button
                    v-if="!notification.is_read"
                    type="button"
                    class="whitespace-nowrap text-xs font-medium text-kbb-700 hover:text-kbb-800 dark:text-kbb-400 dark:hover:text-kbb-300"
                    @click="markRead(notification)"
                >
                    Tandai dibaca
                </button>
            </div>

            <div v-if="!notifications.data.length" class="py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-200 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="mt-2 text-sm text-gray-400">Tidak ada notifikasi</p>
            </div>

            <div v-if="notifications.last_page > 1" class="border-t border-gray-100 px-5 py-4 dark:border-slate-700">
                <div class="flex flex-wrap items-center justify-center gap-1">
                    <template v-for="(link, index) in notifications.links" :key="index">
                        <span
                            v-if="!link.url"
                            class="rounded-lg px-3 py-1.5 text-xs text-gray-300 dark:text-gray-600"
                            v-html="paginationLabel(link.label)"
                        ></span>
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                            :class="link.active
                                ? 'bg-kbb-700 text-white'
                                : 'bg-white text-gray-600 hover:bg-gray-100 dark:bg-slate-800 dark:text-gray-300 dark:hover:bg-slate-700'"
                            v-html="paginationLabel(link.label)"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
