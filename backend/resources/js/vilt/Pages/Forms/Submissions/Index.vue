<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import RoleLayout from '../../../Layouts/RoleLayout.vue';

const props = defineProps({
    form: { type: Object, required: true },
    submissions: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '', from: '', to: '' }) },
});

const filter = reactive({
    search: props.filters.search ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
});

const selectedIds = ref([]);
const selectAll = ref(false);

function toggleSelectAll() {
    if (selectAll.value) {
        selectedIds.value = props.submissions.data.map((s) => s.id);
    } else {
        selectedIds.value = [];
    }
}

function applyFilters() {
    router.get(`/forms/${props.form.id}/submissions`, {
        search: filter.search || undefined,
        from: filter.from || undefined,
        to: filter.to || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}

function resetFilters() {
    filter.search = '';
    filter.from = '';
    filter.to = '';
    router.get(`/forms/${props.form.id}/submissions`, {}, { preserveState: true, replace: true });
}

function deleteSingle(submission) {
    if (!window.confirm('Hapus respon ini secara permanen?')) {
        return;
    }

    router.post(`/forms/${props.form.id}/submissions/${submission.id}/delete`, {}, {
        preserveScroll: true,
    });
}

function deleteBulk() {
    if (selectedIds.value.length === 0) {
        alert('Pilih minimal satu submission.');
        return;
    }

    if (!window.confirm(`Hapus ${selectedIds.value.length} submission yang dipilih?`)) {
        return;
    }

    router.post(`/forms/${props.form.id}/submissions/bulk-delete`, {
        ids: selectedIds.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            selectAll.value = false;
        },
    });
}

// Show first 4 fields as dynamic columns in table
const previewFields = computed(() => (props.form.fields || []).slice(0, 4));

function paginationLabel(label) {
    return String(label ?? '').replace('&laquo;', '‹').replace('&raquo;', '›');
}
</script>

<template>
    <Head :title="`Respon: ${form.title}`" />

    <RoleLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <Link :href="`/forms/${form.id}`" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <span class="truncate">Daftar Respon Masuk</span>
            </div>
        </template>

        <!-- Header Actions: Exports & Info -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ form.title }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Total {{ submissions.total }} submission tercatat
                </p>
            </div>

            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <a
                    :href="form.exports.csv"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-300 dark:hover:bg-slate-700"
                >
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    CSV
                </a>
                <a
                    :href="form.exports.xlsx"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-300 dark:hover:bg-slate-700"
                >
                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Excel (XLSX)
                </a>
                <a
                    :href="form.exports.pdf"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-300 dark:hover:bg-slate-700"
                >
                    <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    PDF
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-800">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <input
                        v-model="filter.search"
                        type="text"
                        placeholder="Cari respon (nama, nilai jawaban, dll)..."
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2 text-xs text-gray-900 focus:border-kbb-500 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        @keydown.enter="applyFilters"
                    />
                </div>
                <div>
                    <input
                        v-model="filter.from"
                        type="date"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        title="Dari Tanggal"
                    />
                </div>
                <div>
                    <input
                        v-model="filter.to"
                        type="date"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        title="Sampai Tanggal"
                    />
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-xl bg-kbb-700 px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-kbb-800 dark:bg-kbb-600"
                        @click="applyFilters"
                    >
                        Terapkan Filter
                    </button>
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-700 dark:text-gray-300"
                        @click="resetFilters"
                    >
                        Reset
                    </button>
                </div>

                <div v-if="selectedIds.length > 0">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs transition hover:bg-red-700"
                        @click="deleteBulk"
                    >
                        Hapus {{ selectedIds.length }} Terpilih
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Submissions -->
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 bg-gray-50/50 text-gray-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-gray-400">
                        <tr>
                            <th class="w-10 px-4 py-3 text-center">
                                <input
                                    v-model="selectAll"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-kbb-700 focus:ring-kbb-500"
                                    @change="toggleSelectAll"
                                />
                            </th>
                            <th class="px-4 py-3 font-semibold">ID / Waktu Submit</th>
                            <th
                                v-for="field in previewFields"
                                :key="field.id"
                                class="px-4 py-3 font-semibold truncate max-w-[150px]"
                            >
                                {{ field.label }}
                            </th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        <tr v-if="submissions.data.length === 0">
                            <td :colspan="previewFields.length + 3" class="px-4 py-12 text-center text-gray-400">
                                Tidak ada data pengisian yang sesuai dengan filter.
                            </td>
                        </tr>

                        <tr
                            v-for="sub in submissions.data"
                            :key="sub.id"
                            class="transition hover:bg-gray-50/50 dark:hover:bg-slate-700/30"
                        >
                            <td class="px-4 py-3 text-center">
                                <input
                                    v-model="selectedIds"
                                    type="checkbox"
                                    :value="sub.id"
                                    class="h-4 w-4 rounded border-gray-300 text-kbb-700 focus:ring-kbb-500"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-gray-900 dark:text-gray-100">#{{ sub.id }}</p>
                                <p class="text-[10px] text-gray-400">{{ sub.submitted_at }}</p>
                            </td>
                            <td
                                v-for="field in previewFields"
                                :key="field.id"
                                class="px-4 py-3 text-gray-700 dark:text-gray-300 truncate max-w-[150px]"
                            >
                                {{ sub.data[field.id] || '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <Link
                                        :href="sub.url"
                                        class="rounded-lg bg-gray-100 px-2.5 py-1 font-semibold text-gray-700 hover:bg-gray-200 dark:bg-slate-700 dark:text-gray-300 dark:hover:bg-slate-600"
                                    >
                                        Detail
                                    </Link>
                                    <button
                                        type="button"
                                        class="rounded-lg p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30"
                                        title="Hapus"
                                        @click="deleteSingle(sub)"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="submissions.links && submissions.links.length > 3"
                class="flex items-center justify-between border-t border-gray-100 px-4 py-3 dark:border-slate-700"
            >
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan {{ submissions.from ?? 0 }} - {{ submissions.to ?? 0 }} dari {{ submissions.total }} data
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
                    >{{ paginationLabel(link.label) }}</Component>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
