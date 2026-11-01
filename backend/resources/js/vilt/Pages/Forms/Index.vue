<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import RoleLayout from '../../Layouts/RoleLayout.vue';

const props = defineProps({
    forms: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '', status: '' }) },
    isSuperAdmin: { type: Boolean, default: false },
});

const filter = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
});

function applyFilters() {
    router.get('/forms', {
        search: filter.search || undefined,
        status: filter.status || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}

function resetFilters() {
    filter.search = '';
    filter.status = '';
    router.get('/forms', {}, { preserveState: true, replace: true });
}

function duplicate(form) {
    if (!window.confirm('Gandakan form ini sebagai draft?')) {
        return;
    }

    router.post(`/forms/${form.id}/duplicate`);
}

function statusClasses(status) {
    if (status === 'published') {
        return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400';
    }

    if (status === 'closed') {
        return 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400';
    }

    return 'bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-gray-300';
}

function statusDot(status) {
    if (status === 'published') {
        return 'bg-emerald-500';
    }

    if (status === 'closed') {
        return 'bg-red-500';
    }

    return 'bg-gray-400';
}

function paginationLabel(label) {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›');
}
</script>

<template>
    <Head title="Forms" />

    <RoleLayout>
        <template #title>Forms</template>

        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] p-6 text-white shadow-2xl sm:p-8">
            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-medium uppercase tracking-wider text-kbb-200">Forms</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Kelola Form</h1>
                    <p class="mt-1 text-sm text-kbb-200/80">
                        {{ isSuperAdmin ? 'Semua form dari seluruh admin' : 'Semua form yang Anda miliki' }}
                    </p>
                </div>
                <a
                    href="/forms/create"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/15 px-5 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition-all hover:scale-[1.02] hover:bg-white/25 hover:shadow-lg active:scale-[0.98]"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Form Baru
                </a>
            </div>
        </div>

        <form class="mb-6 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800" @submit.prevent="applyFilters">
            <div class="flex flex-wrap gap-3">
                <div class="relative min-w-[200px] flex-1">
                    <svg class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="filter.search"
                        type="search"
                        placeholder="Cari form..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                    >
                </div>
                <select
                    v-model="filter.status"
                    class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                >
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="closed">Closed</option>
                </select>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-slate-700 dark:text-gray-100 dark:hover:bg-slate-600"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                <button
                    v-if="filter.search || filter.status"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    @click="resetFilters"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset
                </button>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/80 text-left text-gray-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-gray-400">
                            <th class="px-5 py-3.5 font-semibold">Judul</th>
                            <th v-if="isSuperAdmin" class="px-5 py-3.5 font-semibold">Pembuat</th>
                            <th class="px-5 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 text-center font-semibold">Fields</th>
                            <th class="px-5 py-3.5 text-center font-semibold">Respons</th>
                            <th class="px-5 py-3.5 font-semibold">Dibuat</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                        <tr
                            v-for="form in forms.data"
                            :key="form.id"
                            class="group transition-all hover:bg-gray-50/80 dark:hover:bg-slate-700/40"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-kbb-50 to-kbb-100 transition-transform group-hover:scale-110 dark:from-kbb-500/15 dark:to-kbb-500/5">
                                        <svg class="h-5 w-5 text-kbb-700 dark:text-kbb-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="max-w-[220px] truncate font-semibold text-gray-900 dark:text-gray-100">{{ form.title }}</p>
                                        <p v-if="form.description" class="max-w-[220px] truncate text-xs text-gray-400">{{ form.description }}</p>
                                    </div>
                                </div>
                            </td>
                            <td v-if="isSuperAdmin" class="px-5 py-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ form.user?.name || 'Deleted' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses(form.status)">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="statusDot(form.status)"></span>
                                    {{ form.status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ form.fields_count }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ form.submissions_count }}</span>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-400">{{ new Date(form.created_at).toLocaleDateString('id-ID') }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="`/forms/${form.id}`" class="rounded-lg p-2 text-kbb-700 transition hover:bg-kbb-50 dark:text-kbb-400 dark:hover:bg-slate-700" title="Detail">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </Link>
                                    <a :href="`/forms/${form.id}/edit`" class="rounded-lg p-2 text-amber-600 transition hover:bg-amber-50 dark:hover:bg-amber-500/10" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <button type="button" class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 dark:hover:bg-slate-700" title="Gandakan" @click="duplicate(form)">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!forms.data.length">
                            <td :colspan="isSuperAdmin ? 7 : 6" class="px-5 py-10 text-center">
                                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 dark:bg-slate-700">
                                    <svg class="h-7 w-7 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-400">
                                    {{ filter.search || filter.status ? 'Tidak ada form yang cocok dengan filter' : 'Belum ada form' }}
                                </p>
                                <a v-if="!filter.search && !filter.status" href="/forms/create" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-kbb-700 hover:text-kbb-800 dark:text-kbb-400 dark:hover:text-kbb-300">
                                    Buat form pertama
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="forms.last_page > 1" class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/50 px-5 py-4 dark:border-slate-700 dark:bg-slate-900/40">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan {{ forms.from }}–{{ forms.to }} dari {{ forms.total }} form
                </p>
                <div class="flex flex-wrap items-center gap-1">
                    <template v-for="(link, index) in forms.links" :key="index">
                        <span
                            v-if="!link.url"
                            class="rounded-lg px-3 py-1.5 text-xs text-gray-300 dark:text-gray-600"
                        >{{ paginationLabel(link.label) }}</span>
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                            :class="link.active
                                ? 'bg-kbb-700 text-white'
                                : 'bg-white text-gray-600 hover:bg-gray-100 dark:bg-slate-800 dark:text-gray-300 dark:hover:bg-slate-700'"
                        >{{ paginationLabel(link.label) }}</Link>
                    </template>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
