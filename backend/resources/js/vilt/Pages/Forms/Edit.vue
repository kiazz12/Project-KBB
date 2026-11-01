<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    form: { type: Object, required: true },
});

const currentTab = ref('fields'); // 'fields', 'sections', 'settings'
const saveStatus = ref('saved'); // 'saved', 'saving', 'error'
const saveMessage = ref('Semua perubahan tersimpan');
let autoSaveTimer = null;

// Settings state
const settings = ref({
    title: props.form.title,
    description: props.form.description ?? '',
    starts_at: props.form.starts_at ?? '',
    ends_at: props.form.ends_at ?? '',
    max_submissions: props.form.max_submissions ?? 0,
    require_auth: props.form.require_auth ?? false,
    collect_ip: props.form.collect_ip ?? true,
    show_kbb_logo: props.form.show_kbb_logo ?? true,
    limit_one_response: props.form.limit_one_response ?? false,
    confirmation_type: props.form.confirmation_type ?? 'message',
    confirmation_message: props.form.confirmation_message ?? 'Terima kasih, jawaban Anda telah dicatat.',
    redirect_url: props.form.redirect_url ?? '',
});

// Field modal state
const showFieldModal = ref(false);
const editingFieldId = ref(null);
const fieldForm = ref({
    type: 'text',
    label: '',
    placeholder: '',
    help_text: '',
    required: false,
    options_text: '',
    section_id: '',
    formula_ref: '',
    formula_op: 'multiply',
    formula_val: '',
});

// Section modal state
const showSectionModal = ref(false);
const editingSectionId = ref(null);
const sectionForm = ref({
    title: '',
    description: '',
});

// Live preview modal state
const showLivePreview = ref(false);
const showPublishModal = ref(false);
const copied = ref(false);

