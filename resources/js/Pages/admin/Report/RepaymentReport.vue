<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    users: Array,
    report: Array,
    selectedUserId: [String, Number],
});

const form = useForm({
    user_id: props.selectedUserId ?? '',
});

function filterReport() {
    router.get(route('admin.report.repayment'), {
        user_id: form.user_id || '',
    }, {
        preserveScroll: true,
    });
}

// ✅ Total repayment across all rows
const totalRepaid = computed(() => {
    return props.report.reduce((sum, item) => sum + Number(item.amount), 0);
});

// ✅ Latest balance per borrow ID (avoid counting same borrow_id multiple times)
const totalBalance = computed(() => {
    const borrowMap = new Map();
    props.report.forEach(item => {
        borrowMap.set(item.borrow_id, item.balance); // Only the latest balance per borrow
    });
    return Array.from(borrowMap.values()).reduce((sum, bal) => sum + Number(bal), 0);
});

// Calculate additional metrics
const totalBorrowedAmount = computed(() => {
    return totalRepaid.value + totalBalance.value;
});

const repaymentRate = computed(() => {
    if (totalBorrowedAmount.value === 0) return 0;
    return ((totalRepaid.value / totalBorrowedAmount.value) * 100).toFixed(1);
});

const uniqueBorrows = computed(() => {
    const uniqueIds = new Set(props.report.map(item => item.borrow_id));
    return uniqueIds.size;
});

// Animation and interaction states
const hoveredRow = ref(null);
</script>

<template>

    <Head title="Repayment Report" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                        Repayment Analytics
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Monitor repayment performance and outstanding
                        balances</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Live Data</span>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Modern Stats Dashboard -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Repaid -->
                    <div
                        class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Total Repaid</p>
                                <p class="text-3xl font-bold mt-1">{{ new Intl.NumberFormat().format(totalRepaid) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Balance -->
                    <div
                        class="bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm font-medium">Outstanding Balance</p>
                                <p class="text-3xl font-bold mt-1">{{ new Intl.NumberFormat().format(totalBalance) }}
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.882 16.5c-.77.833.192 2.5 1.732 2.5z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Repayment Rate -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700 hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Repayment Rate</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ repaymentRate }}%
                                </p>
                            </div>
                            <div
                                class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Unique Borrows -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-200 dark:border-gray-700 hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Loans</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ uniqueBorrows }}</p>
                            </div>
                            <div
                                class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
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
                                <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Repayment Report</h3>
                            </div>

                            <div class="flex items-center space-x-4">
                                <!-- Modern Filter Dropdown -->
                                <div class="relative">
                                    <select v-model="form.user_id" @change="filterReport"
                                        class="appearance-none bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2 pr-8 text-sm font-medium text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
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
                                <a :href="route('admin.report.repayment.export', { user_id: form.user_id })"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-green-600 text-white text-sm font-medium rounded-xl hover:from-emerald-700 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
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
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
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
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No repayment records
                                found</h3>
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
                                                User</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Loan ID</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Repaid Amount</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Currency</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Rate</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 dark:text-gray-200 uppercase tracking-wider">
                                                Timeline</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="(item, index) in props.report" :key="item.repayment_id"
                                            @mouseenter="hoveredRow = index" @mouseleave="hoveredRow = null"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
                                            :class="{ 'bg-emerald-50 dark:bg-emerald-900/20': hoveredRow === index }">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ index + 1 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xs font-bold mr-3">
                                                        {{ item.user_name.charAt(0).toUpperCase() }}
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                        item.user_name }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 bg-emerald-500 rounded-full mr-3"></div>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                        item.borrow_id }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                                    {{ new Intl.NumberFormat().format(item.amount) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200">
                                                    {{ item.currency }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-200">
                                                    {{ item.rate }}%
                                                </span>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                <div class="flex flex-col space-y-1">
                                                    <div
                                                        class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                            </path>
                                                        </svg>
                                                        Borrowed: {{ new Date(item.borrowed_date).toLocaleDateString()
                                                        }}
                                                    </div>
                                                    <div
                                                        class="flex items-center text-xs text-emerald-600 dark:text-emerald-400">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                                            </path>
                                                        </svg>
                                                        Repaid: {{ new Date(item.pay_date).toLocaleDateString() }}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Modern Summary Footer -->
                            <div
                                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-center space-x-8">
                                        <div class="text-center">
                                            <div
                                                class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                                Total Repaid</div>
                                            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ new Intl.NumberFormat().format(totalRepaid) }}
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div
                                                class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                                Outstanding</div>
                                            <div class="text-lg font-bold text-red-600 dark:text-red-400">
                                                {{ new Intl.NumberFormat().format(totalBalance) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-emerald-500 to-green-600 rounded-full transition-all duration-300"
                                                :style="{ width: `${repaymentRate}%` }"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{
                                            repaymentRate }}%
                                            repaid</span>
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

<style scoped>
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