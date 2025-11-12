<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        default: () => ({
            user: null
        })
    }
});

const referralLink = ref('');
const showCopied = ref(false);

onMounted(() => {
    const baseUrl = window.location.origin;
    const refCode = props.auth?.user?.referral_code || 'YOUR_CODE';
    referralLink.value = `${baseUrl}/register?ref=${refCode}`;
});

function copyToClipboard() {
    if (!referralLink.value) return;
    
    navigator.clipboard.writeText(referralLink.value).then(() => {
        showCopied.value = true;
        setTimeout(() => {
            showCopied.value = false;
        }, 2000);
    });
}
</script>

<template>
    <Head title="Welcome to Our Referral Program" />
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-gray-900 dark:to-gray-800 text-gray-800 dark:text-gray-200">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-900 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link href="/" class="flex-shrink-0 flex items-center">
                            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">ReferralSystem</span>
                        </Link>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
                        <Link 
                            v-if="props.auth?.user"
                            :href="route('dashboard')" 
                            class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-white transition-colors"
                        >
                            Dashboard
                        </Link>
                        <Link 
                            v-else
                            :href="route('login')" 
                            class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-white transition-colors"
                        >
                            Sign In
                        </Link>
                        <Link 
                            v-if="!props.auth?.user"
                            :href="route('register')" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Hero Section -->
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                    Earn Rewards with Our
                    <span class="text-indigo-600 dark:text-indigo-400">Referral Program</span>
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto mb-10">
                    Invite your friends and earn amazing rewards when they sign up using your unique referral link.
                </p>
                
                <!-- Referral Section -->
                <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-12">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Your Referral Link</h2>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-grow">
                            <input 
                                type="text" 
                                :value="referralLink" 
                                readonly 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                            <div v-if="showCopied" class="absolute -top-2 right-3 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                Copied!
                            </div>
                        </div>
                        <button 
                            @click="copyToClipboard"
                            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 flex items-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z" />
                                <path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM15 11h2a1 1 0 100-2h-2v2z" />
                            </svg>
                            Copy Link
                        </button>
                    </div>
                </div>

                <!-- How It Works Section -->
                <div class="mt-16 text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">How It Works</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto mb-12">
                        Start earning rewards in just a few simple steps
                    </p>

                    <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                        <!-- Step 1 -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
                            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-indigo-600 dark:text-indigo-300 text-xl font-bold">1</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Share Your Link</h3>
                            <p class="text-gray-600 dark:text-gray-300">Copy and share your unique referral link with friends and family.</p>
                        </div>

                        <!-- Step 2 -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
                            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-indigo-600 dark:text-indigo-300 text-xl font-bold">2</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">They Sign Up</h3>
                            <p class="text-gray-600 dark:text-gray-300">Your friends sign up using your referral link.</p>
                        </div>

                        <!-- Step 3 -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
                            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-indigo-600 dark:text-indigo-300 text-xl font-bold">3</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Earn Rewards</h3>
                            <p class="text-gray-600 dark:text-gray-300">Get rewarded when they complete their first action!</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="col-span-1">
                            <h3 class="text-lg font-semibold mb-4">ReferralProgram</h3>
                            <p class="text-gray-400 text-sm">
                                Earn rewards by sharing our platform with your friends and family.
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Quick Links</h4>
                            <ul class="space-y-2">
                                <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
                                <li><a href="#" class="text-gray-400 hover:text-white transition-colors">How It Works</a></li>
                                <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Rewards</a></li>
                                <li><a href="#" class="text-gray-400 hover:text-white transition-colors">FAQs</a></li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Contact Us</h4>
                            <p class="text-gray-400 text-sm">
                                Have questions? Reach out to our support team.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-gray-800 pt-8 text-center">
                        <p class="text-sm text-gray-400">
                            &copy; {{ new Date().getFullYear() }} ReferralProgram. All rights reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    
</template>
