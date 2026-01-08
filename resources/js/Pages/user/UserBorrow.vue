<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    borrows: Array,
    currentPage: Number,
    lastPage: Number,
    filters: Object,
});

const search = ref(props.filters.search || '');

function applySearch() {
    const query = new URLSearchParams();
    if (search.value) query.append('search', search.value);
    window.location.href = `?${query.toString()}`;
}

const formatNumber = (n) => new Intl.NumberFormat().format(n);

const getStatusConfig = (status) => {
    const configs = {
        Pending: {
            bg: "bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20",
            text: "text-amber-700 dark:text-amber-300",
            border: "border-amber-200 dark:border-amber-700",
            icon: "⏳"
        },
        Paid: {
            bg: "bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20",
            text: "text-emerald-700 dark:text-emerald-300",
            border: "border-emerald-200 dark:border-emerald-700",
            icon: "✅"
        },
        Overdue: {
            bg: "bg-gradient-to-r from-rose-50 to-red-50 dark:from-rose-900/20 dark:to-red-900/20",
            text: "text-rose-700 dark:text-rose-300",
            border: "border-rose-200 dark:border-rose-700",
            icon: "⚠️"
        }
    };
    return configs[status] || configs.Pending;
};

const totalStats = computed(() => {
    const total = props.borrows.reduce((acc, borrow) => {
        acc.totalBorrowed += borrow.amount;
        acc.totalRepaid += borrow.total_repaid || 0;
        acc.totalRemaining += borrow.remaining_amount || borrow.amount;
        return acc;
    }, { totalBorrowed: 0, totalRepaid: 0, totalRemaining: 0 });

    return total;
});
</script>

<template>

    <Head title="My Borrows" />

    <UserLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2
                    class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-gray-100 dark:to-gray-300 bg-clip-text text-transparent">
                    My Borrow Summary
                </h2>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8">

            <!-- Stats Overview -->
            <div v-if="props.borrows.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Borrowed</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{
                                formatNumber(totalStats.totalBorrowed) }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Repaid</p>
                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{
                                formatNumber(totalStats.totalRepaid) }}</p>
                        </div>
                        <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-full">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Remaining</p>
                            <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{
                                formatNumber(totalStats.totalRemaining) }}</p>
                        </div>
                        <div class="p-3 bg-rose-100 dark:bg-rose-900/30 rounded-full">
                            <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input v-model="search" @keyup.enter="applySearch"
                        class="block w-full pl-10 pr-3 py-3 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200"
                        placeholder="Search by Borrow ID..." />
                </div>
                <div class="flex items-center space-x-4">
                    <span v-if="props.borrows.length > 2"
                        class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ props.borrows.length }} records
                    </span>
                    <button @click="applySearch"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-105">
                        Search
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="props.borrows.length === 0" class="text-center py-16">
                <div
                    class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gray-100 dark:bg-gray-800 mb-6">
                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No borrow records found</h3>
                <p class="text-gray-500 dark:text-gray-400">Try adjusting your search criteria or check back later.</p>
            </div>

            <!-- Borrow List -->
            <div v-else class="space-y-6">
                <div v-for="borrow in props.borrows" :key="borrow.id"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:border-blue-200 dark:hover:border-blue-700 transition-all duration-300 overflow-hidden">

                    <!-- Header -->
                    <div
                        class="p-6 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-blue-900/20 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex flex-wrap justify-between items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                        Borrow #{{ borrow.id }}
                                    </h3>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-700">
                                        {{ borrow.currency }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 10h6m-6-4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Borrowed on {{ borrow.borrowed_date }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Remaining Amount
                                </p>
                                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ formatNumber(borrow.remaining_amount ?? borrow.amount) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6">
                        <!-- Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div
                                    class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Interest
                                        Due</span>
                                    <span class="text-lg font-bold text-rose-600 dark:text-rose-400">
                                        {{ borrow.interest ? formatNumber(borrow.interest) : 'Pending' }}
                                    </span>
                                </div>
                                <div
                                    class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total
                                        Repaid</span>
                                    <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ formatNumber(borrow.total_repaid ?? 0) }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Progress Section -->
                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                                    <div class="flex justify-between items-center mb-3">
                                        <span
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ Math.round(((borrow.total_repaid / borrow.amount) * 100) || 0) }}%
                                        </span>
                                    </div>
                                    <div class="w-full h-3 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full transition-all duration-700 ease-out"
                                            :style="{ width: ((borrow.total_repaid / borrow.amount) * 100 || 0) + '%' }">
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div class="flex justify-center">
                                    <div :class="[
                                        'inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold border',
                                        getStatusConfig(borrow.status).bg,
                                        getStatusConfig(borrow.status).text,
                                        getStatusConfig(borrow.status).border
                                    ]">
                                        <span class="mr-2">{{ getStatusConfig(borrow.status).icon }}</span>
                                        {{ borrow.status }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Repayment History -->
                        <details class="group">
                            <summary
                                class="flex items-center justify-between cursor-pointer p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                <span class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Repayment History
                                </span>
                                <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform duration-200"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <div
                                class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-600">
                                <div v-if="!borrow.repayments || borrow.repayments.length === 0"
                                    class="text-center py-8">
                                    <div
                                        class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No repayments yet</p>
                                </div>
                                <div v-else class="space-y-3">
                                    <div v-for="repayment in borrow.repayments" :key="repayment.id"
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                repayment.date
                                                }}</span>
                                        </div>
                                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ formatNumber(repayment.amount) }} {{ borrow.currency }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center items-center space-x-4 pt-8">
                <button :disabled="props.currentPage === 1"
                    @click="window.location.href = `?page=${props.currentPage - 1}`"
                    class="flex items-center px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </button>

                <div class="flex items-center space-x-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-xl">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Page</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ props.currentPage }}</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">of</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ props.lastPage }}</span>
                </div>

                <button :disabled="props.currentPage === props.lastPage"
                    @click="window.location.href = `?page=${props.currentPage + 1}`"
                    class="flex items-center px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                    Next
                    <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </UserLayout>
</template>