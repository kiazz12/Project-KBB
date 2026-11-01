<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    user: { type: Object, default: null },
});

const isEdit = computed(() => props.user !== null);

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    role: props.user?.role ?? 'admin',
    nip: props.user?.nip ?? '',
    opd: props.user?.opd ?? '',
});

function submit() {
    if (isEdit.value) {
        form.put(`/admin/users/${props.user.id}`, {
            preserveScroll: true,
        });

        return;
    }

    form.post('/admin/users', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="isEdit ? 'Edit User' : 'Tambah User'" />

    <AdminLayout>
        <template #title>{{ isEdit ? 'Edit User' : 'Tambah User' }}</template>

        <div class="max-w-lg">
            <div class="mb-6 flex items-center gap-3">
                <Link
                    href="/admin/users"
                    class="text-gray-400 transition hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ isEdit ? 'Edit User' : 'Tambah User' }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ isEdit ? 'Perbarui data pengguna' : 'Buat akun baru' }}</p>
                </div>
            </div>

            <form
                class="space-y-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                @submit.prevent="submit"
            >
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                    >
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                    >
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        :required="!isEdit"
                        autocomplete="new-password"
                        :placeholder="isEdit ? 'Kosongkan jika tidak diganti' : ''"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                    >
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label for="role" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                    <select
                        id="role"
                        v-model="form.role"
                        required
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                    >
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                    </select>
                    <p v-if="form.errors.role" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.role }}</p>
                </div>

                <div>
                    <label for="nip" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">NIP</label>
                    <input
                        id="nip"
                        v-model="form.nip"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                    >
                    <p v-if="form.errors.nip" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.nip }}</p>
                </div>

                <div>
                    <label for="opd" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">OPD</label>
                    <input
                        id="opd"
                        v-model="form.opd"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                    >
                    <p v-if="form.errors.opd" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.opd }}</p>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="submit"
                        class="rounded-lg bg-kbb-700 px-6 py-2.5 font-medium text-white transition hover:bg-kbb-800 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Buat User') }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
