<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from './AdminLayout.vue';
import AppLayout from './AppLayout.vue';

const page = usePage();
const isSuperAdmin = computed(() => Boolean(page.props.auth?.user?.is_super_admin));
const layout = computed(() => (isSuperAdmin.value ? AdminLayout : AppLayout));
</script>

<template>
    <component :is="layout">
        <template v-if="$slots.title" #title>
            <slot name="title" />
        </template>

        <slot />
    </component>
</template>
