<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalUsers: 0,
            activeUsers: 0,
            totalReferrals: 0,
            pendingReferrals: 0,
        })
    },
    topReferrers: {
        type: Array,
        default: () => []
    },
    users: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
        }),
    },
});

const searchQuery = ref('');
const isSearching = ref(false);

// Initialize search query from URL
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    searchQuery.value = urlParams.get('search') || '';
    
    console.log('Initial search query:', searchQuery.value);
    console.log('Users data:', props.users);
});

// Get the active tab from URL or default to 'overview'
const activeTab = ref(new URLSearchParams(window.location.search).get('tab') || 'overview');

// Watch for tab changes and update URL
watch(activeTab, (newTab) => {
    const url = new URL(window.location);
    url.searchParams.set('tab', newTab);
    window.history.pushState({}, '', url);
});

// Handle user deletion with confirmation
const confirmDelete = (userId) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('admin.users.destroy', userId), {
            onSuccess: () => {
                // Refresh the users list after deletion
                router.visit(window.route('admin.dashboard', { tab: 'users' }), {
                    only: ['users'],
                    preserveState: true,
                    preserveScroll: true,
                });
            },
        });
    }
};

const tabs = [
    { name: 'Overview', id: 'overview', icon: 'grid' },
    { name: 'Users', id: 'users', icon: 'users' },
];

const currentPage = ref(1);
const itemsPerPage = 5;

// Recent Users Pagination - Now handled by backend

// Debounce function
const debounce = (func, delay) => {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
};

// Handle search with Laravel's pagination
const performSearch = () => {
    if (isSearching.value) return;
    
    isSearching.value = true;
    const query = searchQuery.value.trim();
    
    // Get current query parameters
    const currentQuery = { ...route().query };
    
    // Update search parameter
    if (query) {
        currentQuery.search = query;
    } else {
        delete currentQuery.search;
    }
    
    // Reset to first page when searching
    delete currentQuery.page;
    
    // Use Inertia's router to update the URL and fetch new data
    router.get(window.route('admin.dashboard'), currentQuery, {
        preserveState: true,
        replace: true,
        only: ['users'],
        onSuccess: () => {
            console.log('Search successful');
        },
        onError: (errors) => {
            console.error('Search error:', errors);
        },
        onFinish: () => {
            isSearching.value = false;
        }
    });
};

// Debounced search
const debouncedSearch = debounce(performSearch, 500);

// Watch for changes to search query
watch(searchQuery, (newValue, oldValue) => {
    if (newValue !== oldValue) {
        debouncedSearch();
    }
});

// Clear search query and reset results
const clearSearch = () => {
    searchQuery.value = '';
    // The watcher will automatically trigger performSearch
};

// Handle Enter key press
const onSearchKeyup = (event) => {
    if (event.key === 'Enter') {
        performSearch();
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                activeTab === tab.id
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                            ]"
                            :aria-current="activeTab === tab.id ? 'page' : undefined"
                        >
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Overview Tab -->
                        <div v-if="activeTab === 'overview'" class="space-y-6">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                                <!-- Total Users -->
                                <div class="bg-white overflow-hidden shadow rounded-lg">
                                    <div class="px-4 py-5 sm:p-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                                                    <dd class="flex items-baseline">
                                                        <div class="text-2xl font-semibold text-gray-900">{{ stats.totalUsers || 0 }}</div>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Referrals -->
                                <div class="bg-white overflow-hidden shadow rounded-lg">
                                    <div class="px-4 py-5 sm:p-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Referrals</dt>
                                                    <dd class="flex items-baseline">
                                                        <div class="text-2xl font-semibold text-gray-900">{{ stats.totalReferrals || 0 }}</div>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Users Tab -->
                        <div v-if="activeTab === 'users'" class="space-y-4">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                                <div class="w-full sm:w-1/3">
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <input 
                                            type="text" 
                                            v-model="searchQuery"
                                            @input="(e) => searchQuery = e.target.value"
                                            @keyup.enter="onSearchKeyup"
                                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" 
                                            placeholder="Search users..."
                                            :disabled="isSearching"
                                        >
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 space-x-1">
                                            <!-- Clear button (shown when there's text) -->
                                            <button v-if="searchQuery" @click="clearSearch" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                            
                                            <!-- Search button -->
                                            <button @click="performSearch" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <Link :href="route('admin.users.create')" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Add New User
                                </Link>
                            </div>

                            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referral Code</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Referrals</th>
                                                <th scope="col" class="relative px-6 py-3">
                                                    <span class="sr-only">Actions</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="user in users.data" :key="user.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                            <span class="text-indigo-800 font-medium">{{ user.name.charAt(0) }}</span>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                                            <div class="text-sm text-gray-500">{{ user.role }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ user.email }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                                    {{ user.referral_code || '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span :class="{
                                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                        'bg-green-100 text-green-800': user.role === 'Admin',
                                                        'bg-blue-100 text-blue-800': user.role === 'User'
                                                    }">
                                                        {{ user.role }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ user.referral_count || 0 }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                                                    <button @click="confirmDelete(user.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <!-- Pagination -->
                                    <div v-if="users.links && users.links.length > 0" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                        <div class="flex-1 flex justify-between sm:hidden">
                                            <Link 
                                                v-if="users.prev_page_url" 
                                                :href="users.prev_page_url + '&tab=users'" 
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                                preserve-scroll
                                                :preserve-state="true"
                                            >
                                                Previous
                                            </Link>
                                            <span v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed">
                                                Previous
                                            </span>
                                            <Link 
                                                v-if="users.next_page_url"
                                                :href="users.next_page_url + '&tab=users'" 
                                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                                preserve-scroll
                                                :preserve-state="true"
                                            >
                                                Next
                                            </Link>
                                            <span v-else class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed">
                                                Next
                                            </span>
                                        </div>
                                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm text-gray-700">
                                                    Showing <span class="font-medium">{{ users.from || 0 }}</span>
                                                    to <span class="font-medium">{{ users.to || 0 }}</span>
                                                    of <span class="font-medium">{{ users.total || 0 }}</span> results
                                                </p>
                                            </div>
                                            <div>
                                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                                    <template v-for="(link, key) in users.links" :key="key">
                                                        <Link 
                                                            v-if="!link.url || link.url === 'null'"
                                                            v-html="link.label"
                                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed"
                                                            disabled
                                                        />
                                                        <Link 
                                                            v-else
                                                            :href="link.url + (link.url.includes('?') ? '&' : '?') + 'tab=users'"
                                                            v-html="link.label"
                                                            :class="{
                                                                'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active
                                                            }"
                                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                                            preserve-scroll
                                                            :preserve-state="true"
                                                        />
                                                    </template>
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
