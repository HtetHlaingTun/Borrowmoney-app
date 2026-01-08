<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch, onUnmounted, computed } from 'vue';

const props = defineProps({
    users: Array,
    report: Array,
    currencies: Array,
    selectedUserId: [String, Number],
});

const page = usePage();

const form = useForm({
    user_id: props.selectedUserId ?? '',
});

function filterReport() {
    router.get(route('admin.report.interest'), {
        user_id: form.user_id || '',
    }, {
        preserveScroll: true,
    });
}

const totalInterest = computed(() => {
    const sum = props.report.reduce((sum, item) => sum + Number(item.total_interest), 0);
    return new Intl.NumberFormat(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(sum);
});

const showAlert = ref(false);
const alertMessage = ref('');
let alertTimeout = null;

watch(() => page.props.flash?.success, (newVal) => {
    if (newVal) {
        alertMessage.value = newVal;
        showAlert.value = true;
        if (alertTimeout) clearTimeout(alertTimeout);
        alertTimeout = setTimeout(() => showAlert.value = false, 4000);
    }
});

onUnmounted(() => {
    if (alertTimeout) clearTimeout(alertTimeout);
});

// Animation and interaction states
const isLoading = ref(false);
const hoveredRow = ref(null);
</script>

<template>

    <Head title="Interest Report" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Interest Analytics
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Track and analyze interest calculations</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Live Data</span>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Alert with modern design -->
                <Transition name="slide-down">
                    <div v-if="showAlert"
                        class="mb-6 relative overflow-hidden rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-6 border border-green-200 dark:border-green-800 shadow-lg backdrop-blur-sm">
                        <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5"></div>
                        <div class="relative flex items-center">
                            <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-green-800 dark:text-green-200 font-medium">{{ alertMessage }}</span>
                        </div>
                    </div>
                </Transition>

                <!-- Modern Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700 hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Records</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ props.report.length
                                }}</p>
                            </div>
                            <div
                                class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700 hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Users</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ props.users.length
                                }}</p>
                            </div>
                            <div
                                class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-br from-purple-600 to-blue-600 rounded-2xl p-6 shadow-xl text-white hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">Total Interest</p>
                                <p class="text-3xl font-bold mt-1">{{ totalInterest }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Card -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Header with Controls -->
                    <div
                        class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Interest Report</h3>
                            </div>

                            <div class="flex items-center space-x-4">
                                <!-- Modern Filter Dropdown -->
                                <div class="relative">
                                    <select v-model="form.user_id" @change="filterReport"
                                        class="appearance-none bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2 pr-8 text-sm font-medium text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                        <option value="">All Users</option>
                                        <option v-for="user in props.users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Modern Download Button -->
                                <a :href="route('admin.interest.report.download', { user_id: form.user_id })"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Export Excel
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Content Area -->
                    <div class="p-6">
                        <!-- Filter Info -->
                        <div class="mb-6">
                            <div
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z">
                                    </path>
                                </svg>
                                Showing: <strong class="ml-1">{{props.users.find(u => u.id == form.user_id)?.name ??
                                    'All Users'}}</strong>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="props.report.length === 0" class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No interest records found
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">Try adjusting your filter or check back later.
                            </p>
                        </div>

                        <!-- Modern Table -->
                        <div v-else class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                            <div class="overflow-x-auto">
                                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead
                                        class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
                                        <tr>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                #</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Borrow ID</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Amount</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Rate</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Period</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Currency</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Total Interest</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="(item, index) in props.report" :key="item.borrow_id"
                                            @mouseenter="hoveredRow = index" @mouseleave="hoveredRow = null"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
                                            :class="{ 'bg-blue-50 dark:bg-blue-900/20': hoveredRow === index }">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ index + 1 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                        item.borrow_id }}</span>
                                                </div>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                                {{ new Intl.NumberFormat().format(item.amount) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                                                    {{ item.rate }}%
                                                </span>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ item.start_date }}</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">to {{
                                                        item.end_date
                                                    }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200">
                                                    {{props.currencies.find(c => c.id === item.currency_id)?.symbol ||
                                                        'N/A'}}
                                                </span>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                                {{ new Intl.NumberFormat().format(item.total_interest) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease;
}

.slide-down-enter-from {
    transform: translateY(-20px);
    opacity: 0;
}

.slide-down-leave-to {
    transform: translateY(-20px);
    opacity: 0;
}

/* Custom scrollbar */
.overflow-x-auto::-webkit-scrollbar {
    height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Dark mode scrollbar */
.dark .overflow-x-auto::-webkit-scrollbar-track {
    background: #374151;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>