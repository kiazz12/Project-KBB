<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    form: { type: Object, required: true },
});

const isPublished = computed(() => props.form.status === 'published');

const statusColor = computed(() => {
    if (props.form.status === 'published') {
        return 'text-emerald-300';
    }

    if (props.form.status === 'closed') {
        return 'text-red-300';
    }

    return 'text-kbb-300';
});

function duplicate() {
    if (!window.confirm('Gandakan form ini sebagai draft?')) {
        return;
    }

    router.post(`/forms/${props.form.id}/duplicate`);
}
</script>

<template>
    <Head :title="form.title" />

    <AppLayout>
        <template #title>{{ form.title }}</template>

        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] p-6 text-white shadow-2xl sm:p-8">
            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="mb-2 flex items-center gap-2">
                        <Link href="/forms" class="text-kbb-200 transition hover:text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <span class="text-xs font-medium uppercase tracking-wider text-kbb-200">Detail Form</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ form.title }}</h1>
                    <p class="mt-1 text-sm text-kbb-200/80">
                        <span :class="statusColor">{{ form.status_label }}</span>
                        &middot; {{ form.fields_count }} fields &middot; {{ form.submissions_count }} responses
                        <template v-if="form.sections_count > 0">&middot; {{ form.sections_count }} sections</template>
                    </p>
                    <p v-if="form.description" class="mt-2 max-w-2xl text-sm text-kbb-200/70">{{ form.description }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        v-if="isPublished"
                        :href="form.public_url"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/15 px-5 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition-all hover:scale-[1.02] hover:bg-white/25 hover:shadow-lg active:scale-[0.98]"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Public Link
                    </a>
                    <span v-if="form.trashed" class="text-xs text-red-300">(Deleted)</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <div class="space-y-6 lg:col-span-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Fields</h2>
                        <span class="text-sm text-gray-400">{{ form.fields.length }} field</span>
                    </div>

                    <div v-if="form.fields.length" class="space-y-2">
                        <div
                            v-for="field in form.fields"
                            :key="field.id"
                            class="flex items-center gap-3 rounded-lg bg-gray-50 p-3 dark:bg-slate-900/60"
                        >
                            <span class="w-16 font-mono text-xs text-kbb-600 dark:text-kbb-400">{{ field.type }}</span>
                            <span class="flex-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ field.label }}</span>
                            <span v-if="field.required" class="text-xs text-red-500">*wajib</span>
                            <span class="text-xs text-gray-400">Urutan {{ field.order }}</span>
                        </div>
                    </div>

                    <p v-else class="py-6 text-center text-sm text-gray-400">
                        Belum ada field.
                        <a :href="`/forms/${form.id}/edit`" class="text-kbb-700 hover:underline dark:text-kbb-400">Tambahkan field</a>
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Aksi</h3>
                    <div class="space-y-2">
                        <a :href="`/forms/${form.id}/edit`" class="block w-full rounded-lg bg-kbb-700 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-kbb-800">Edit Form</a>
                        <button type="button" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-200 dark:hover:bg-slate-700" @click="duplicate">Gandakan Form</button>
                        <a :href="`/forms/${form.id}/submissions`" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-200 dark:hover:bg-slate-700">Lihat Data</a>
                        <a :href="`/forms/${form.id}/analytics`" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-200 dark:hover:bg-slate-700">Analytics</a>

                        <hr class="border-gray-200 dark:border-slate-700">

                        <a :href="form.exports.csv" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-200 dark:hover:bg-slate-700">Download CSV</a>
                        <a :href="form.exports.xlsx" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-200 dark:hover:bg-slate-700">Download Excel (XLSX)</a>
                        <a :href="form.exports.pdf" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-200 dark:hover:bg-slate-700">Download PDF</a>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Info</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Slug</dt>
                            <dd class="break-all text-right text-xs text-gray-900 dark:text-gray-100">/form/{{ form.slug }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Batas</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ form.max_submissions || 'Tak terbatas' }}</dd>
                        </div>
                        <div v-if="form.ends_at" class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Berakhir</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ form.ends_at }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
