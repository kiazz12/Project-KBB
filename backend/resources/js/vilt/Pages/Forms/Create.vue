<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const form = useForm({
    title: '',
    description: '',
});

function submit() {
    form.post('/forms');
}
</script>

<template>
    <Head title="Buat Form Baru" />

    <AppLayout>
        <template #title>Buat Formulir Baru</template>

        <div class="mx-auto max-w-2xl">
            <div class="mb-6">
                <Link
                    href="/forms"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-kbb-700 dark:text-gray-400 dark:hover:text-kbb-400"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Daftar Formulir
                </Link>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-800 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Informasi Dasar Formulir</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Mulai dengan memberi nama dan deskripsi formulir Anda. Anda dapat menambahkan pertanyaan dan pengaturan setelah ini.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Judul Formulir <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="Contoh: Pendaftaran Bantuan UMKM 2026"
                            class="mt-1.5 block w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 transition focus:border-kbb-500 focus:outline-none focus:ring-2 focus:ring-kbb-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100 dark:focus:border-kbb-400"
                            :class="{ 'border-red-500': form.errors.title }"
                        />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Deskripsi / Petunjuk Pengisian (Opsional)
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            placeholder="Berikan petunjuk singkat bagi responden tentang tujuan formulir ini..."
                            class="mt-1.5 block w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 transition focus:border-kbb-500 focus:outline-none focus:ring-2 focus:ring-kbb-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100 dark:focus:border-kbb-400"
                            :class="{ 'border-red-500': form.errors.description }"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <Link
                            href="/forms"
                            class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-300 dark:hover:bg-slate-700"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-kbb-700 px-6 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-kbb-800 disabled:opacity-50 dark:bg-kbb-600 dark:hover:bg-kbb-500"
                        >
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            Lanjut ke Form Builder &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
