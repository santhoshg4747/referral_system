<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalUsers: 0,
            totalReferrals: 0
        })
    },
    recentUsers: {
        type: Array,
        default: () => []
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
            total: 0,
            from: 0,
            to: 0,
            current_page: 1,
            last_page: 1,
            per_page: 10,
            prev_page_url: null,
            next_page_url: null,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: ''
        })
    },
});

const activeTab = ref('overview');
const searchQuery = ref(props.filters.search || '');
const isSearching = ref(false);
const searchInput = ref(null);

// Debug: Log initial state
console.log('=== INITIAL STATE ===');
console.log('searchQuery:', searchQuery.value);
console.log('props.filters:', props.filters);
console.log('props.users:', props.users);
console.log('route().current():', route().current());
console.log('route().query:', route().query);

// Debug: Log initial props and state
console.log('Initial props:', props);
console.log('Initial searchQuery:', searchQuery.value);

// Debug: Log route information
console.log('Current route:', route().current());
console.log('Current params:', route().params);
console.log('Current query:', route().query);
console.log('Initial search query:', searchQuery.value);

const tabs = [
    { name: 'Overview', id: 'overview', icon: 'grid' },
    { name: 'Users', id: 'users', icon: 'users' },
];

// Debounce function to limit how often search is triggered
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
    console.log('=== PERFORMING SEARCH ===');
    
    if (isSearching.value) {
        console.log('Search already in progress, skipping');
        return;
    }
    
    isSearching.value = true;
    const query = searchQuery.value.trim();
    console.log('Search query:', query);
    
    // Get current query parameters
    const currentQuery = { ...route().query };
    
    // Update search parameter
    if (query) {
        currentQuery.search = query;
    } else {
        delete currentQuery.search;
    }
    
    // Always reset to first page when searching
    delete currentQuery.page;
    
    console.log('Navigation query:', currentQuery);
    
    // Get the target route
    const targetRoute = route('admin.dashboard');
    console.log('Target route:', targetRoute);
    
    // Use Inertia's router to update the URL and fetch new data
    router.get(targetRoute, currentQuery, {
        preserveState: true,
        replace: true,
        only: ['users'],
        onSuccess: (page) => {
            console.log('=== SEARCH SUCCESS ===');
            console.log('Received users:', page.props.users);
            console.log('Received filters:', page.props.filters);
        },
        onError: (errors) => {
            console.error('=== SEARCH ERROR ===', errors);
        },
        onFinish: () => {
            console.log('=== SEARCH FINISHED ===');
            isSearching.value = false;
        }
    });
};

// Debounced search function
const debouncedSearch = debounce(performSearch, 500);

// Watch for changes to search query
watch(searchQuery, (newValue, oldValue) => {
    console.log('=== SEARCH QUERY CHANGED ===');
    console.log('New value:', newValue);
    console.log('Old value:', oldValue);
    
    // Always trigger search when query changes
    debouncedSearch();
});

// Debug: Log when component is mounted
onMounted(() => {
    console.log('=== COMPONENT MOUNTED ===');
    console.log('Component file: Index.vue');
    console.log('Current route:', route().current());
    console.log('Route params:', route().params);
    console.log('Route query:', route().query);
    
    // Log the search input element
    console.log('Search input ref:', searchInput.value);
    
    // Test route generation
    try {
        const testRoute = route('admin.dashboard');
        console.log('Route generation test:', testRoute);
    } catch (error) {
        console.error('Error generating route:', error);
    }
    
    // Initial search if there's a search query
    if (searchQuery.value) {
        console.log('Initial search query found, performing search...');
        performSearch();
    }
});

