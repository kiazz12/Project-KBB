<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    forms: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '', status: '' }) },
});

const filter = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
});

function applyFilters() {
    router.get('/admin/forms', {
        search: filter.search || undefined,
        status: filter.status || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}

function resetFilters() {
    filter.search = '';
    filter.status = '';
    router.get('/admin/forms', {}, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Manajemen Formulir — Panel Super Admin" />

    <AdminLayout>
        <template #title>Manajemen Seluruh Formulir</template>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-800">
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <input
                    v-model="filter.search"
                    type="text"
                    placeholder="Cari judul formulir atau pemilik (OPD)..."
                    class="w-full max-w-sm rounded-xl border border-gray-200 px-3.5 py-2 text-xs text-gray-900 focus:border-kbb-500 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                    @keydown.enter="applyFilters"
                />

                <select
                    v-model="filter.status"
                    class="rounded-xl border border-gray-200 px-3 py-2 text-xs text-gray-700 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-200"
                    @change="applyFilters"
                >
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Diterbitkan</option>
                    <option value="closed">Ditutup</option>
                </select>

                <button
                    type="button"
                    class="rounded-xl bg-kbb-700 px-4 py-2 text-xs font-semibold text-white transition hover:bg-kbb-800 dark:bg-kbb-600"
                    @click="applyFilters"
                >
                    Cari
                </button>
                <button
                    type="button"
                    class="rounded-xl border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-700 dark:text-gray-300"
                    @click="resetFilters"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 bg-gray-50/50 text-gray-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Judul Formulir</th>
                            <th class="px-5 py-3 font-semibold">Pemilik / OPD</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-center font-semibold">Jumlah Respon</th>
                            <th class="px-5 py-3 font-semibold">Dibuat Pada</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        <tr v-if="forms.data.length === 0">
                            <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                Tidak ada formulir yang ditemukan.
                            </td>
                        </tr>

                        <tr
                            v-for="form in forms.data"
                            :key="form.id"
                            class="transition hover:bg-gray-50/50 dark:hover:bg-slate-700/30"
                        >
                            <td class="px-5 py-3">
                                <p class="font-bold text-gray-900 dark:text-gray-100">{{ form.title }}</p>
                                <p v-if="form.trashed" class="text-[10px] text-red-500 font-semibold">(Form Terhapus)</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">
                                {{ form.owner }}
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                    :class="form.status === 'published'
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                        : (form.status === 'closed'
                                            ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                            : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300')"
                                >
                                    {{ form.status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center font-bold text-gray-900 dark:text-gray-100">
                                {{ form.submissions_count }}
                            </td>
                            <td class="px-5 py-3 text-gray-400">
                                {{ form.created_at }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Link
                                    :href="form.url"
                                    class="rounded-lg bg-kbb-50 px-3 py-1 font-semibold text-kbb-700 hover:bg-kbb-100 dark:bg-kbb-900/30 dark:text-kbb-300 dark:hover:bg-kbb-900/50"
                                >
                                    Inspeksi &rarr;
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="forms.links && forms.links.length > 3"
                class="flex items-center justify-between border-t border-gray-100 px-5 py-3 dark:border-slate-700"
            >
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan {{ forms.from ?? 0 }} - {{ forms.to ?? 0 }} dari {{ forms.total }} formulir
                </p>
                <div class="flex items-center gap-1">
                    <Component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, i) in forms.links"
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