const fieldTypes = [
    { value: 'text', label: 'Teks Pendek', icon: 'M4 6h16M4 12h10M4 18h7' },
    { value: 'textarea', label: 'Teks Panjang / Paragraf', icon: 'M4 6h16M4 10h16M4 14h16M4 18h10' },
    { value: 'number', label: 'Angka / Numerik', icon: 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14' },
    { value: 'email', label: 'Alamat Email', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { value: 'date', label: 'Tanggal', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { value: 'time', label: 'Waktu / Jam', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { value: 'select', label: 'Pilihan Dropdown', icon: 'M19 9l-7 7-7-7' },
    { value: 'radio', label: 'Pilihan Ganda (Radio)', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { value: 'checkbox', label: 'Kotak Centang (Checkbox)', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { value: 'file', label: 'Unggah Berkas / Dokumen', icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12' },
    { value: 'heading', label: 'Judul Pemisah (Heading)', icon: 'M7 20V4m10 16V4M4 12h16' },
    { value: 'paragraph', label: 'Teks Informasi / Panduan', icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { value: 'signature', label: 'Tanda Tangan Digital', icon: 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z' },
    { value: 'computed', label: 'Kalkulasi Otomatis (Formula)', icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
];

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

// Autosave settings with debounce
function triggerAutoSave() {
    saveStatus.value = 'saving';
    saveMessage.value = 'Menyimpan perubahan…';
    clearTimeout(autoSaveTimer);

    autoSaveTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/forms/${props.form.id}/settings`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(settings.value),
            });

            if (res.ok) {
                saveStatus.value = 'saved';
                saveMessage.value = 'Semua perubahan tersimpan';
            } else {
                saveStatus.value = 'error';
                saveMessage.value = 'Gagal menyimpan perubahan';
            }
        } catch {
            saveStatus.value = 'error';
            saveMessage.value = 'Kesalahan jaringan saat menyimpan';
        }
    }, 800);
}

// Field operations
function openAddFieldModal() {
    editingFieldId.value = null;
    fieldForm.value = {
        type: 'text',
        label: '',
        placeholder: '',
        help_text: '',
        required: false,
        options_text: '',
        section_id: '',
        formula_ref: '',
        formula_op: 'multiply',
        formula_val: '',
    };
    showFieldModal.value = true;
}

function openEditFieldModal(field) {
    editingFieldId.value = field.id;
    fieldForm.value = {
        type: field.type,
        label: field.label,
        placeholder: field.placeholder ?? '',
        help_text: field.help_text ?? '',
        required: Boolean(field.required),
        options_text: (field.options || []).join('\n'),
        section_id: field.section_id ?? '',
        formula_ref: field.formula?.ref_field_id ?? '',
        formula_op: field.formula?.operation ?? 'multiply',
        formula_val: field.formula?.value ?? '',
    };
    showFieldModal.value = true;
}

function saveField() {
    if (!fieldForm.value.label.trim()) {
        alert('Label pertanyaan wajib diisi.');
        return;
    }

    const payload = {
        type: fieldForm.value.type,
        label: fieldForm.value.label,
        placeholder: fieldForm.value.placeholder,
        help_text: fieldForm.value.help_text,
        required: fieldForm.value.required,
        section_id: fieldForm.value.section_id || null,
        options: ['select', 'radio', 'checkbox'].includes(fieldForm.value.type)
            ? fieldForm.value.options_text.split('\n').map((s) => s.trim()).filter(Boolean)
            : null,
        formula: fieldForm.value.type === 'computed' && fieldForm.value.formula_ref
            ? {
                  ref_field_id: Number(fieldForm.value.formula_ref),
                  operation: fieldForm.value.formula_op,
                  value: fieldForm.value.formula_val !== '' ? Number(fieldForm.value.formula_val) : null,
              }
            : null,
    };

    if (editingFieldId.value) {
        router.put(`/forms/${props.form.id}/fields/${editingFieldId.value}`, payload, {
            preserveScroll: true,
            onSuccess: () => {
                showFieldModal.value = false;
            },
        });
    } else {
        router.post(`/forms/${props.form.id}/fields`, payload, {
            preserveScroll: true,
            onSuccess: () => {
                showFieldModal.value = false;
            },
        });
    }
}

function deleteField(field) {
    if (!window.confirm(`Hapus pertanyaan "${field.label}"?`)) {
        return;
    }

    router.delete(`/forms/${props.form.id}/fields/${field.id}`, { preserveScroll: true });
}

function cloneField(field) {
    router.post(`/forms/${props.form.id}/fields/${field.id}/clone`, {}, { preserveScroll: true });
}

function moveField(index, direction) {
    const fields = [...props.form.fields];
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= fields.length) return;

    const temp = fields[index];
    fields[index] = fields[targetIndex];
    fields[targetIndex] = temp;

    router.post(`/forms/${props.form.id}/fields/reorder`, {
        order: fields.map((f) => f.id),
    }, { preserveScroll: true });
}

// Section operations
function openAddSectionModal() {
    editingSectionId.value = null;
    sectionForm.value = { title: '', description: '' };
    showSectionModal.value = true;
}

function openEditSectionModal(section) {
    editingSectionId.value = section.id;
    sectionForm.value = { title: section.title, description: section.description ?? '' };
    showSectionModal.value = true;
}

function saveSection() {
    if (!sectionForm.value.title.trim()) {
        alert('Judul bagian wajib diisi.');
        return;
    }

    if (editingSectionId.value) {
        router.put(`/forms/${props.form.id}/sections/${editingSectionId.value}`, sectionForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                showSectionModal.value = false;
            },
        });
    } else {
        router.post(`/forms/${props.form.id}/sections`, sectionForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                showSectionModal.value = false;
            },
        });
    }
}

function deleteSection(section) {
    if (!window.confirm(`Hapus bagian "${section.title}"? Field di dalamnya akan tetap tersimpan.`)) {
        return;
    }

    router.delete(`/forms/${props.form.id}/sections/${section.id}`, { preserveScroll: true });
}

function moveSection(index, direction) {
    const sections = [...props.form.sections];
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= sections.length) return;

    const temp = sections[index];
    sections[index] = sections[targetIndex];
    sections[targetIndex] = temp;

    router.post(`/forms/${props.form.id}/sections/reorder`, {
        order: sections.map((s) => s.id),
    }, { preserveScroll: true });
}

// Publish & Close
function publishForm() {
    router.post(`/forms/${props.form.id}/publish`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            showPublishModal.value = true;
        },
    });
}

function closeForm() {
    if (!window.confirm('Tutup formulir ini? Responden tidak akan bisa mengisi lagi.')) {
        return;
    }

    router.post(`/forms/${props.form.id}/close`, {}, { preserveScroll: true });
}

function copyUrl() {
    navigator.clipboard.writeText(props.form.public_url);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}

const statusBadge = computed(() => {
    if (props.form.status === 'published') {
        return { text: 'Diterbitkan', class: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800' };
    }
    if (props.form.status === 'closed') {
        return { text: 'Ditutup', class: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800' };
    }
    return { text: 'Draft', class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800' };
});

const qrCodeUrl = computed(() => {
    return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(props.form.public_url)}`;
});
</script>

<template>
    <Head :title="`Editor: ${form.title}`" />

    <AppLayout>
        <template #title>
            <div class="flex items-center gap-3">
                <Link href="/forms" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <span class="truncate">{{ form.title }}</span>
                <span :class="['rounded-full border px-2.5 py-0.5 text-xs font-semibold', statusBadge.class]">
                    {{ statusBadge.text }}
                </span>
            </div>
        </template>

        <!-- Top Control Bar -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-800">
            <!-- Tabs -->
            <div class="flex items-center gap-1 rounded-xl bg-gray-100 p-1 dark:bg-slate-900">
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-xs font-semibold transition"
                    :class="currentTab === 'fields' ? 'bg-white text-kbb-700 shadow-xs dark:bg-slate-800 dark:text-kbb-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'"
                    @click="currentTab = 'fields'"
                >
                    Pertanyaan ({{ form.fields.length }})
                </button>
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-xs font-semibold transition"
                    :class="currentTab === 'sections' ? 'bg-white text-kbb-700 shadow-xs dark:bg-slate-800 dark:text-kbb-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'"
                    @click="currentTab = 'sections'"
                >
                    Bagian / Steps ({{ form.sections.length }})
                </button>
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-xs font-semibold transition"
                    :class="currentTab === 'settings' ? 'bg-white text-kbb-700 shadow-xs dark:bg-slate-800 dark:text-kbb-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'"
                    @click="currentTab = 'settings'"
                >
                    Pengaturan
                </button>
            </div>

            <!-- Autosave Indicator & Actions -->
            <div class="flex items-center gap-3">
                <div class="hidden items-center gap-2 text-xs sm:flex">
                    <span v-if="saveStatus === 'saving'" class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        {{ saveMessage }}
                    </span>
                    <span v-else-if="saveStatus === 'saved'" class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ saveMessage }}
                    </span>
                    <span v-else class="flex items-center gap-1.5 text-red-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        {{ saveMessage }}
                    </span>
                </div>

                <a
                    :href="form.public_url"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
                >
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Pratinjau
                </a>

                <button
                    v-if="form.status !== 'published'"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-emerald-700"
                    @click="publishForm"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    Terbitkan Form
                </button>

                <button
                    v-else
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-red-700"
                    @click="closeForm"
                >
                    Tutup Form
                </button>
            </div>
        </div>

        <!-- TAB 1: PERTANYAAN / FIELDS -->
        <div v-if="currentTab === 'fields'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Daftar Pertanyaan</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Atur dan sesuaikan pertanyaan pada formulir ini</p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-kbb-700 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-kbb-800 dark:bg-kbb-600"
                    @click="openAddFieldModal"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Pertanyaan
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="form.fields.length === 0" class="rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center dark:border-slate-700">
                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h4 class="mt-4 text-sm font-semibold text-gray-900 dark:text-gray-100">Belum ada pertanyaan</h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mulai dengan menambahkan pertanyaan pertama untuk responden Anda.</p>
                <button
                    type="button"
                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-kbb-700 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-kbb-800 dark:bg-kbb-600"
                    @click="openAddFieldModal"
                >
                    Tambah Pertanyaan Sekarang
                </button>
            </div>

            <!-- Fields list -->
            <div v-else class="space-y-3">
                <div
                    v-for="(field, index) in form.fields"
                    :key="field.id"
                    class="flex items-center justify-between gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-xs transition hover:border-gray-200 dark:border-slate-800 dark:bg-slate-800 dark:hover:border-slate-700"
                >
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600 dark:bg-slate-700 dark:text-gray-300">
                            {{ index + 1 }}
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ field.label }}</h4>
                                <span v-if="field.required" class="text-xs font-bold text-red-500">*wajib</span>
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-slate-700 dark:text-gray-300">
                                    {{ field.type }}
                                </span>
                            </div>
                            <p v-if="field.help_text" class="text-xs text-gray-400">{{ field.help_text }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <!-- Move Up / Down -->
                        <button
                            type="button"
                            :disabled="index === 0"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 disabled:opacity-30 dark:hover:bg-slate-700"
                            title="Pindah ke Atas"
                            @click="moveField(index, -1)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            :disabled="index === form.fields.length - 1"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 disabled:opacity-30 dark:hover:bg-slate-700"
                            title="Pindah ke Bawah"
                            @click="moveField(index, 1)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Clone -->
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-kbb-700 dark:hover:bg-slate-700 dark:hover:text-kbb-400"
                            title="Duplikasi Pertanyaan"
                            @click="cloneField(field)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                        </button>

                        <!-- Edit -->
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-blue-600 dark:hover:bg-slate-700"
                            title="Edit Pertanyaan"
                            @click="openEditFieldModal(field)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>

                        <!-- Delete -->
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30"
                            title="Hapus Pertanyaan"
                            @click="deleteField(field)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: BAGIAN / SECTIONS -->
        <div v-if="currentTab === 'sections'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Tahapan Formulir (Multi-Step)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Bagi formulir yang panjang menjadi beberapa langkah/tahapan pengisian</p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-kbb-700 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-kbb-800 dark:bg-kbb-600"
                    @click="openAddSectionModal"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Bagian
                </button>
            </div>

            <div v-if="form.sections.length === 0" class="rounded-2xl border-2 border-dashed border-gray-200 p-8 text-center dark:border-slate-700">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Belum ada bagian khusus</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Seluruh pertanyaan saat ini akan ditampilkan dalam 1 halaman tunggal.</p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="(sec, index) in form.sections"
                    :key="sec.id"
                    class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-800"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-kbb-50 px-2 py-0.5 text-xs font-bold text-kbb-700 dark:bg-kbb-900/30 dark:text-kbb-300">
                                Langkah {{ index + 1 }}
                            </span>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ sec.title }}</h4>
                        </div>
                        <p v-if="sec.description" class="mt-1 text-xs text-gray-400">{{ sec.description }}</p>
                    </div>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            :disabled="index === 0"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 disabled:opacity-30 dark:hover:bg-slate-700"
                            @click="moveSection(index, -1)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                        </button>
                        <button
                            type="button"
                            :disabled="index === form.sections.length - 1"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 disabled:opacity-30 dark:hover:bg-slate-700"
                            @click="moveSection(index, 1)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-blue-600 dark:hover:bg-slate-700"
                            @click="openEditSectionModal(sec)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </button>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30"
                            @click="deleteSection(sec)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: PENGATURAN / SETTINGS -->
        <div v-if="currentTab === 'settings'" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-800 sm:p-8">
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pengaturan Umum Formulir</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Perubahan pada pengaturan akan otomatis tersimpan.</p>

            <div class="mt-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Judul Formulir</label>
                        <input
                            v-model="settings.title"
                            type="text"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                            @input="triggerAutoSave"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Deskripsi / Petunjuk Pengisian</label>
                        <textarea
                            v-model="settings.description"
                            rows="3"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                            @input="triggerAutoSave"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Waktu Pembukaan (Opsional)</label>
                        <input
                            v-model="settings.starts_at"
                            type="datetime-local"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                            @change="triggerAutoSave"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Waktu Penutupan (Opsional)</label>
                        <input
                            v-model="settings.ends_at"
                            type="datetime-local"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                            @change="triggerAutoSave"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Batas Maksimal Respon (0 = Tanpa Batas)</label>
                        <input
                            v-model="settings.max_submissions"
                            type="number"
                            min="0"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                            @input="triggerAutoSave"
                        />
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6 dark:border-slate-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Keamanan & Pembatasan</h4>
                    <div class="mt-4 space-y-3">
                        <label class="flex items-center gap-3">
                            <input
                                v-model="settings.require_auth"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-kbb-700 focus:ring-kbb-500"
                                @change="triggerAutoSave"
                            />
                            <span class="text-xs text-gray-700 dark:text-gray-300">Wajibkan responden login akun sebelum mengisi</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input
                                v-model="settings.limit_one_response"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-kbb-700 focus:ring-kbb-500"
                                @change="triggerAutoSave"
                            />
                            <span class="text-xs text-gray-700 dark:text-gray-300">Batasi hanya 1 kali pengisian per akun pengguna</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input
                                v-model="settings.collect_ip"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-kbb-700 focus:ring-kbb-500"
                                @change="triggerAutoSave"
                            />
                            <span class="text-xs text-gray-700 dark:text-gray-300">Catat alamat IP dan User-Agent responden untuk audit integritas</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input
                                v-model="settings.show_kbb_logo"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-kbb-700 focus:ring-kbb-500"
                                @change="triggerAutoSave"
                            />
                            <span class="text-xs text-gray-700 dark:text-gray-300">Tampilkan logo resmi Kabupaten Bandung Barat pada header form publik</span>
                        </label>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6 dark:border-slate-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Konfirmasi Setelah Pengisian</h4>
                    <div class="mt-4 space-y-4">
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2">
                                <input
                                    v-model="settings.confirmation_type"
                                    type="radio"
                                    value="message"
                                    class="text-kbb-700 focus:ring-kbb-500"
                                    @change="triggerAutoSave"
                                />
                                <span class="text-xs text-gray-700 dark:text-gray-300">Pesan Konfirmasi</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input
                                    v-model="settings.confirmation_type"
                                    type="radio"
                                    value="redirect"
                                    class="text-kbb-700 focus:ring-kbb-500"
                                    @change="triggerAutoSave"
                                />
                                <span class="text-xs text-gray-700 dark:text-gray-300">Redirect ke Tautan Eksternal</span>
                            </label>
                        </div>

                        <div v-if="settings.confirmation_type === 'message'">
                            <label class="block text-xs text-gray-500 dark:text-gray-400">Isi Pesan Sukses</label>
                            <input
                                v-model="settings.confirmation_message"
                                type="text"
                                class="mt-1 block w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                                @input="triggerAutoSave"
                            />
                        </div>

                        <div v-else>
                            <label class="block text-xs text-gray-500 dark:text-gray-400">URL Tujuan Pengalihan (http/https)</label>
                            <input
                                v-model="settings.redirect_url"
                                type="url"
                                placeholder="https://bandungbaratkab.go.id"
                                class="mt-1 block w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                                @input="triggerAutoSave"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: ADD/EDIT FIELD -->
        <div v-if="showFieldModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-800">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-slate-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                        {{ editingFieldId ? 'Edit Pertanyaan' : 'Tambah Pertanyaan Baru' }}
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="showFieldModal = false">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Tipe Input</label>
                        <select
                            v-model="fieldForm.type"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        >
                            <option v-for="t in fieldTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Label / Pertanyaan</label>
                        <input
                            v-model="fieldForm.label"
                            type="text"
                            required
                            placeholder="Contoh: Nomor Induk Kependudukan (NIK)"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        />
                    </div>

                    <div v-if="!['heading', 'paragraph', 'computed', 'signature'].includes(fieldForm.type)">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Teks Placeholder (Opsional)</label>
                        <input
                            v-model="fieldForm.placeholder"
                            type="text"
                            placeholder="Contoh: Masukkan 16 digit NIK"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Petunjuk Bantuan (Help Text)</label>
                        <input
                            v-model="fieldForm.help_text"
                            type="text"
                            placeholder="Keterangan tambahan di bawah input..."
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        />
                    </div>

                    <div v-if="form.sections.length > 0">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Tempatkan di Bagian / Section</label>
                        <select
                            v-model="fieldForm.section_id"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        >
                            <option value="">-- Tanpa Bagian (Halaman Utama) --</option>
                            <option v-for="sec in form.sections" :key="sec.id" :value="sec.id">{{ sec.title }}</option>
                        </select>
                    </div>

                    <!-- Options for select/radio/checkbox -->
                    <div v-if="['select', 'radio', 'checkbox'].includes(fieldForm.type)">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Pilihan Jawaban (Satu baris per pilihan)
                        </label>
                        <textarea
                            v-model="fieldForm.options_text"
                            rows="4"
                            placeholder="Pilihan 1&#10;Pilihan 2&#10;Pilihan 3"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        ></textarea>
                    </div>

                    <!-- Formula builder for computed -->
                    <div v-if="fieldForm.type === 'computed'" class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-900/40 dark:bg-amber-950/20">
                        <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200">Konfigurasi Rumus Perhitungan</h4>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] text-gray-600 dark:text-gray-400">Field Referensi</label>
                                <select
                                    v-model="fieldForm.formula_ref"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                                >
                                    <option value="">Pilih Field</option>
                                    <option v-for="f in form.fields.filter(x => x.id !== editingFieldId && ['number', 'text'].includes(x.type))" :key="f.id" :value="f.id">
                                        {{ f.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-600 dark:text-gray-400">Operasi</label>
                                <select
                                    v-model="fieldForm.formula_op"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                                >
                                    <option value="multiply">x (Dikali)</option>
                                    <option value="add">+ (Ditambah)</option>
                                    <option value="subtract">- (Dikurang)</option>
                                    <option value="divide">/ (Dibagi)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-600 dark:text-gray-400">Nilai Pengali/Konstanta</label>
                                <input
                                    v-model="fieldForm.formula_val"
                                    type="number"
                                    step="any"
                                    placeholder="Contoh: 10000"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                                />
                            </div>
                        </div>
                    </div>

                    <div v-if="!['heading', 'paragraph', 'computed'].includes(fieldForm.type)">
                        <label class="flex items-center gap-2">
                            <input v-model="fieldForm.required" type="checkbox" class="h-4 w-4 rounded text-kbb-700 focus:ring-kbb-500" />
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Wajib Diisi oleh Responden</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-slate-700">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-700 dark:text-gray-300"
                        @click="showFieldModal = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-xl bg-kbb-700 px-5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-kbb-800 dark:bg-kbb-600"
                        @click="saveField"
                    >
                        Simpan Pertanyaan
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: ADD/EDIT SECTION -->
        <div v-if="showSectionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-800">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-slate-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                        {{ editingSectionId ? 'Edit Bagian' : 'Tambah Bagian Formulir' }}
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="showSectionModal = false">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Judul Bagian</label>
                        <input
                            v-model="sectionForm.title"
                            type="text"
                            required
                            placeholder="Contoh: Data Pribadi & Kontak"
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Deskripsi Bagian (Opsional)</label>
                        <textarea
                            v-model="sectionForm.description"
                            rows="2"
                            placeholder="Petunjuk singkat bagian ini..."
                            class="mt-1 block w-full rounded-xl border border-gray-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        ></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-slate-700">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-slate-700 dark:text-gray-300"
                        @click="showSectionModal = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-xl bg-kbb-700 px-5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-kbb-800 dark:bg-kbb-600"
                        @click="saveSection"
                    >
                        Simpan Bagian
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: PUBLISH SUCCESS & QR CODE -->
        <div v-if="showPublishModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-xl dark:bg-slate-800">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h3 class="mt-3 text-lg font-bold text-gray-900 dark:text-gray-100">Formulir Berhasil Diterbitkan!</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Formulir Anda sekarang sudah dapat diakses publik melalui tautan atau QR Code berikut:
                </p>

                <!-- QR Code preview -->
                <div class="mt-4 flex justify-center">
                    <div class="rounded-2xl border border-gray-100 bg-white p-3 shadow-xs dark:border-slate-700">
                        <img :src="qrCodeUrl" alt="QR Code Form" class="h-36 w-36" />
                    </div>
                </div>

                <!-- URL Copy Box -->
                <div class="mt-4 flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-2 dark:border-slate-700 dark:bg-slate-900">
                    <input
                        :value="form.public_url"
                        readonly
                        class="w-full bg-transparent text-xs text-gray-700 focus:outline-hidden dark:text-gray-300"
                    />
                    <button
                        type="button"
                        class="rounded-lg bg-kbb-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-kbb-800 dark:bg-kbb-600"
                        @click="copyUrl"
                    >
                        {{ copied ? 'Tersalin!' : 'Salin' }}
                    </button>
                </div>

                <button
                    type="button"
                    class="mt-6 w-full rounded-xl border border-gray-200 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:text-gray-300"
                    @click="showPublishModal = false"
                >
                    Selesai
                </button>
            </div>
        </div>
    </AppLayout>
</template>
