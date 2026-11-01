<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    recentForms: { type: Array, default: () => [] },
    topForms: { type: Array, default: () => [] },
    latestSubmissions: { type: Array, default: () => [] },
    activity: { type: Object, required: true },
    today: { type: String, default: '' },
    isSuperAdmin: { type: Boolean, default: false },
});

const ranges = [
    { value: '7d', label: '7 Hari' },
    { value: '1m', label: '1 Bulan' },
    { value: '3m', label: '3 Bulan' },
    { value: '6m', label: '6 Bulan' },
    { value: '1y', label: '1 Tahun' },
];

const range = ref('7d');
const chart = ref({ ...props.activity });
const chartLoading = ref(false);

async function loadRange(value) {
    if (range.value === value && chart.value !== null) {
        return;
    }

    range.value = value;
    chartLoading.value = true;

    try {
        const response = await fetch(`/dashboard/activity?range=${value}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (response.ok) {
            chart.value = await response.json();
        }
    } finally {
        chartLoading.value = false;
    }
}

const barHeights = computed(() => {
    const max = chart.value?.max || 0;

    return (chart.value?.data ?? []).map((value) => {
        if (!max || !value) {
            return '3px';
        }

        return `${Math.max(4, Math.round((value / max) * 100))}%`;
    });
});

const labelStep = computed(() => {
    const count = (chart.value?.labels ?? []).length;

    if (count > 24) {
        return 3;
    }

    if (count > 14) {
        return 2;
    }

    return 1;
});

const statusRows = computed(() => {
    const total = props.stats.total_forms || 0;
    const pct = (value) => (total > 0 ? Math.round((value / total) * 100) : 0);

    return [
        { label: 'Published', value: props.stats.published_forms, color: 'bg-emerald-500', width: pct(props.stats.published_forms), dot: 'bg-emerald-500' },
        { label: 'Draft', value: props.stats.draft_forms, color: 'bg-gray-400', width: pct(props.stats.draft_forms), dot: 'bg-gray-400' },
        { label: 'Closed', value: props.stats.closed_forms, color: 'bg-red-400', width: pct(props.stats.closed_forms), dot: 'bg-red-400' },
    ];
});

const formsWithResponsesPct = computed(() => {
    const published = props.stats.published_forms || 0;

    return published > 0 ? Math.round((props.stats.forms_with_submissions / published) * 100) : 0;
});

const publishedPct = computed(() => {
    const total = props.stats.total_forms || 0;

    return total > 0 ? Math.round((props.stats.published_forms / total) * 100) : 0;
});

function statusClasses(status) {
    if (status === 'published') {
        return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400';
    }

    if (status === 'closed') {
        return 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400';
    }

    return 'bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-gray-400';
}

function rankClasses(index) {
    if (index === 0) {
        return 'bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-sm';
    }

    if (index === 1) {
        return 'bg-gradient-to-br from-gray-300 to-gray-400 text-white shadow-sm';
    }

    if (index === 2) {
        return 'bg-gradient-to-br from-orange-300 to-orange-400 text-white shadow-sm';
    }

    return 'bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-gray-400';
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <template #title>Dashboard</template>

        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] p-6 text-white shadow-2xl sm:p-8">
            <div class="relative">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            <span class="text-xs font-medium uppercase tracking-wider text-kbb-200">Dashboard</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                            Selamat datang, {{ $page.props.auth?.user?.display_name || $page.props.auth?.user?.name }}
                        </h1>
                        <p class="mt-1.5 flex flex-wrap items-center gap-2 text-sm text-kbb-200/80">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ isSuperAdmin ? 'Super Admin' : 'Admin' }}
                            <span class="text-kbb-200/40">&bull;</span>
                            {{ today }}
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
        </div>

        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Forms</p>
                        <p class="mt-1.5 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_forms }}</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                            <span class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                {{ stats.published_forms }} published
                            </span>
                            <span class="flex items-center gap-1.5 rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-500 dark:bg-slate-700 dark:text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                {{ stats.draft_forms }} draft
                            </span>
                        </div>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-kbb-50 to-kbb-100 transition-all group-hover:scale-110 dark:from-kbb-500/15 dark:to-kbb-500/5">
                        <svg class="h-6 w-6 text-kbb-700 dark:text-kbb-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Respons</p>
                        <p class="mt-1.5 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_submissions }}</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                            <span class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                {{ stats.submissions_today }} hari ini
                            </span>
                            <span class="flex items-center gap-1.5 rounded-full bg-kbb-50 px-2 py-0.5 font-medium text-kbb-700 dark:bg-kbb-500/15 dark:text-kbb-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ stats.submissions_this_week }} minggu ini
                            </span>
                        </div>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 transition-all group-hover:scale-110 dark:from-emerald-500/15 dark:to-emerald-500/5">
                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Published</p>
                        <p class="mt-1.5 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.published_forms }}</p>
                        <div class="mt-3 space-y-1 text-xs">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-0.5 font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                {{ publishedPct }}% dari total
                            </span>
                            <p class="mt-1 px-1 text-gray-400 dark:text-gray-500">{{ stats.avg_submissions_per_form }} rata-rata/form</p>
                        </div>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 transition-all group-hover:scale-110 dark:from-amber-500/15 dark:to-amber-500/5">
                        <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div v-if="isSuperAdmin" class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pengguna</p>
                        <p class="mt-1.5 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_users }}</p>
                        <div class="mt-3 text-xs">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-50 px-2 py-0.5 font-medium text-purple-700 dark:bg-purple-500/15 dark:text-purple-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ stats.admin_count }} admin
                            </span>
                        </div>
                        <a href="/users" class="mt-1.5 inline-flex items-center gap-1 px-1 text-xs font-medium text-kbb-700 hover:text-kbb-800 dark:text-kbb-400 dark:hover:text-kbb-300">
                            Kelola Pengguna
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 transition-all group-hover:scale-110 dark:from-purple-500/15 dark:to-purple-500/5">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <a
                v-else
                href="/forms/create"
                class="group flex items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-kbb-400 hover:bg-kbb-50/30 hover:shadow-lg dark:border-slate-600 dark:bg-slate-800 dark:hover:bg-kbb-500/10"
            >
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 transition-all group-hover:scale-110 group-hover:bg-kbb-100 dark:bg-slate-700 dark:group-hover:bg-kbb-500/20">
                        <svg class="h-6 w-6 text-gray-400 transition-colors group-hover:text-kbb-700 dark:text-gray-500 dark:group-hover:text-kbb-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <p class="mt-3 text-sm font-medium text-gray-500 transition-colors group-hover:text-kbb-700 dark:text-gray-400 dark:group-hover:text-kbb-400">Buat Form Baru</p>
                </div>
            </a>
        </div>

        <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 lg:col-span-2">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-kbb-500 to-kbb-700 shadow-sm">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Aktivitas Pengiriman</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Jumlah pengiriman formulir per periode</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-kbb-50 px-3 py-1 text-xs font-semibold text-kbb-700 dark:bg-kbb-500/15 dark:text-kbb-400">
                        {{ chart.total ?? 0 }} total
                    </span>
                </div>

                <div class="mb-5 flex flex-wrap gap-2">
                    <button
                        v-for="option in ranges"
                        :key="option.value"
                        type="button"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                        :class="range === option.value
                            ? 'bg-kbb-700 text-white shadow-sm'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600'"
                        @click="loadRange(option.value)"
                    >
                        {{ option.label }}
                    </button>
                </div>

                <div class="relative flex h-56 items-end gap-1.5 rounded-xl bg-gray-50/70 px-3 pb-8 pt-4 dark:bg-slate-900/40 sm:gap-2">
                    <p v-if="chartLoading" class="absolute inset-0 flex items-center justify-center bg-white/50 text-sm text-gray-500 dark:bg-slate-800/50">
                        Memuat…
                    </p>
                    <div
                        v-for="(value, index) in chart.data"
                        :key="`${range}-${index}`"
                        class="group relative flex h-full flex-1 flex-col items-center justify-end"
                        :title="`${chart.labels[index]}: ${value} respons`"
                    >
                        <span class="mb-1 text-[10px] font-semibold text-gray-500 opacity-0 transition group-hover:opacity-100 dark:text-gray-400">{{ value }}</span>
                        <div
                            class="w-full max-w-10 rounded-t-md bg-gradient-to-t from-kbb-700 to-kbb-500 transition-all duration-300 hover:from-kbb-800 hover:to-kbb-400"
                            :style="{ height: barHeights[index] }"
                        ></div>
                        <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 whitespace-nowrap text-[10px] text-gray-400">
                            {{ index % labelStep === 0 ? chart.labels[index] : '' }}
                        </span>
                    </div>

                    <p v-if="!chart.data?.length" class="absolute inset-0 flex items-center justify-center text-sm text-gray-400">
                        Belum ada data
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Status Form</h2>
                </div>

                <div v-if="stats.total_forms > 0" class="space-y-5">
                    <div v-for="row in statusRows" :key="row.label">
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full" :class="row.dot"></span>
                                <span class="font-medium text-gray-600 dark:text-gray-300">{{ row.label }}</span>
                            </span>
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ row.value }}</span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-slate-700">
                            <div class="h-full rounded-full transition-all duration-500" :class="row.color" :style="{ width: row.width + '%' }"></div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-3 dark:border-slate-700">
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-kbb-500"></span>
                                <span class="font-medium text-gray-600 dark:text-gray-300">Form dengan respons</span>
                            </span>
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ stats.forms_with_submissions }} / {{ stats.published_forms }}</span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-slate-700">
                            <div class="h-full rounded-full bg-gradient-to-r from-kbb-600 to-kbb-500 transition-all duration-500" :style="{ width: formsWithResponsesPct + '%' }"></div>
                        </div>
                    </div>
                </div>

                <div v-else class="py-10 text-center">
                    <p class="text-sm text-gray-400">Belum ada form</p>
                </div>
            </div>
        </div>

        <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Form Terpopuler</h2>
                    <a href="/forms" class="inline-flex items-center gap-1 text-sm font-medium text-kbb-700 hover:text-kbb-800 dark:text-kbb-400 dark:hover:text-kbb-300">
                        Lihat Semua
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div v-if="topForms.length" class="space-y-1.5">
                    <a
                        v-for="(form, index) in topForms"
                        :key="form.id"
                        :href="form.url"
                        class="group flex items-center justify-between rounded-xl p-3.5 transition-all hover:bg-gray-50 dark:hover:bg-slate-700/50"
                    >
                        <div class="flex min-w-0 items-center gap-3.5">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl text-sm font-bold" :class="rankClasses(index)">
                                {{ index + 1 }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 transition-colors group-hover:text-kbb-700 dark:text-gray-100 dark:group-hover:text-kbb-400">{{ form.title }}</p>
                                <p class="mt-0.5 text-xs text-gray-400">
                                    <span class="font-medium text-gray-600 dark:text-gray-300">{{ form.submissions_count }}</span> respons
                                    <template v-if="isSuperAdmin">
                                        <span class="text-gray-300 dark:text-gray-600">&middot;</span> {{ form.owner || 'Unknown' }}
                                    </template>
                                </p>
                            </div>
                        </div>
                        <div class="ml-2 flex flex-shrink-0 items-center gap-2.5">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses(form.status)">
                                {{ form.status_label }}
                            </span>
                            <svg class="h-4 w-4 text-gray-300 transition-colors group-hover:text-gray-500 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>
                </div>

                <div v-else class="py-10 text-center">
                    <p class="text-sm text-gray-400">Belum ada respons masuk</p>
                    <a href="/forms/create" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-kbb-700 hover:text-kbb-800 dark:text-kbb-400 dark:hover:text-kbb-300">
                        Buat form dan bagikan
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Aktivitas Terbaru</h2>
                </div>

                <div v-if="latestSubmissions.length" class="space-y-1.5">
                    <div
                        v-for="submission in latestSubmissions"
                        :key="submission.id"
                        class="group flex items-start gap-3.5 rounded-xl p-3 transition-all hover:bg-gray-50 dark:hover:bg-slate-700/50"
                    >
                        <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-kbb-50 to-kbb-100 transition-transform group-hover:scale-110 dark:from-kbb-500/15 dark:to-kbb-500/5">
                            <svg class="h-4 w-4 text-kbb-600 dark:text-kbb-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Respons baru di
                                <a :href="submission.url" class="font-semibold text-kbb-700 hover:text-kbb-800 dark:text-kbb-400 dark:hover:text-kbb-300">
                                    {{ submission.form_title }}
                                </a>
                            </p>
                            <p class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-gray-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ submission.submitted_at }}
                                <template v-if="isSuperAdmin && submission.owner">
                                    <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                                    <span>{{ submission.owner }}</span>
                                </template>
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else class="py-10 text-center">
                    <p class="text-sm text-gray-400">Belum ada aktivitas terbaru</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 lg:col-span-3">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Forms Terbaru</h2>
                    <a href="/forms" class="inline-flex items-center gap-1 text-sm font-medium text-kbb-700 hover:text-kbb-800 dark:text-kbb-400 dark:hover:text-kbb-300">
                        Lihat Semua
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div v-if="recentForms.length" class="space-y-1.5">
                    <a
                        v-for="form in recentForms"
                        :key="form.id"
                        :href="form.url"
                        class="group flex items-center justify-between rounded-xl p-3.5 transition-all hover:bg-gray-50 dark:hover:bg-slate-700/50"
                    >
                        <div class="flex min-w-0 items-center gap-3.5">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-kbb-50 to-kbb-100 transition-all group-hover:scale-110 group-hover:shadow-md dark:from-kbb-500/15 dark:to-kbb-500/5">
                                <svg class="h-5 w-5 text-kbb-700 dark:text-kbb-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 transition-colors group-hover:text-kbb-700 dark:text-gray-100 dark:group-hover:text-kbb-400">{{ form.title }}</p>
                                <p class="mt-0.5 text-xs text-gray-400">
                                    <span class="font-medium text-gray-600 dark:text-gray-300">{{ form.submissions_count }}</span> respons
                                    <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                                    <span>{{ form.fields_count ?? 0 }}</span> field
                                </p>
                            </div>
                        </div>
                        <span class="flex-shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses(form.status)">
                            {{ form.status_label }}
                        </span>
                    </a>
                </div>

                <div v-else class="py-10 text-center">
                    <p class="text-sm text-gray-400">Belum ada form</p>
                    <a href="/forms/create" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-kbb-700 hover:text-kbb-800 dark:text-kbb-400 dark:hover:text-kbb-300">
                        Buat form pertama
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 lg:col-span-2">
                <h2 class="mb-5 text-lg font-semibold text-gray-900 dark:text-gray-100">Akses Cepat</h2>
                <div class="grid grid-cols-2 gap-3">
                    <a href="/forms" class="group flex flex-col items-center gap-3 rounded-2xl border border-kbb-100/50 bg-gradient-to-br from-kbb-50 to-kbb-100/50 p-5 transition-all hover:-translate-y-0.5 hover:border-kbb-200 hover:from-kbb-100 hover:to-kbb-200/50 hover:shadow-md dark:border-kbb-500/20 dark:from-kbb-500/10 dark:to-kbb-500/5 dark:hover:border-kbb-500/30 dark:hover:from-kbb-500/20 dark:hover:to-kbb-500/10">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm transition-transform group-hover:scale-110 dark:bg-slate-800">
                            <svg class="h-6 w-6 text-kbb-700 dark:text-kbb-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-kbb-800 dark:text-kbb-300">Semua Form</span>
                    </a>

                    <a href="/forms/create" class="group flex flex-col items-center gap-3 rounded-2xl border border-emerald-100/50 bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-5 transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:from-emerald-100 hover:to-emerald-200/50 hover:shadow-md dark:border-emerald-500/20 dark:from-emerald-500/10 dark:to-emerald-500/5 dark:hover:border-emerald-500/30 dark:hover:from-emerald-500/20 dark:hover:to-emerald-500/10">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm transition-transform group-hover:scale-110 dark:bg-slate-800">
                            <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Buat Form</span>
                    </a>

                    <template v-if="isSuperAdmin">
                        <a href="/users" class="group flex flex-col items-center gap-3 rounded-2xl border border-purple-100/50 bg-gradient-to-br from-purple-50 to-purple-100/50 p-5 transition-all hover:-translate-y-0.5 hover:border-purple-200 hover:from-purple-100 hover:to-purple-200/50 hover:shadow-md dark:border-purple-500/20 dark:from-purple-500/10 dark:to-purple-500/5 dark:hover:border-purple-500/30 dark:hover:from-purple-500/20 dark:hover:to-purple-500/10">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm transition-transform group-hover:scale-110 dark:bg-slate-800">
                                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-purple-800 dark:text-purple-300">Kelola User</span>
                        </a>

                        <a href="/admin" class="group flex flex-col items-center gap-3 rounded-2xl border border-amber-100/50 bg-gradient-to-br from-amber-50 to-amber-100/50 p-5 transition-all hover:-translate-y-0.5 hover:border-amber-200 hover:from-amber-100 hover:to-amber-200/50 hover:shadow-md dark:border-amber-500/20 dark:from-amber-500/10 dark:to-amber-500/5 dark:hover:border-amber-500/30 dark:hover:from-amber-500/20 dark:hover:to-amber-500/10">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm transition-transform group-hover:scale-110 dark:bg-slate-800">
                                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">Admin Panel</span>
                        </a>
                    </template>

                    <template v-else>
                        <a href="/change-password" class="group flex flex-col items-center gap-3 rounded-2xl border border-gray-100/50 bg-gradient-to-br from-gray-50 to-gray-100/50 p-5 transition-all hover:-translate-y-0.5 hover:border-gray-200 hover:from-gray-100 hover:to-gray-200/50 hover:shadow-md dark:border-slate-600/30 dark:from-slate-600/20 dark:to-slate-600/10 dark:hover:border-slate-500/40 dark:hover:from-slate-600/30 dark:hover:to-slate-600/15">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm transition-transform group-hover:scale-110 dark:bg-slate-800">
                                <svg class="h-6 w-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-300">Ubah Password</span>
                        </a>

                        <a href="/forms?status=published" class="group flex flex-col items-center gap-3 rounded-2xl border border-emerald-100/50 bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-5 transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:from-emerald-100 hover:to-emerald-200/50 hover:shadow-md dark:border-emerald-500/20 dark:from-emerald-500/10 dark:to-emerald-500/5 dark:hover:border-emerald-500/30 dark:hover:from-emerald-500/20 dark:hover:to-emerald-500/10">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm transition-transform group-hover:scale-110 dark:bg-slate-800">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Form Aktif</span>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
