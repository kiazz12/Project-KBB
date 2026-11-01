<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const logoUrl = '/images/kbb-logo.png';
const showPassword = ref(false);
const showLimitModal = ref(Boolean(page.props.flash?.login_limit_error));

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const errorMessage = computed(() => form.errors.email || page.props.flash?.error || '');

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Login" />

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-kbb-800 via-[#001a3a] to-kbb-950 px-4 py-10">
        <div class="bg-grid absolute inset-0"></div>

        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="animate-blob-1 absolute -right-40 -top-40 h-[600px] w-[600px] rounded-full bg-gradient-to-br from-kbb-500/20 via-transparent to-transparent blur-3xl"></div>
            <div class="animate-blob-2 absolute -bottom-48 -left-40 h-[700px] w-[700px] rounded-full bg-gradient-to-br from-gold-500/10 via-transparent to-transparent blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-sm">
            <div class="overflow-hidden rounded-2xl bg-white shadow-[0_8px_40px_rgba(0,0,0,0.3)] dark:bg-slate-800">
                <div class="relative overflow-hidden bg-gradient-to-br from-kbb-700 via-kbb-800 to-[#001a3a] px-8 pb-6 pt-8 text-white">
                    <div class="absolute -right-8 top-0 h-32 w-32 rounded-full bg-white/[0.04]"></div>
                    <div class="absolute -bottom-6 -left-6 h-24 w-24 rounded-full bg-white/[0.03]"></div>

                    <div class="relative text-center">
                        <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 shadow-lg ring-4 ring-white/20 backdrop-blur-sm">
                            <img :src="logoUrl" alt="KBB" class="h-10 w-10">
                        </div>
                        <h1 class="text-xl font-bold">Formulir Online</h1>
                        <p class="mt-0.5 text-sm text-kbb-200/80">Pemerintah Kabupaten Bandung Barat</p>
                    </div>
                </div>

                <div class="p-8">
                    <div
                        v-if="errorMessage"
                        class="mb-5 flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/15 dark:text-red-400"
                    >
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ errorMessage }}
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="admin@dinas.com"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                            >
                        </div>

                        <div>
                            <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 pr-12 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-kbb-500 focus:ring-2 focus:ring-kbb-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100"
                                >
                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-slate-700 dark:hover:text-gray-200"
                                    :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                    @click="showPassword = !showPassword"
                                >
                                    <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <label class="flex cursor-pointer select-none items-center gap-2.5">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                name="remember"
                                class="h-4 w-4 rounded border-gray-300 text-kbb-700 focus:ring-kbb-500/20 dark:border-slate-600"
                            >
                            <span class="text-sm text-gray-600 dark:text-gray-300">Ingat saya</span>
                        </label>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-xl bg-gradient-to-r from-kbb-700 to-kbb-800 py-3 font-semibold text-white shadow-lg shadow-kbb-700/30 transition-all duration-200 hover:-translate-y-0.5 hover:from-kbb-800 hover:to-kbb-900 hover:shadow-kbb-700/50 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            {{ form.processing ? 'Memproses…' : 'Masuk' }}
                        </button>
                    </form>
                </div>
            </div>

            <p class="mt-5 text-center text-xs text-white/40">
                &copy; {{ new Date().getFullYear() }} Pemerintah Kabupaten Bandung Barat
            </p>
        </div>

        <div
            v-if="showLimitModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 backdrop-blur-sm"
            @click.self="showLimitModal = false"
        >
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-slate-800">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 dark:bg-red-500/15">
                    <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-gray-100">Session Login Terbatas</h3>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">{{ page.props.flash?.login_limit_error }}</p>
                <button
                    type="button"
                    class="w-full rounded-xl bg-kbb-700 py-2.5 font-medium text-white transition hover:bg-kbb-800"
                    @click="showLimitModal = false"
                >
                    Mengerti
                </button>
            </div>
        </div>
    </div>
</template>
