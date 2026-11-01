<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    error: { type: String, default: null },
    requiresAuth: { type: Boolean, default: false },
    form: { type: Object, default: null },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});
const isSubmitted = computed(() => Boolean(flash.value.submitted));
const logoUrl = '/images/kbb-logo.png';

// Responses model: field_id => value
const responses = ref({});
const files = ref({}); // field_id => File object
const honeypot = ref('');
const currentStep = ref(0);
const isSubmitting = ref(false);
const clientErrors = ref({});

// Multi-step grouping
const steps = computed(() => {
    if (!props.form || !props.form.fields) return [];

    const sections = props.form.sections || [];
    const fields = props.form.fields || [];

    if (sections.length === 0) {
        return [{ title: 'Formulir', description: '', fields }];
    }

    const result = [];
    for (const sec of sections) {
        const secFields = fields.filter((f) => f.section_id === sec.id);
        if (secFields.length > 0) {
            result.push({
                id: sec.id,
                title: sec.title,
                description: sec.description,
                fields: secFields,
            });
        }
    }

    const noSecFields = fields.filter((f) => !f.section_id);
    if (noSecFields.length > 0) {
        result.push({
            id: null,
            title: 'Informasi Tambahan',
            description: '',
            fields: noSecFields,
        });
    }

    return result.length > 0 ? result : [{ title: 'Formulir', description: '', fields }];
});

const currentStepData = computed(() => steps.value[currentStep.value] || null);

// REALTIME COMPUTED FORMULA CALCULATION
function recalculateComputedFields() {
    if (!props.form || !props.form.fields) return;

    for (const field of props.form.fields) {
        if (field.type !== 'computed' || !field.formula) continue;

        const refId = field.formula.ref_field_id;
        const op = field.formula.operation || 'multiply';
        const constVal = field.formula.value;

        if (!refId) continue;

        const ref1 = parseFloat(responses.value[refId]) || 0;
        let ref2 = 0;

        if (constVal !== null && constVal !== undefined && constVal !== '') {
            ref2 = parseFloat(constVal) || 0;
        } else {
            const ref2Id = field.formula.ref_field_id_2;
            ref2 = ref2Id ? parseFloat(responses.value[ref2Id]) || 0 : 0;
        }

        let result = 0;
        switch (op) {
            case 'multiply':
                result = ref1 * ref2;
                break;
            case 'add':
                result = ref1 + ref2;
                break;
            case 'subtract':
                result = ref1 - ref2;
                break;
            case 'divide':
                result = ref2 !== 0 ? ref1 / ref2 : 0;
                break;
            default:
                result = ref1;
        }

        responses.value[field.id] = Math.round(result).toString();
    }
}

watch(
    () => ({ ...responses.value }),
    () => {
        recalculateComputedFields();
    },
    { deep: true },
);

// File change handler
function handleFileChange(event, fieldId) {
    const file = event.target.files?.[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        clientErrors.value[fieldId] = 'Ukuran berkas maksimal 2MB.';
        event.target.value = '';
        delete files.value[fieldId];
        return;
    }

    delete clientErrors.value[fieldId];
    files.value[fieldId] = file;
}

// Checkbox change handler
function toggleCheckbox(fieldId, optionValue) {
    if (!Array.isArray(responses.value[fieldId])) {
        responses.value[fieldId] = [];
    }
    const idx = responses.value[fieldId].indexOf(optionValue);
    if (idx > -1) {
        responses.value[fieldId].splice(idx, 1);
    } else {
        responses.value[fieldId].push(optionValue);
    }
}

// Step navigation & validation
function validateCurrentStep() {
    clientErrors.value = {};
    if (!currentStepData.value) return true;

    let valid = true;
    for (const field of currentStepData.value.fields) {
        if (!field.required || ['heading', 'paragraph', 'computed'].includes(field.type)) {
            continue;
        }

        if (field.type === 'file') {
            if (!files.value[field.id]) {
                clientErrors.value[field.id] = `${field.label} wajib diunggah.`;
                valid = false;
            }
        } else if (field.type === 'checkbox') {
            if (!responses.value[field.id] || responses.value[field.id].length === 0) {
                clientErrors.value[field.id] = `Pilih minimal satu opsi pada ${field.label}.`;
                valid = false;
            }
        } else {
            const val = responses.value[field.id];
            if (val === undefined || val === null || val.toString().trim() === '') {
                clientErrors.value[field.id] = `${field.label} wajib diisi.`;
                valid = false;
            }
        }
    }

    return valid;
}

