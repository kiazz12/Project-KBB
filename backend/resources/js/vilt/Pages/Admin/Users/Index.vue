<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    users: { type: Object, required: true },
});

const page = usePage();
const currentUserId = page.props.auth?.user?.id ?? null;

function destroy(user) {
    if (!window.confirm(`Hapus user "${user.name}"?`)) {
        return;
    }

    router.delete(`/admin/users/${user.id}`, { preserveScroll: true });
}

function paginationLabel(label) {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›');
}

function initial(name) {
    return name ? name.charAt(0).toUpperCase() : '?';
}

function formatNip(nip) {
    return nip ? `NIP. ${nip}` : null;
}
</script>

<template>
    <Head title="Users" />

    <AdminLayout>
        <template #title>Users</template>

        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] p-6 text-white shadow-2xl sm:p-8">
            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-medium uppercase tracking-wider text-kbb-200">Users</span>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Users</h1>
                    <p class="mt-1 text-sm text-kbb-200/80">Kelola akun pengguna</p>
                </div>
                <a
                    href="/admin/users/create"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/15 px-5 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition-all hover:scale-[1.02] hover:bg-white/25 hover:shadow-lg active:scale-[0.98]"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah User
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/80 text-left text-gray-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-gray-400">
                            <th class="px-5 py-3.5 font-semibold">Nama</th>
                            <th class="px-5 py-3.5 font-semibold">Email</th>
                            <th class="px-5 py-3.5 font-semibold">Role</th>
                            <th class="px-5 py-3.5 font-semibold">OPD</th>
                            <th class="px-5 py-3.5 font-semibold">Tgl Dibuat</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="group transition-all hover:bg-gray-50/80 dark:hover:bg-slate-700/40"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br transition-transform group-hover:scale-110"
                                        :class="user.is_super_admin
                                            ? 'from-amber-50 to-amber-100 dark:from-amber-500/15 dark:to-amber-500/5'
                                            : 'from-kbb-50 to-kbb-100 dark:from-kbb-500/15 dark:to-kbb-500/5'"
                                    >
                                        <span class="text-sm font-bold" :class="user.is_super_admin ? 'text-amber-700 dark:text-amber-400' : 'text-kbb-700 dark:text-kbb-400'">
                                            {{ initial(user.name) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ user.name }}</p>
                                        <p v-if="user.nip" class="text-xs text-gray-400">{{ formatNip(user.nip) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ user.email }}</td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="user.is_super_admin
                                        ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400'
                                        : 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400'"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="user.is_super_admin ? 'bg-amber-500' : 'bg-blue-500'"></span>
                                    {{ user.is_super_admin ? 'Super Admin' : 'Admin' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ user.opd || '-' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-400">{{ user.created_at }}</td>
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="`/users/${user.id}`"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-slate-700 dark:hover:text-gray-100"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Detail
                                </Link>
                                <Link
                                    :href="`/admin/users/${user.id}/edit`"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-kbb-700 transition hover:bg-kbb-50 hover:text-kbb-800 dark:text-kbb-400 dark:hover:bg-slate-700 dark:hover:text-kbb-300"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </Link>
                                <button
                                    v-if="user.id !== currentUserId"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-red-500 transition hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300"
                                    @click="destroy(user)"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus
                                </button>
                            </td>
                        </tr>

                        <tr v-if="!users.data.length">
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 dark:bg-slate-700">
                                    <svg class="h-7 w-7 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-400">Tidak ada user.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="users.last_page > 1" class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/50 px-5 py-4 dark:border-slate-700 dark:bg-slate-900/40">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan {{ users.from }}–{{ users.to }} dari {{ users.total }} user
                </p>
                <div class="flex flex-wrap items-center gap-1">
                    <template v-for="(link, index) in users.links" :key="index">
                        <span
                            v-if="!link.url"
                            class="rounded-lg px-3 py-1.5 text-xs text-gray-300 dark:text-gray-600"
                            v-html="paginationLabel(link.label)"
                        ></span>
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                            :class="link.active
                                ? 'bg-kbb-700 text-white'
                                : 'bg-white text-gray-600 hover:bg-gray-100 dark:bg-slate-800 dark:text-gray-300 dark:hover:bg-slate-700'"
                            v-html="paginationLabel(link.label)"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
