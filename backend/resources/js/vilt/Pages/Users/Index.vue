<script setup>
import { Head, Link } from '@inertiajs/vue3';
import RoleLayout from '../../Layouts/RoleLayout.vue';

defineProps({
    users: { type: Object, required: true },
});

function paginationLabel(label) {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›');
}
</script>

<template>
    <Head title="Users" />

    <RoleLayout>
        <template #title>Users</template>

        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] p-6 text-white shadow-2xl sm:p-8">
            <div class="relative">
                <div class="mb-2 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span class="text-xs font-medium uppercase tracking-wider text-kbb-200">Users</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight">Kelola Pengguna</h1>
                <p class="mt-1 text-sm text-kbb-200/80">Semua akun pengguna sistem</p>
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
                            <th class="px-5 py-3.5 font-semibold">Dibuat</th>
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
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ user.name }}</p>
                                        <p v-if="user.nip" class="text-xs text-gray-400">{{ user.nip }}</p>
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
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-kbb-700 transition hover:bg-kbb-50 hover:text-kbb-800 dark:text-kbb-400 dark:hover:bg-slate-700 dark:hover:text-kbb-300"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Detail
                                </Link>
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
                        <span v-if="!link.url" class="rounded-lg px-3 py-1.5 text-xs text-gray-300 dark:text-gray-600">{{ paginationLabel(link.label) }}</span>
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                            :class="link.active ? 'bg-kbb-700 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 dark:bg-slate-800 dark:text-gray-300 dark:hover:bg-slate-700'"
                            {{ paginationLabel(link.label) }}
                        ></Link>
                    </template>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
