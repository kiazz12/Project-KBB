<script setup>
import { Head, Link } from '@inertiajs/vue3';
import RoleLayout from '../../../Layouts/RoleLayout.vue';

const props = defineProps({
    form: { type: Object, required: true },
    submission: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`Detail Respon #${submission.id} — ${form.title}`" />

    <RoleLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <Link :href="`/forms/${form.id}/submissions`" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <span class="truncate">Detail Respon #{{ submission.id }}</span>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Header Meta Card -->
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-800">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 pb-4 dark:border-slate-700">
                    <div>
                        <span class="rounded bg-kbb-50 px-2.5 py-1 text-xs font-bold text-kbb-700 dark:bg-kbb-900/30 dark:text-kbb-300">
                            ID: #{{ submission.id }}
                        </span>
                        <h2 class="mt-2 text-lg font-bold text-gray-900 dark:text-gray-100">{{ form.title }}</h2>
                    </div>

                    <Link
                        :href="`/forms/${form.id}/submissions`"
                        class="rounded-xl border border-gray-200 px-3.5 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-700 dark:text-gray-300"
                    >
                        &larr; Kembali ke Daftar
                    </Link>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 text-xs sm:grid-cols-3">
                    <div>
                        <span class="text-gray-400">Waktu Pengisian:</span>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ submission.submitted_at }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400">UUID Transaksi:</span>
                        <p class="font-mono text-gray-800 dark:text-gray-200 truncate" :title="submission.uuid">{{ submission.uuid }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400">Alamat IP:</span>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ submission.ip_address || 'Tidak dicatat' }}</p>
                    </div>
                </div>
            </div>

            <!-- Answers List Card -->
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-800">
                <h3 class="border-b border-gray-100 pb-3 text-sm font-bold text-gray-900 dark:text-gray-100 dark:border-slate-700">
                    Jawaban yang Diisi Responden
                </h3>

                <div class="mt-4 divide-y divide-gray-100 dark:divide-slate-700">
                    <div
                        v-for="ans in submission.answers"
                        :key="ans.field_id"
                        class="py-4 first:pt-0 last:pb-0"
                    >
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ ans.label }}</p>

                        <!-- If File upload -->
                        <div v-if="ans.is_file && ans.file_url" class="mt-2">
                            <a
                                :href="ans.file_url"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-xs font-semibold text-kbb-700 hover:bg-gray-100 dark:border-slate-700 dark:bg-slate-900 dark:text-kbb-400"
                            >
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Unduh Lampiran Berkas
                            </a>
                        </div>

                        <!-- Standard text / json answer -->
                        <div v-else class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                            {{ ans.value || '(Tidak diisi)' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
