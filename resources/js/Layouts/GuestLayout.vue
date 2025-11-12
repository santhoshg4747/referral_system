<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const isProductsPage = computed(() => page.url.startsWith('/products'));
</script>

<template>
    <div class="min-h-screen flex flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0">
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div :class="[
            'mt-6 w-full px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg',
            !isProductsPage && 'sm:max-w-md'
        ]">
            <slot />
        </div>
    </div>
</template>
