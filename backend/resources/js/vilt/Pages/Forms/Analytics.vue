<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import RoleLayout from '../../Layouts/RoleLayout.vue';

const props = defineProps({
    form: { type: Object, required: true },
    totalSubmissions: { type: Number, default: 0 },
    submissionsByDate: { type: Array, default: () => [] },
    maxByDate: { type: Number, default: 0 },
    fieldAnalytics: { type: Array, default: () => [] },
});

const barHeights = computed(() => {
    const max = props.maxByDate || 1;
    return props.submissionsByDate.map((item) => {
        if (!item.count) return '4px';
        return `${Math.max(6, Math.round((item.count / max) * 100))}%`;
    });
});
</script>

<template>
    <Head :title="`Analisis: ${form.title}`" />

    <RoleLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <Link :href="`/forms/${form.id}`" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <span class="truncate">Analitik Formulir</span>
            </div>
        </template>

        <!-- Summary Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-800">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Pengisian (Submissions)</p>
                <p class="mt-2 text-3xl font-extrabold text-kbb-700 dark:text-kbb-400">{{ totalSubmissions }}</p>
                <p class="mt-1 text-xs text-gray-400">Dari seluruh responden</p>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-800">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Jumlah Pertanyaan</p>
                <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-gray-100">{{ form.fields_count }}</p>
                <p class="mt-1 text-xs text-gray-400">Field aktif dalam form</p>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-800">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Status Formulir</p>
                <p class="mt-2 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ form.status_label }}</p>
                <p class="mt-1 text-xs text-gray-400">
                    <Link :href="`/forms/${form.id}/submissions`" class="text-kbb-700 underline dark:text-kbb-400">
                        Lihat Data Masuk &rarr;
                    </Link>
                </p>
            </div>
        </div>

        <!-- Submission Trend Chart -->
        <div class="mb-8 rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-800">
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Tren Respon Berdasarkan Tanggal</h3>
            <p class="text-xs text-gray-400">Grafik jumlah pengisian per hari</p>

            <div v-if="submissionsByDate.length === 0" class="py-12 text-center text-sm text-gray-400">
                Belum ada data pengisian untuk ditampilkan dalam grafik.
            </div>

            <div v-else class="mt-6">
                <div class="flex h-56 items-end gap-2 border-b border-gray-100 pb-2 dark:border-slate-700">
                    <div
                        v-for="(item, i) in submissionsByDate"
                        :key="item.date"
                        class="group relative flex flex-1 flex-col items-center justify-end h-full"
                    >
                        <div
                            class="w-full rounded-t-lg bg-kbb-600 transition-all duration-300 group-hover:bg-kbb-700 dark:bg-kbb-500 dark:group-hover:bg-kbb-400"
                            :style="{ height: barHeights[i] }"
                        ></div>
                        <!-- Tooltip -->
                        <div class="absolute -top-8 hidden whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[10px] text-white shadow-md group-hover:block dark:bg-slate-700">
                            {{ item.date }}: {{ item.count }} respon
                        </div>
                    </div>
                </div>
                <div class="mt-2 flex justify-between gap-1 overflow-x-auto text-[10px] text-gray-400">
                    <span v-for="item in submissionsByDate" :key="item.date" class="flex-1 text-center truncate">
                        {{ item.date }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Field Distribution Breakdown -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-800">
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Distribusi Jawaban Responden per Field</h3>
            <p class="text-xs text-gray-400">Rangkuman frekuensi jawaban pada pertanyaan pilihan</p>

            <div v-if="fieldAnalytics.length === 0" class="py-8 text-center text-sm text-gray-400">
                Belum ada data jawaban per field yang tercatat.
            </div>

            <div v-else class="mt-6 space-y-6">
                <div
                    v-for="fa in fieldAnalytics"
                    :key="fa.field_id"
                    class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-slate-700 dark:bg-slate-900/50"
                >
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ fa.field_label }}</h4>
                        <span class="rounded bg-gray-200 px-2 py-0.5 text-[10px] text-gray-600 dark:bg-slate-700 dark:text-gray-300">
                            {{ fa.field_type }}
                        </span>
                    </div>

                    <div v-if="Object.keys(fa.counts).length === 0" class="mt-3 text-xs text-gray-400">
                        Belum ada respon untuk pertanyaan ini.
                    </div>

                    <div v-else class="mt-3 space-y-2">
                        <div
                            v-for="(count, val) in fa.counts"
                            :key="val"
                            class="flex items-center justify-between gap-4 text-xs"
                        >
                            <span class="truncate text-gray-700 dark:text-gray-300">{{ val || '(Kosong)' }}</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ count }}x</span>
                                <span class="text-gray-400">({{ totalSubmissions > 0 ? Math.round((count / totalSubmissions) * 100) : 0 }}%)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