function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep.value < steps.value.length - 1) {
            currentStep.value++;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}

function prevStep() {
    if (currentStep.value > 0) {
        currentStep.value--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Form submission
function submitForm() {
    if (!validateCurrentStep()) return;

    isSubmitting.value = true;
    recalculateComputedFields();

    const formData = new FormData();
    formData.append('company_website', honeypot.value);

    for (const [key, val] of Object.entries(responses.value)) {
        if (Array.isArray(val)) {
            val.forEach((v) => formData.append(`responses[${key}][]`, v));
        } else if (val !== null && val !== undefined) {
            formData.append(`responses[${key}]`, val);
        }
    }

    for (const [key, file] of Object.entries(files.value)) {
        formData.append(`responses[${key}]`, file);
    }

    router.post(`/form/${props.form.slug}`, formData, {
        forceFormData: true,
        onFinish: () => {
            isSubmitting.value = false;
        },
        onSuccess: () => {
            if (props.form.confirmation_type === 'redirect' && props.form.redirect_url) {
                window.location.href = props.form.redirect_url;
            }
        },
    });
}
</script>

<template>
    <Head :title="form ? form.title : 'Formulir Online KBB'" />

    <div class="min-h-screen bg-slate-50 py-10 px-4 text-gray-900 antialiased dark:bg-slate-900 dark:text-gray-100 sm:px-6 sm:py-16">
        <div class="mx-auto max-w-2xl">
            <!-- Header Branding -->
            <div class="mb-8 text-center">
                <img
                    v-if="form?.show_kbb_logo ?? true"
                    :src="logoUrl"
                    alt="Logo Kabupaten Bandung Barat"
                    class="mx-auto mb-3 h-14 w-14 object-contain drop-shadow-sm"
                />
                <h1 class="text-xs font-bold uppercase tracking-wider text-kbb-700 dark:text-kbb-400">
                    Pemerintah Kabupaten Bandung Barat
                </h1>
                <p class="text-[11px] text-gray-400">Layanan Formulir & Pendataan Digital Terpadu</p>
            </div>

            <!-- Error State / Guardrails -->
            <div
                v-if="error"
                class="overflow-hidden rounded-3xl border border-red-100 bg-white p-8 text-center shadow-lg dark:border-red-900/30 dark:bg-slate-800"
            >
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-gray-100">Formulir Tidak Dapat Diakses</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ error }}</p>

                <div v-if="requiresAuth" class="mt-6">
                    <a
                        href="/login"
                        class="inline-flex items-center gap-2 rounded-xl bg-kbb-700 px-6 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-kbb-800 dark:bg-kbb-600"
                    >
                        Login Sekarang &rarr;
                    </a>
                </div>
            </div>

            <!-- Submission Success State -->
            <div
                v-else-if="isSubmitted"
                class="overflow-hidden rounded-3xl border border-emerald-100 bg-white p-8 text-center shadow-lg dark:border-emerald-900/30 dark:bg-slate-800"
            >
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="mt-5 text-xl font-bold text-gray-900 dark:text-gray-100">Jawaban Berhasil Terkirim!</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ form?.confirmation_message || 'Terima kasih, data Anda telah berhasil dicatat oleh sistem.' }}
                </p>

                <div class="mt-8">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200 bg-gray-50 px-5 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-300"
                        @click="() => window.location.reload()"
                    >
                        Kirim Jawaban Lain
                    </button>
                </div>
            </div>

            <!-- Active Form Content -->
            <div v-else-if="form" class="space-y-6">
                <!-- Title Card -->
                <div class="overflow-hidden rounded-3xl border-t-8 border-kbb-700 bg-white p-6 shadow-sm dark:border-kbb-600 dark:bg-slate-800 sm:p-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ form.title }}</h2>
                    <p v-if="form.description" class="mt-2 text-sm text-gray-600 whitespace-pre-wrap dark:text-gray-300">
                        {{ form.description }}
                    </p>

                    <!-- Multi-step Indicator -->
                    <div v-if="steps.length > 1" class="mt-6 border-t border-gray-100 pt-4 dark:border-slate-700">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-bold text-kbb-700 dark:text-kbb-400">
                                Langkah {{ currentStep + 1 }} dari {{ steps.length }}
                            </span>
                            <span>{{ currentStepData?.title }}</span>
                        </div>
                        <div class="mt-2 flex gap-1.5">
                            <div
                                v-for="(st, idx) in steps"
                                :key="idx"
                                class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                :class="idx <= currentStep ? 'bg-kbb-700 dark:bg-kbb-500' : 'bg-gray-200 dark:bg-slate-700'"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Step Section Details -->
                <div v-if="currentStepData && steps.length > 1" class="rounded-2xl border border-gray-100 bg-white px-6 py-4 shadow-xs dark:border-slate-800 dark:bg-slate-800">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ currentStepData.title }}</h3>
                    <p v-if="currentStepData.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ currentStepData.description }}
                    </p>
                </div>

                <!-- Form Fields Card -->
                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Honeypot -->
                    <input
                        v-model="honeypot"
                        type="text"
                        name="company_website"
                        tabindex="-1"
                        autocomplete="off"
                        style="position: absolute; opacity: 0; pointer-events: none; height: 0; width: 0;"
                    />

                    <!-- Fields in Current Step -->
                    <div
                        v-for="field in currentStepData?.fields || []"
                        :key="field.id"
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs transition dark:border-slate-800 dark:bg-slate-800 sm:p-6"
                    >
                        <!-- HEADING -->
                        <div v-if="field.type === 'heading'" class="border-b border-gray-100 pb-2 dark:border-slate-700">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ field.label }}</h3>
                            <p v-if="field.help_text" class="text-xs text-gray-500 dark:text-gray-400">{{ field.help_text }}</p>
                        </div>

                        <!-- PARAGRAPH -->
                        <div v-else-if="field.type === 'paragraph'" class="rounded-xl bg-gray-50 p-4 text-xs text-gray-600 dark:bg-slate-900/60 dark:text-gray-300">
                            <p class="font-semibold">{{ field.label }}</p>
                            <p v-if="field.help_text" class="mt-1">{{ field.help_text }}</p>
                        </div>

                        <!-- INPUT CONTROLS -->
                        <div v-else class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ field.label }}
                                <span v-if="field.required" class="text-red-500">*</span>
                            </label>

                            <p v-if="field.help_text" class="text-xs text-gray-400">{{ field.help_text }}</p>

                            <!-- TEXT / NUMBER / EMAIL / DATE / TIME -->
                            <input
                                v-if="['text', 'number', 'email', 'date', 'time'].includes(field.type)"
                                v-model="responses[field.id]"
                                :type="field.type"
                                :placeholder="field.placeholder || ''"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                                :class="{ 'border-red-500': clientErrors[field.id] }"
                            />

                            <!-- TEXTAREA -->
                            <textarea
                                v-else-if="field.type === 'textarea'"
                                v-model="responses[field.id]"
                                rows="3"
                                :placeholder="field.placeholder || ''"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                                :class="{ 'border-red-500': clientErrors[field.id] }"
                            ></textarea>

                            <!-- SELECT -->
                            <select
                                v-else-if="field.type === 'select'"
                                v-model="responses[field.id]"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 transition focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                                :class="{ 'border-red-500': clientErrors[field.id] }"
                            >
                                <option value="">-- Pilih Jawaban --</option>
                                <option v-for="opt in field.options || []" :key="opt" :value="opt">{{ opt }}</option>
                            </select>

                            <!-- RADIO -->
                            <div v-else-if="field.type === 'radio'" class="space-y-2 pt-1">
                                <label
                                    v-for="opt in field.options || []"
                                    :key="opt"
                                    class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:bg-gray-50 dark:border-slate-700/60 dark:hover:bg-slate-700/30"
                                >
                                    <input
                                        v-model="responses[field.id]"
                                        type="radio"
                                        :value="opt"
                                        class="h-4 w-4 text-kbb-700 focus:ring-kbb-500"
                                    />
                                    <span class="text-xs text-gray-700 dark:text-gray-200">{{ opt }}</span>
                                </label>
                            </div>

                            <!-- CHECKBOX -->
                            <div v-else-if="field.type === 'checkbox'" class="space-y-2 pt-1">
                                <label
                                    v-for="opt in field.options || []"
                                    :key="opt"
                                    class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:bg-gray-50 dark:border-slate-700/60 dark:hover:bg-slate-700/30"
                                >
                                    <input
                                        type="checkbox"
                                        :value="opt"
                                        :checked="Array.isArray(responses[field.id]) && responses[field.id].includes(opt)"
                                        class="h-4 w-4 rounded text-kbb-700 focus:ring-kbb-500"
                                        @change="toggleCheckbox(field.id, opt)"
                                    />
                                    <span class="text-xs text-gray-700 dark:text-gray-200">{{ opt }}</span>
                                </label>
                            </div>

                            <!-- FILE UPLOAD -->
                            <div v-else-if="field.type === 'file'" class="pt-1">
                                <div class="rounded-2xl border-2 border-dashed border-gray-200 p-6 text-center dark:border-slate-700">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="mt-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        {{ files[field.id] ? files[field.id].name : 'Pilih atau seret berkas ke sini' }}
                                    </p>
                                    <p class="mt-1 text-[10px] text-gray-400">Format: JPG, PNG, PDF, DOC, DOCX (Maksimal 2MB)</p>
                                    <input
                                        type="file"
                                        class="mt-3 block w-full text-xs text-gray-500 file:mr-3 file:rounded-xl file:border-0 file:bg-kbb-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-kbb-700 hover:file:bg-kbb-100 dark:file:bg-kbb-900/30 dark:file:text-kbb-300"
                                        @change="(e) => handleFileChange(e, field.id)"
                                    />
                                </div>
                            </div>

                            <!-- COMPUTED (REALTIME RECALCULATED) -->
                            <div v-else-if="field.type === 'computed'" class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-900/40 dark:bg-amber-950/20">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Hasil Perhitungan Otomatis:</span>
                                <p class="mt-1 font-mono text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ responses[field.id] || '0' }}
                                </p>
                            </div>

                            <!-- SIGNATURE -->
                            <div v-else-if="field.type === 'signature'" class="pt-1">
                                <input
                                    v-model="responses[field.id]"
                                    type="text"
                                    placeholder="Ketik Nama Lengkap Anda sebagai tanda tangan persetujuan..."
                                    class="w-full rounded-xl border border-gray-200 px-4 py-2.5 font-serif italic text-sm text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                                    :class="{ 'border-red-500': clientErrors[field.id] }"
                                />
                            </div>

                            <!-- Error Message -->
                            <p v-if="clientErrors[field.id]" class="text-xs text-red-500">
                                {{ clientErrors[field.id] }}
                            </p>
                        </div>
                    </div>

                    <!-- Step Controls & Submit -->
                    <div class="flex items-center justify-between gap-4 pt-4">
                        <button
                            v-if="currentStep > 0"
                            type="button"
                            class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-xs font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-300"
                            @click="prevStep"
                        >
                            &larr; Sebelumnya
                        </button>
                        <div v-else></div>

                        <button
                            v-if="currentStep < steps.length - 1"
                            type="button"
                            class="rounded-xl bg-kbb-700 px-6 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-kbb-800 dark:bg-kbb-600"
                            @click="nextStep"
                        >
                            Selanjutnya &rarr;
                        </button>

                        <button
                            v-else
                            type="submit"
                            :disabled="isSubmitting"
                            class="inline-flex items-center gap-2 rounded-xl bg-kbb-700 px-7 py-3 text-xs font-bold uppercase tracking-wider text-white shadow-md transition hover:bg-kbb-800 disabled:opacity-50 dark:bg-kbb-600"
                        >
                            <svg v-if="isSubmitting" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            Kirim Jawaban
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
