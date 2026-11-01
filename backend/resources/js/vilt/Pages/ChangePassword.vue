<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

defineProps({
    isSuperAdmin: { type: Boolean, default: false },
});

const form = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

function submit() {
    form.post('/change-password', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Ubah Password" />

    <AppLayout>
        <template #title>Ubah Password</template>

        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] p-6 text-white shadow-2xl sm:p-8">
            <div class="relative">
                <div class="mb-2 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span class="text-xs font-medium uppercase tracking-wider text-kbb-200">Akun</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight">Ubah Password</h1>
                <p class="mt-1 text-sm text-kbb-200/80">Perbarui password akun Anda</p>
            </div>
        </div>

        <div class="max-w-lg">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <form class="space-y-4" @submit.prevent="submit">
                    <div v-if="!isSuperAdmin">
                        <label for="current_password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password Saat Ini
                        </label>
                        <input
                            id="current_password"
                            v-model="form.current_password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                        >
                        <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.current_password }}
                        </p>
                    </div>

                    <div>
                        <label for="new_password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password Baru
                        </label>
                        <input
                            id="new_password"
                            v-model="form.new_password"
                            type="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                        >
                        <p v-if="form.errors.new_password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.new_password }}
                        </p>
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Konfirmasi Password Baru
                        </label>
                        <input
                            id="new_password_confirmation"
                            v-model="form.new_password_confirmation"
                            type="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                        >
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            class="rounded-xl bg-kbb-700 px-6 py-2.5 font-medium text-white transition hover:bg-kbb-800 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
