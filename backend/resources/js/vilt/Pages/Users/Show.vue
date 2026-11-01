<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    user: { type: Object, required: true },
    forms: { type: Object, required: true },
});

function statusClasses(status) {
    if (status === 'published') {
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400';
    }

    if (status === 'closed') {
        return 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400';
    }

    return 'bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-gray-400';
}

function paginationLabel(label) {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›');
}
</script>

<template>
    <Head :title="user.name" />

    <AppLayout>
        <template #title>{{ user.name }}</template>

        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] p-6 text-white shadow-2xl sm:p-8">
            <div class="relative">
                <div class="mb-2 flex items-center gap-2">
                    <Link href="/users" class="text-kbb-200 transition hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <span class="text-xs font-medium uppercase tracking-wider text-kbb-200">Detail User</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight">{{ user.name }}</h1>
                <p class="mt-1 text-sm text-kbb-200/80">
                    {{ user.email }} &middot; {{ user.is_super_admin ? 'Super Admin' : 'Admin' }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <div class="space-y-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Info User</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="break-all text-right text-gray-900 dark:text-gray-200">{{ user.email }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Role</dt>
                            <dd class="text-gray-900 dark:text-gray-200">{{ user.is_super_admin ? 'Super Admin' : 'Admin' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">OPD</dt>
                            <dd class="text-gray-900 dark:text-gray-200">{{ user.opd || '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">NIP</dt>
                            <dd class="text-gray-900 dark:text-gray-200">{{ user.nip || '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Total Forms</dt>
                            <dd class="text-gray-900 dark:text-gray-200">{{ user.forms_count }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Dibuat</dt>
                            <dd class="text-gray-900 dark:text-gray-200">{{ user.created_at }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Forms oleh {{ user.name }}</h2>

                    <div v-if="forms.data.length" class="space-y-3">
                        <Link
                            v-for="form in forms.data"
                            :key="form.id"
                            :href="form.url"
                            class="flex items-center justify-between rounded-xl p-3 transition hover:bg-gray-50 dark:hover:bg-slate-700/50"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ form.title }}</p>
                                <p class="mt-0.5 text-xs text-gray-400">{{ form.submissions_count }} pengiriman &middot; {{ form.fields_count }} field</p>
                            </div>
                            <span class="flex-shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold" :class="statusClasses(form.status)">
                                {{ form.status_label }}
                            </span>
                        </Link>
                    </div>

                    <p v-else class="py-8 text-center text-sm text-gray-400">User ini belum membuat form.</p>

                    <div v-if="forms.last_page > 1" class="mt-4 flex items-center justify-end gap-1 border-t border-gray-100 pt-4 dark:border-slate-700">
                        <template v-for="(link, index) in forms.links" :key="index">
                            <span v-if="!link.url" class="rounded-lg px-3 py-1.5 text-xs text-gray-300 dark:text-gray-600" v-html="paginationLabel(link.label)"></span>
                            <Link
                                v-else
                                :href="link.url"
                                preserve-scroll
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                                :class="link.active ? 'bg-kbb-700 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600'"
                                v-html="paginationLabel(link.label)"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
