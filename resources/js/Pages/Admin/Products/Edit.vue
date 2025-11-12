<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductForm from '@/Components/ProductForm.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="`Edit Product: ${product.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Product: {{ product.title }}
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <ProductForm :product="product" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
