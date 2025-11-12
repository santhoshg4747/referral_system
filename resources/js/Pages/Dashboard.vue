<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const referralLink = ref('');
const showCopied = ref(false);

function generateReferralLink() {
    return `${window.location.origin}/register?ref=${user.value.referral_code}`;
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    showCopied.value = true;
    setTimeout(() => {
        showCopied.value = false;
    }, 2000);
}

onMounted(() => {
    referralLink.value = generateReferralLink();
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">
                Welcome back, {{ user.name }}! 👋
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-5 mt-6 sm:grid-cols-1">
                    <!-- Total Referrals Card -->
                    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                        <div class="flex">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Referrals</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ user.referral_count || 0 }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referral Section -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Your Referral Information</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Share your referral link and earn rewards</p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                        <!-- Referral Code -->
                        <div class="mb-6">
                            <label for="referral-code" class="block text-sm font-medium text-gray-700 mb-1">Your Referral Code</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input
                                    type="text"
                                    name="referral-code"
                                    id="referral-code"
                                    :value="user.referral_code"
                                    class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300"
                                    readonly
                                >
                                <button
                                    @click="copyToClipboard(user.referral_code)"
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 bg-gray-50 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 rounded-r-md"
                                >
                                    <svg v-if="!showCopied" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z" />
                                        <path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM15 11h2a1 1 0 100-2h-2v2z" />
                                    </svg>
                                    <span>{{ showCopied ? 'Copied!' : 'Copy' }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Referral Link -->
                        <div>
                            <label for="referral-link" class="block text-sm font-medium text-gray-700 mb-1">Your Referral Link</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input
                                    type="text"
                                    name="referral-link"
                                    id="referral-link"
                                    :value="referralLink"
                                    class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300"
                                    readonly
                                >
                                <button
                                    @click="copyToClipboard(referralLink)"
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 rounded-r-md"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z" />
                                        <path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM15 11h2a1 1 0 100-2h-2v2z" />
                                    </svg>
                                    <span>Copy Link</span>
                                </button>
                            </div>
                        </div>

                        
                    </div>
                </div>


            </div>
        </div>
    </AuthenticatedLayout>
</template>
