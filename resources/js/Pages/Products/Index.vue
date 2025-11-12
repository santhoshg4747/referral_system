<script setup>
import { ref, onMounted, onBeforeUnmount, shallowRef } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const isAdmin = page.props.auth?.user?.is_admin;

// Debounce function to limit how often a function can run
const debounce = (func, delay) => {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
};

const products = shallowRef([]);
const isLoading = ref(true);
const isCheckingAuth = ref(true);
const isFetchingMore = ref(false);
const error = ref(null);
const currentPage = ref(1);
const totalProducts = ref(0);
const hasMore = ref(true);
const observer = ref(null);
const productsPerPage = 5;
const allProducts = shallowRef([]);
const loadingStates = ref({});

// Format category name to be more readable
const formatCategory = (category) => {
    return category
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

// Add product to cart
const addToCart = (product) => {
    // This is a placeholder - you can implement your cart logic here
    console.log('Added to cart:', product.title);
    // Example: cartStore.addToCart(product);
};

// Preload images for smoother loading
const preloadImage = (url) => {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.src = url;
        img.onload = resolve;
        img.onerror = reject;
    });
};

const fetchProducts = async (page = 1) => {
    try {
        if (page === 1) {
            isLoading.value = true;
            products.value = [];
            hasMore.value = true;
        } else {
            isFetchingMore.value = true;
        }
        
        error.value = null;
        
        // Only fetch all products once
        if (allProducts.value.length === 0) {
            const response = await fetch('https://fakestoreapi.com/products');
            if (!response.ok) {
                throw new Error('Failed to fetch products');
            }
            allProducts.value = await response.json();
        }
        
        // Simulate pagination on the client side
        const startIndex = (page - 1) * productsPerPage;
        const endIndex = startIndex + productsPerPage;
        const newProducts = allProducts.value.slice(startIndex, endIndex);
        
        // Preload images before updating the UI
        await Promise.all(newProducts.map(product => 
            preloadImage(product.image).catch(console.error)
        ));
        
        if (newProducts.length === 0) {
            hasMore.value = false;
        } else {
            if (newProducts.length < productsPerPage) {
                hasMore.value = false;
            }
            
            // Update products in a single batch
            products.value = [...products.value, ...newProducts];
            currentPage.value = page;
            
            // Update loading states for new products
            newProducts.forEach(product => {
                loadingStates.value[product.id] = false;
            });
        }
        
    } catch (err) {
        console.error('Error fetching products:', err);
        error.value = 'Failed to load products. Please try again later.';
    } finally {
        isLoading.value = false;
        isFetchingMore.value = false;
    }
};

const handleIntersection = debounce((entries) => {
    const target = entries[0];
    
    if (target.isIntersecting && !isLoading.value && !isFetchingMore.value && hasMore.value) {
        fetchProducts(currentPage.value + 1);
    }
}, 100);

onMounted(() => {
    fetchProducts(1);
    
    // Set up intersection observer for infinite scroll
    const options = {
        root: null,
        rootMargin: '200px 0px', // Start loading when within 200px of viewport
        threshold: 0.01
    };
    
    // Initialize the observer
    observer.value = new IntersectionObserver(handleIntersection, options);
    
    // Setup with retry logic
    const setupObserver = () => {
        const loadingIndicator = document.getElementById('loading-indicator');
        
        if (loadingIndicator) {
            // Disconnect any existing observer
            if (observer.value) {
                observer.value.disconnect();
            }
            
            // Create new observer
            observer.value = new IntersectionObserver(handleIntersection, options);
            observer.value.observe(loadingIndicator);
        } else {
            // Retry after a short delay if element not found yet
            setTimeout(setupObserver, 100);
        }
    };
    
    // Initial setup
    setupObserver();
    
    // Re-setup observer when products change
    return () => {
        if (observer.value) {
            observer.value.disconnect();
        }
    };
});

onBeforeUnmount(() => {
    if (observer.value) {
        observer.value.disconnect();
    }
});
</script>

<template>
    <Head title="Our Products" />

    <AuthenticatedLayout>
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                        Our Products
                    </h1>
                    <p class="mt-6 text-xl text-indigo-100 max-w-3xl mx-auto">
                        Discover our high-quality products at competitive prices. Free shipping on all orders over $50.
                    </p>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div v-if="!isCheckingAuth" class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Loading State -->
                <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <div v-for="i in 8" :key="i" class="animate-pulse">
                        <div class="bg-gray-200 rounded-2xl h-72"></div>
                        <div class="mt-5 space-y-3">
                            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2 mt-4"></div>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="bg-red-50 border-l-4 border-red-400 p-4 mb-8 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                {{ error }}
                                <button @click="fetchProducts(1)" class="font-medium text-red-700 hover:text-red-600 underline ml-2">
                                    Try again
                                </button>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div v-else-if="products.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                        <div v-for="product in products" :key="product.id" class="group">
                            <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 h-full flex flex-col border border-gray-100">
                                <!-- Product Image -->
                                <div class="relative pt-[120%] bg-gray-50">
                                    <div v-if="loadingStates[product.id]" class="absolute inset-0 flex items-center justify-center">
                                        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                                    </div>
                                    <img 
                                        :src="product.image" 
                                        :alt="product.title"
                                        class="absolute inset-0 w-full h-full object-contain p-6 transition-opacity duration-300"
                                        :class="{ 'opacity-0': loadingStates[product.id] }"
                                        @load="loadingStates[product.id] = false"
                                    />
                                    <!-- Category Badge -->
                                    <div class="absolute top-4 right-4">
                                        <span class="px-3 py-1.5 bg-white/90 backdrop-blur-sm text-xs font-medium rounded-full shadow-sm border border-gray-200">
                                            {{ formatCategory(product.category) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="p-5 flex flex-col flex-grow space-y-3">
                                    <h3 class="text-gray-900 font-medium text-base leading-tight line-clamp-2" :title="product.title">
                                        {{ product.title }}
                                    </h3>
                                    
                                    <div class="flex items-center">
                                        <!-- Star Rating -->
                                        <div class="flex items-center text-amber-400">
                                            <span v-for="i in 5" :key="i" class="text-sm" :class="{ 'text-gray-200': i > Math.round(product.rating.rate) }">
                                                ★
                                            </span>
                                            <span class="ml-2 text-xs text-gray-500">
                                                ({{ product.rating.count }})
                                            </span>
                                        </div>
                                    </div>

                                    <p class="text-sm text-gray-500 line-clamp-2 flex-grow">
                                        {{ product.description }}
                                    </p>

                                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                                        <span class="text-lg font-bold text-gray-900">${{ product.price.toFixed(2) }}</span>
                                        <button 
                                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 flex items-center space-x-1.5"
                                            @click="addToCart(product)"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            <span>Add</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading More Indicator -->
                    <div class="mt-12 text-center">
                        <div v-if="isFetchingMore" class="inline-flex items-center px-6 py-2 text-sm font-medium text-gray-600">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading more products...
                        </div>
                        <div v-else-if="!hasMore" class="text-sm text-gray-500 py-4">
                            You've reached the end of our collection
                        </div>
                        <div id="loading-indicator" class="h-1 w-full"></div>
                    </div>
                </div>
                
                <!-- No products message -->
                <div v-else class="text-center py-16">
                    <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No products found</h3>
                    <p class="mt-2 text-sm text-gray-500">We couldn't find any products matching your criteria.</p>
                    <div class="mt-6">
                        <button @click="fetchProducts(1)" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Refresh products
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
