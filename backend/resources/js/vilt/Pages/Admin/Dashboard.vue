<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    recentForms: { type: Array, default: () => [] },
    usersByRole: { type: Array, default: () => [] },
    activity: { type: Object, required: true },
    today: { type: String, default: '' },
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
    if (range.value === value) {
        return;
    }

    range.value = value;
    chartLoading.value = true;

    try {
        const response = await fetch(`/admin/activity?range=${value}`, {
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

const roleRows = computed(() => {
    const total = props.stats.total_users || 0;

    return props.usersByRole.map((item) => ({
        ...item,
        width: total > 0 ? Math.round((item.total / total) * 100) : 0,
        dot: item.role === 'super_admin' ? 'bg-amber-500' : 'bg-blue-500',
        bar: item.role === 'super_admin'
            ? 'bg-gradient-to-r from-amber-400 to-amber-300'
            : 'bg-gradient-to-r from-blue-500 to-blue-400',
    }));
});

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
</script>

<template>
    <Head title="Dashboard Admin" />

    <AdminLayout>
        <template #title>Dashboard Admin</template>

        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] p-6 text-white shadow-2xl sm:p-8">
            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-medium uppercase tracking-wider text-kbb-200">Dashboard</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Dashboard Admin</h1>
                    <p class="mt-1 text-sm text-kbb-200/80">Overview sistem Formulir Online KBB</p>
                </div>
                <div class="mt-1 text-sm text-white/60">{{ today }}</div>
            </div>
        </div>

        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Users</p>
                        <p class="mt-1.5 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_users }}</p>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 transition-all group-hover:scale-110 dark:from-blue-500/15 dark:to-blue-500/5">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Forms</p>
                        <p class="mt-1.5 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_forms }}</p>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 transition-all group-hover:scale-110 dark:from-emerald-500/15 dark:to-emerald-500/5">
                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Published</p>
                        <p class="mt-1.5 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_published }}</p>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 transition-all group-hover:scale-110 dark:from-amber-500/15 dark:to-amber-500/5">
                        <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Submissions</p>
                        <p class="mt-1.5 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_submissions }}</p>
                        <p class="mt-1.5 text-xs text-gray-400">{{ stats.submissions_today }} hari ini</p>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 transition-all group-hover:scale-110 dark:from-purple-500/15 dark:to-purple-500/5">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-8 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
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

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <h2 class="mb-5 text-lg font-semibold text-gray-900 dark:text-gray-100">Forms Terbaru</h2>

                <div v-if="recentForms.length" class="space-y-1.5">
                    <a
                        v-for="form in recentForms"
                        :key="form.id"
                        :href="form.url"
                        class="group flex items-center justify-between rounded-xl p-3.5 transition-all hover:bg-gray-50 dark:hover:bg-slate-700/50"
                    >
                        <div class="flex min-w-0 items-center gap-3.5">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-kbb-50 to-kbb-100 transition-transform group-hover:scale-110 dark:from-kbb-500/15 dark:to-kbb-500/5">
                                <svg class="h-5 w-5 text-kbb-700 dark:text-kbb-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 transition-colors group-hover:text-kbb-700 dark:text-gray-100 dark:group-hover:text-kbb-400">{{ form.title }}</p>
                                <p class="mt-0.5 text-xs text-gray-400">
                                    <span class="font-medium text-gray-600 dark:text-gray-300">{{ form.submissions_count }}</span> pengiriman
                                    <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                                    {{ form.owner }}
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses(form.status)">
                            <span class="h-1.5 w-1.5 rounded-full" :class="statusDot(form.status)"></span>
                            {{ form.status_label }}
                        </span>
                    </a>
                </div>

                <p v-else class="py-10 text-center text-sm text-gray-400">Belum ada form.</p>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <h2 class="mb-5 text-lg font-semibold text-gray-900 dark:text-gray-100">Users by Role</h2>

                <div v-if="roleRows.length" class="space-y-5">
                    <div v-for="row in roleRows" :key="row.role">
                        <div class="mb-1.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full" :class="row.dot"></span>
                                <span class="text-sm font-medium capitalize text-gray-700 dark:text-gray-300">{{ row.label }}</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ row.total }}</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100 shadow-inner dark:bg-slate-700">
                            <div class="h-full rounded-full transition-all duration-500" :class="row.bar" :style="{ width: row.width + '%' }"></div>
                        </div>
                    </div>
                </div>

                <p v-else class="py-10 text-center text-sm text-gray-400">Tidak ada data.</p>
            </div>
        </div>
    </AdminLayout>
</template>
