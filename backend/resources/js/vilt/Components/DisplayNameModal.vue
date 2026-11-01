<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const show = ref(false);
const displayName = ref('');
const error = ref('');
const isSubmitting = ref(false);

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

onMounted(() => {
    if (user.value && (!user.value.display_name || user.value.display_name.trim() === '')) {
        displayName.value = user.value.name || '';
        show.value = true;
    }
});

async function save() {
    if (!displayName.value || displayName.value.trim().length < 2) {
        error.value = 'Nama tampilan minimal 2 karakter.';
        return;
    }

    isSubmitting.value = true;
    error.value = '';

    try {
        const res = await fetch('/session/display-name', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ displayName: displayName.value }),
        });

        if (res.ok) {
            show.value = false;
            if (user.value) {
                user.value.display_name = displayName.value;
            }
        } else {
            const data = await res.json();
            error.value = data.message || 'Gagal menyimpan nama tampilan.';
        }
    } catch {
        error.value = 'Terjadi kesalahan jaringan.';
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs"
    >
        <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-800 sm:p-8">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-kbb-50 text-kbb-700 dark:bg-kbb-900/40 dark:text-kbb-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>

            <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-gray-100">Selamat Datang!</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Silakan konfirmasi nama tampilan Anda untuk sesi kerja ini:
            </p>

            <form @submit.prevent="save" class="mt-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Nama Tampilan Sesi</label>
                    <input
                        v-model="displayName"
                        type="text"
                        required
                        minlength="2"
                        maxlength="255"
                        placeholder="Nama lengkap Anda..."
                        class="mt-1.5 block w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 dark:border-slate-700 dark:bg-slate-900 dark:text-gray-100"
                        :class="{ 'border-red-500': error }"
                    />
                    <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="w-full rounded-xl bg-kbb-700 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-kbb-800 disabled:opacity-50 dark:bg-kbb-600"
                    >
                        {{ isSubmitting ? 'Menyimpan…' : 'Simpan dan Lanjutkan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
