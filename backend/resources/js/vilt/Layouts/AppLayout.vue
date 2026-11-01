<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import NotificationBell from '../Components/NotificationBell.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const isSuperAdmin = computed(() => Boolean(user.value?.is_super_admin));
const displayName = computed(() => user.value?.display_name || user.value?.name || '');
const initial = computed(() => (displayName.value ? displayName.value.charAt(0).toUpperCase() : '?'));
const logoUrl = '/images/kbb-logo.png';

const sidebarOpen = ref(false);
const mobileOpen = ref(false);

const flash = computed(() => page.props.flash ?? {});
const toast = ref(null);
let toastTimer = null;

function showToast(message, type) {
    if (!message) {
        return;
    }

    toast.value = { message, type };
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.value = null;
    }, 4000);
}

watch(
    () => [flash.value.success, flash.value.error],
    ([success, error]) => {
        if (success) {
            showToast(success, 'success');
        } else if (error) {
            showToast(error, 'error');
        }
    },
    { immediate: true },
);

const currentPath = computed(() => {
    if (typeof window === 'undefined') {
        return '';
    }

    return window.location.pathname;
});

function isActive(prefix) {
    return currentPath.value === prefix || currentPath.value.startsWith(prefix + '/');
}

const navItems = computed(() => {
    const items = [
        { label: 'Dashboard', href: '/dashboard', active: isActive('/dashboard'), icon: 'home' },
        { label: 'Forms', href: '/forms', active: isActive('/forms'), icon: 'document' },
    ];

    if (isSuperAdmin.value) {
        items.push(
            { label: 'Users', href: '/users', active: isActive('/users'), icon: 'users' },
            { label: 'Admin Panel', href: '/admin', active: isActive('/admin'), icon: 'settings' },
            { label: 'Notifikasi', href: '/notifications', active: isActive('/notifications'), icon: 'bell' },
        );
    }

    items.push({ label: 'Ubah Password', href: '/change-password', active: isActive('/change-password'), icon: 'key' });

    return items;
});

const icons = {
    home: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    users: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
    settings: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
    bell: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    key: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
};

const dark = ref(false);

function applyTheme() {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.classList.toggle('dark', dark.value);
    try {
        localStorage.setItem('kbb-theme', dark.value ? 'dark' : 'light');
    } catch (error) {
        // ignore storage failures
    }
}

function toggleTheme() {
    dark.value = !dark.value;
    applyTheme();
}

function logout() {
    router.post('/logout');
}

onMounted(() => {
    if (typeof document === 'undefined') {
        return;
    }

    let stored = null;
    try {
        stored = localStorage.getItem('kbb-theme');
    } catch (error) {
        // ignore
    }

    dark.value = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme();
});

onUnmounted(() => {
    clearTimeout(toastTimer);
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900 antialiased dark:bg-slate-900 dark:text-gray-100">
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="mobileOpen = false"
        ></div>

        <div class="flex h-screen overflow-hidden">
            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-300 dark:border-slate-700 dark:bg-slate-800 lg:static lg:translate-x-0"
                :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="flex h-16 items-center gap-3 border-b border-gray-100 px-5 dark:border-slate-700">
                    <img :src="logoUrl" alt="KBB" class="h-8 w-8">
                    <div class="min-w-0">
                        <h2 class="truncate text-sm font-semibold leading-tight">Formulir Online KBB</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ isSuperAdmin ? 'Super Admin' : 'Admin' }}</p>
                    </div>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    <a
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition"
                        :class="item.active
                            ? 'bg-kbb-50 font-medium text-kbb-700 dark:bg-kbb-500/15 dark:text-kbb-400'
                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-slate-700'"
                        
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons[item.icon]" />
                        </svg>
                        {{ item.label }}
                    </a>
                </nav>

                <div class="border-t border-gray-100 px-4 py-4 dark:border-slate-700">
                    <div class="mb-2 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-kbb-700 text-sm font-semibold text-white">
                            {{ initial }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium dark:text-gray-100">{{ displayName }}</p>
                            <p class="truncate text-xs text-gray-400 dark:text-gray-500">{{ user?.email }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="w-full rounded px-2 py-1.5 text-left text-sm text-red-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10"
                        @click="logout"
                    >
                        Logout
                    </button>
                </div>
            </aside>

            <main class="min-w-0 flex-1 overflow-y-auto">
                <div class="sticky top-0 z-30 flex items-center gap-4 border-b border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-slate-700 dark:bg-slate-900/85 sm:px-6">
                    <button
                        type="button"
                        class="text-gray-400 transition hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 lg:hidden"
                        @click="mobileOpen = true"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="min-w-0 flex-1 truncate text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <slot name="title">Dashboard</slot>
                    </h1>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:text-gray-300 dark:hover:bg-slate-800"
                        title="Ubah tema"
                        @click="toggleTheme"
                    >
                        <svg v-if="dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <NotificationBell v-if="user" />
                </div>

                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">
                    <slot />
                </div>
            </main>
        </div>

        <div
            v-if="toast"
            class="fixed bottom-5 right-5 z-[60] flex max-w-sm items-start gap-3 rounded-xl border px-4 py-3 shadow-lg transition"
            :class="toast.type === 'error'
                ? 'border-red-200 bg-white text-red-700 dark:border-red-500/30 dark:bg-slate-800 dark:text-red-400'
                : 'border-emerald-200 bg-white text-emerald-700 dark:border-emerald-500/30 dark:bg-slate-800 dark:text-emerald-400'"
        >
            <span class="text-sm">{{ toast.message }}</span>
            <button type="button" class="ml-2 text-gray-400 hover:text-gray-600" @click="toast = null">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>
