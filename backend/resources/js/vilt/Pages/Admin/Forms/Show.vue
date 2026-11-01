<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    form: { type: Object, required: true },
    submissions: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`Inspeksi: ${form.title} — Admin Panel`" />

    <AdminLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <Link href="/admin/forms" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <span class="truncate">Inspeksi Formulir</span>
            </div>
        </template>

        <!-- Form Info Card -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-800">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 pb-4 dark:border-slate-700">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-kbb-50 px-2.5 py-0.5 text-xs font-bold text-kbb-700 dark:bg-kbb-900/30 dark:text-kbb-300">
                            {{ form.status_label }}
                        </span>
                        <span class="text-xs text-gray-400">Slug: /form/{{ form.slug }}</span>
                    </div>
                    <h2 class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100">{{ form.title }}</h2>
                    <p v-if="form.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ form.description }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        :href="`/form/${form.slug}`"
                        target="_blank"
                        class="rounded-xl border border-gray-200 px-3.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:text-gray-300"
                    >
                        Buka Form Publik &rarr;
                    </a>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4 text-xs sm:grid-cols-4">
                <div>
                    <span class="text-gray-400">Pemilik / Pembuat:</span>
                    <p class="font-bold text-gray-900 dark:text-gray-100">{{ form.owner }}</p>
                </div>
                <div>
                    <span class="text-gray-400">Total Respon Masuk:</span>
                    <p class="font-bold text-kbb-700 dark:text-kbb-400">{{ form.submissions_count }}</p>
                </div>
                <div>
                    <span class="text-gray-400">Batas Maksimum:</span>
                    <p class="font-bold text-gray-900 dark:text-gray-100">{{ form.max_submissions ? `${form.max_submissions} kuota` : 'Tidak dibatasi' }}</p>
                </div>
                <div>
                    <span class="text-gray-400">Jadwal Penutupan:</span>
                    <p class="font-bold text-gray-900 dark:text-gray-100">{{ form.ends_at || 'Tidak ditentukan' }}</p>
                </div>
            </div>
        </div>

        <!-- Submissions Log Table -->
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-800">
            <div class="border-b border-gray-100 px-5 py-3 dark:border-slate-700">
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Log Respon yang Masuk</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 bg-gray-50/50 text-gray-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-gray-400">
                        <tr>
                            <th class="w-20 px-5 py-3 font-semibold">ID</th>
                            <th class="w-40 px-5 py-3 font-semibold">Waktu Pengisian</th>
                            <th class="px-5 py-3 font-semibold">Ringkasan Jawaban</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        <tr v-if="submissions.data.length === 0">
                            <td colspan="3" class="px-5 py-12 text-center text-gray-400">
                                Belum ada respon yang dikirimkan untuk form ini.
                            </td>
                        </tr>

                        <tr
                            v-for="sub in submissions.data"
                            :key="sub.id"
                            class="transition hover:bg-gray-50/50 dark:hover:bg-slate-700/30"
                        >
                            <td class="px-5 py-3 font-bold text-gray-900 dark:text-gray-100">
                                #{{ sub.id }}
                            </td>
                            <td class="px-5 py-3 text-gray-500">
                                {{ sub.created_at }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="(item, idx) in sub.data"
                                        :key="idx"
                                        class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-0.5 text-[11px] text-gray-700 dark:bg-slate-700 dark:text-gray-300"
                                    >
                                        <span class="font-semibold text-gray-500 dark:text-gray-400">{{ item.label }}:</span>
                                        <span>{{ item.value || '-' }}</span>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="submissions.links && submissions.links.length > 3"
                class="flex items-center justify-between border-t border-gray-100 px-5 py-3 dark:border-slate-700"
            >
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan {{ submissions.from ?? 0 }} - {{ submissions.to ?? 0 }} dari {{ submissions.total }} respon
                </p>
                <div class="flex items-center gap-1">
                    <Component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, i) in submissions.links"
                        :key="i"
                        :href="link.url"
                        class="rounded-lg px-2.5 py-1 text-xs font-medium transition"
                        :class="[
                            link.active
                                ? 'bg-kbb-700 text-white dark:bg-kbb-600'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-slate-700',
                            !link.url ? 'opacity-40 cursor-not-allowed' : '',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