// Watch for URL changes to update search query
watch(() => route().query, (newQuery) => {
    console.log('=== ROUTE QUERY CHANGED ===');
    console.log('New query:', newQuery);
    
    if (newQuery.search !== undefined && newQuery.search !== searchQuery.value) {
        console.log('Updating searchQuery from route:', newQuery.search);
        searchQuery.value = newQuery.search;
    }
}, { immediate: true });
watch(() => route().query, (newQuery) => {
    console.log('Route query changed:', newQuery);
    if (newQuery.search !== undefined && newQuery.search !== searchQuery.value) {
        searchQuery.value = newQuery.search;
    }
}, { immediate: true });
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
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
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Overview Tab -->
                        <div v-if="activeTab === 'overview'" class="space-y-6">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-2">
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
                                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
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

                            <!-- Recent Users -->
                            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Users</h3>
                                        <p class="mt-1 text-sm text-gray-500">Showing {{ recentUsers.from || 0 }} to {{ recentUsers.to || 0 }} of {{ recentUsers.total || 0 }} users</p>
                                    </div>
                                    <Link :href="route('admin.users.index')" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View all</Link>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referrals</th>
                                                <th scope="col" class="relative px-6 py-3">
                                                    <span class="sr-only">Actions</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="user in recentUsers.data" :key="user.id">
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
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ user.email }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ user.referral_count || 0 }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                                                </td>
                                            </tr>
                                            <tr v-if="!recentUsers.data || recentUsers.data.length === 0">
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                                    No users found
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!-- Pagination -->
                                    <div v-if="recentUsers.links && recentUsers.links.length > 3" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                        <div class="flex-1 flex justify-between sm:hidden">
                                            <Link 
                                                v-if="recentUsers.prev_page_url" 
                                                :href="recentUsers.prev_page_url" 
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                            >
                                                Previous
                                            </Link>
                                            <Link 
                                                v-if="recentUsers.next_page_url" 
                                                :href="recentUsers.next_page_url" 
                                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                            >
                                                Next
                                            </Link>
                                        </div>
                                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm text-gray-700">
                                                    Showing <span class="font-medium">{{ recentUsers.from || 0 }}</span> to <span class="font-medium">{{ recentUsers.to || 0 }}</span> of 
                                                    <span class="font-medium">{{ recentUsers.total || 0 }}</span> results
                                                </p>
                                            </div>
                                            <div>
                                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                                    <Link 
                                                        v-for="(link, key) in recentUsers.links" 
                                                        :key="key"
                                                        :href="link.url || '#'"
                                                        v-html="link.label"
                                                        :class="[
                                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                            link.active 
                                                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
                                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                            link.url === null ? 'opacity-50 cursor-not-allowed' : ''
                                                        ]"
                                                        :aria-current="link.active ? 'page' : undefined"
                                                    ></Link>
                                                </nav>
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
                                            @keyup.enter="performSearch"
                                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" 
                                            placeholder="Search users..."
                                            :disabled="isSearching"
                                            ref="searchInput"
                                        >
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <button type="button" @click="performSearch" class="text-gray-400 hover:text-gray-500">
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
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referrals</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
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
                                                            <div class="text-sm text-gray-500">{{ user.is_admin ? 'Admin' : 'User' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ user.email }}</div>
                                                    <div class="text-sm text-gray-500">{{ user.referral_code }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ user.referral_count || 0 }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                                                    <button @click="confirmDelete(user.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <!-- Pagination -->
                                    <div v-if="users.links && users.links.length > 3" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                        <div class="flex-1 flex justify-between sm:hidden">
                                            <Link 
                                                v-if="users.prev_page_url" 
                                                :href="users.prev_page_url" 
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                                preserve-scroll
                                            >
                                                Previous
                                            </Link>
                                            <span v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed">
                                                Previous
                                            </span>
                                            <Link 
                                                v-if="users.next_page_url"
                                                :href="users.next_page_url" 
                                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                                preserve-scroll
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
                                                            :href="link.url"
                                                            v-html="link.label"
                                                            :class="{
                                                                'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active
                                                            }"
                                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                                            preserve-scroll
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
    </AdminLayout>
</template>
