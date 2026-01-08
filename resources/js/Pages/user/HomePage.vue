<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watchEffect } from 'vue';
import { Pie } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
    Title,
} from 'chart.js';

// Register chart.js components
ChartJS.register(ArcElement, Tooltip, Legend, Title);

const props = defineProps({
    borrows: Array,
});

const formatAmount = (n) => Intl.NumberFormat().format(n);

// Total Repaid and Remaining
const totalRepaid = computed(() =>
    props.borrows.reduce((sum, b) => sum + (b.amount - (b.remaining_amount ?? 0)), 0)
);

const totalRemaining = computed(() =>
    props.borrows.reduce((sum, b) => sum + (b.remaining_amount ?? b.amount), 0)
);

const totalBorrowed = computed(() =>
    props.borrows.reduce((sum, b) => sum + b.amount, 0)
);

const repaymentPercentage = computed(() =>
    totalBorrowed.value > 0 ? Math.round((totalRepaid.value / totalBorrowed.value) * 100) : 0
);

// Chart data and options
const chartData = ref({
    labels: ['Repaid', 'Remaining'],
    datasets: [{
        backgroundColor: ['#10b981', '#f59e0b'],
        borderColor: ['#059669', '#d97706'],
        borderWidth: 2,
        data: [0, 0],
    }],
});

const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                color: '#374151',
                font: { size: 12, weight: '500' },
                padding: 15,
                usePointStyle: true,
                pointStyle: 'circle',
            },
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleFont: { size: 12, weight: '600' },
            bodyFont: { size: 11 },
            cornerRadius: 8,
            displayColors: false,
            callbacks: {
                label: function (context) {
                    const value = formatAmount(context.parsed);
                    const percentage = Math.round((context.parsed / totalBorrowed.value) * 100);
                    return `${context.label}: ${value} (${percentage}%)`;
                }
            }
        },
    },
});

// Sync chart when data changes
watchEffect(() => {
    chartData.value.datasets[0].data = [
        totalRepaid.value,
        totalRemaining.value,
    ];
});

// Status configuration
const getStatusConfig = (borrow) => {
    const repaidAmount = borrow.amount - (borrow.remaining_amount ?? 0);
    const repaidPercentage = (repaidAmount / borrow.amount) * 100;

    if (repaidPercentage === 100) {
        return {
            label: 'Paid',
            bg: 'bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20',
            text: 'text-emerald-700 dark:text-emerald-300',
            border: 'border-emerald-200 dark:border-emerald-700',
            icon: '✅',
            progressColor: 'bg-gradient-to-r from-emerald-500 to-green-500'
        };
    } else if (repaidPercentage > 0) {
        return {
            label: 'Active',
            bg: 'bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20',
            text: 'text-blue-700 dark:text-blue-300',
            border: 'border-blue-200 dark:border-blue-700',
            icon: '🔄',
            progressColor: 'bg-gradient-to-r from-blue-500 to-indigo-500'
        };
    } else {
        return {
            label: 'Pending',
            bg: 'bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20',
            text: 'text-amber-700 dark:text-amber-300',
            border: 'border-amber-200 dark:border-amber-700',
            icon: '⏳',
            progressColor: 'bg-gradient-to-r from-amber-500 to-orange-500'
        };
    }
};

// Summary statistics
const summaryStats = computed(() => {
    const activeLoans = props.borrows.filter(b => (b.remaining_amount ?? b.amount) > 0).length;
    const paidLoans = props.borrows.filter(b => (b.remaining_amount ?? b.amount) === 0).length;
    const avgInterestRate = props.borrows.length > 0
        ? props.borrows.reduce((sum, b) => sum + (b.rate || 0), 0) / props.borrows.length
        : 0;

    return {
        totalLoans: props.borrows.length,
        activeLoans,
        paidLoans,
        avgInterestRate: avgInterestRate.toFixed(1)
    };
});
</script>

<template>

    <Head title="User Dashboard" />

    <UserLayout>
        <template #header>
            <!-- Mobile-optimized header with safe area considerations -->
            <div class="flex items-center space-x-3 sm:space-x-4 pt-safe-top">
                <div
                    class="p-2 sm:p-3 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl sm:rounded-2xl shadow-lg flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 00-2 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2
                        class="text-xl sm:text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-gray-100 dark:to-gray-300 bg-clip-text text-transparent truncate">
                        My Dashboard
                    </h2>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-1 truncate">Overview of your
                        borrowing activities</p>
                </div>
            </div>
        </template>

        <!-- Main content with safe area padding -->
        <div class="py-4 sm:py-8 pb-safe-bottom">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">

                <!-- Summary Statistics - Mobile optimized grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-3 sm:p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Total Loans
                                </p>
                                <p class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-white">{{
                                    summaryStats.totalLoans }}</p>
                            </div>
                            <div class="p-2 sm:p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-3 sm:p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Active Loans
                                </p>
                                <p class="text-lg sm:text-2xl font-bold text-blue-600 dark:text-blue-400">{{
                                    summaryStats.activeLoans }}</p>
                            </div>
                            <div class="p-2 sm:p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-3 sm:p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Paid
                                    Loans
                                </p>
                                <p class="text-lg sm:text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{
                                    summaryStats.paidLoans }}</p>
                            </div>
                            <div class="p-2 sm:p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-emerald-600 dark:text-emerald-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-3 sm:p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg
                                    Interest
                                </p>
                                <p class="text-lg sm:text-2xl font-bold text-amber-600 dark:text-amber-400">{{
                                    summaryStats.avgInterestRate }}%</p>
                            </div>
                            <div class="p-2 sm:p-3 bg-amber-100 dark:bg-amber-900/30 rounded-full flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-amber-600 dark:text-amber-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Dashboard Grid - Mobile responsive -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 sm:gap-8">

                    <!-- Pie Chart Section -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div
                            class="p-4 sm:p-6 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-blue-900/20 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <h3
                                        class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white flex items-center">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 sm:mr-3 text-blue-600 dark:text-blue-400 flex-shrink-0"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 00-2 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                                        </svg>
                                        <span class="truncate">Repayment Overview</span>
                                    </h3>
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 truncate">
                                        Progress
                                        towards full repayment</p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-4">
                                    <div class="text-xl sm:text-2xl font-bold text-blue-600 dark:text-blue-400">{{
                                        repaymentPercentage }}%</div>
                                    <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Completed</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 sm:p-6">
                            <div class="relative w-full h-64 sm:h-80 mb-4 sm:mb-6">
                                <Pie :data="chartData" :options="chartOptions" />
                            </div>

                            <!-- Summary Stats -->
                            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                <div
                                    class="p-3 sm:p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg sm:rounded-xl border border-emerald-200 dark:border-emerald-700">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="text-xs sm:text-sm font-medium text-emerald-700 dark:text-emerald-300 truncate">
                                                Total Repaid</p>
                                            <p
                                                class="text-sm sm:text-lg font-bold text-emerald-900 dark:text-emerald-100 truncate">
                                                {{ formatAmount(totalRepaid) }}</p>
                                        </div>
                                        <div class="text-lg sm:text-2xl flex-shrink-0">💰</div>
                                    </div>
                                </div>
                                <div
                                    class="p-3 sm:p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg sm:rounded-xl border border-amber-200 dark:border-amber-700">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="text-xs sm:text-sm font-medium text-amber-700 dark:text-amber-300 truncate">
                                                Total Remaining</p>
                                            <p
                                                class="text-sm sm:text-lg font-bold text-amber-900 dark:text-amber-100 truncate">
                                                {{ formatAmount(totalRemaining) }}</p>
                                        </div>
                                        <div class="text-lg sm:text-2xl flex-shrink-0">⏰</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Borrow List Section -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div
                            class="p-4 sm:p-6 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-blue-900/20 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <div class="min-w-0 flex-1">
                                    <h3
                                        class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white flex items-center">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 sm:mr-3 text-blue-600 dark:text-blue-400 flex-shrink-0"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                        <span class="truncate">Loan Portfolio</span>
                                    </h3>
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 truncate">Your
                                        active and
                                        completed loans</p>
                                </div>
                                <span v-if="props.borrows.length > 3"
                                    class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 sm:px-3 py-1 rounded-full flex-shrink-0 ml-4">
                                    {{ props.borrows.length }} loans
                                </span>
                            </div>
                        </div>

                        <div class="p-4 sm:p-6">
                            <div v-if="props.borrows.length === 0" class="text-center py-12 sm:py-16">
                                <div
                                    class="mx-auto flex items-center justify-center h-12 w-12 sm:h-16 sm:w-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4 sm:mb-6">
                                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                    </svg>
                                </div>
                                <h4 class="text-base sm:text-lg font-medium text-gray-900 dark:text-white mb-2">No loan
                                    records
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Start by applying for your first
                                    loan.</p>
                            </div>

                            <div v-else
                                class="max-h-[400px] sm:max-h-[500px] overflow-y-auto space-y-3 sm:space-y-4 pr-1 sm:pr-2 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">
                                <div v-for="borrow in props.borrows" :key="borrow.id"
                                    class="group relative bg-gradient-to-r from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-lg sm:rounded-xl p-4 sm:p-5 border border-gray-200 dark:border-gray-600 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300">

                                    <!-- Main Content -->
                                    <div class="flex justify-between items-start mb-3 sm:mb-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2 sm:space-x-3 mb-2">
                                                <h4
                                                    class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">
                                                    #{{ borrow.id }}
                                                </h4>
                                                <span
                                                    class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-700 flex-shrink-0">
                                                    {{ borrow.currency.symbol }}
                                                </span>
                                            </div>
                                            <div
                                                class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-1 truncate">
                                                {{ formatAmount(borrow.amount) }}
                                            </div>
                                            <div
                                                class="flex items-center space-x-3 sm:space-x-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                                <span class="flex items-center">
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                    </svg>
                                                    Rate: {{ borrow.rate }}%
                                                </span>
                                            </div>
                                        </div>

                                        <div class="text-right flex-shrink-0 ml-4">
                                            <div :class="[
                                                'inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-semibold border',
                                                getStatusConfig(borrow).bg,
                                                getStatusConfig(borrow).text,
                                                getStatusConfig(borrow).border
                                            ]">
                                                <span class="mr-1 sm:mr-2">{{ getStatusConfig(borrow).icon }}</span>
                                                <span class="hidden sm:inline">{{ getStatusConfig(borrow).label
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Progress Section -->
                                    <div class="space-y-2 sm:space-y-3">
                                        <div class="flex justify-between items-center text-xs sm:text-sm">
                                            <span class="font-medium text-gray-700 dark:text-gray-300">Progress</span>
                                            <span class="font-bold text-gray-900 dark:text-white">
                                                {{ Math.round(((borrow.amount - (borrow.remaining_amount ?? 0)) /
                                                    borrow.amount)
                                                    * 100) }}%
                                            </span>
                                        </div>

                                        <div
                                            class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 sm:h-3 overflow-hidden">
                                            <div :class="[
                                                'h-2 sm:h-3 rounded-full transition-all duration-700 ease-out',
                                                getStatusConfig(borrow).progressColor
                                            ]"
                                                :style="{ width: ((borrow.amount - (borrow.remaining_amount ?? 0)) / borrow.amount) * 100 + '%' }">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 sm:gap-4 text-xs sm:text-sm">
                                            <div
                                                class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-2 sm:p-3 border border-emerald-200 dark:border-emerald-700">
                                                <div
                                                    class="text-emerald-700 dark:text-emerald-300 font-medium truncate">
                                                    Repaid
                                                </div>
                                                <div
                                                    class="text-sm sm:text-lg font-bold text-emerald-900 dark:text-emerald-100 truncate">
                                                    {{ formatAmount(borrow.amount - (borrow.remaining_amount ?? 0)) }}
                                                </div>
                                            </div>
                                            <div
                                                class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-2 sm:p-3 border border-amber-200 dark:border-amber-700">
                                                <div class="text-amber-700 dark:text-amber-300 font-medium truncate">
                                                    Remaining
                                                </div>
                                                <div
                                                    class="text-sm sm:text-lg font-bold text-amber-900 dark:text-amber-100 truncate">
                                                    {{ formatAmount(borrow.remaining_amount ?? borrow.amount) }}
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
        </div>
    </UserLayout>
</template>

<style scoped>
/* Custom scrollbar styles for better mobile experience */
.scrollbar-thin {
    scrollbar-width: thin;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.8);
}

/* Safe area support for iOS devices */
.pt-safe-top {
    padding-top: env(safe-area-inset-top);
}

.pb-safe-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}

/* Ensure proper touch targets on mobile */
@media (max-width: 640px) {
    .hover\:shadow-md:hover {
        box-shadow: none;
    }

    .hover\:border-blue-300:hover {
        border-color: inherit;
    }
}

/* Fix text truncation on very small screens */
@media (max-width: 375px) {
    .text-xl {
        font-size: 1.125rem;
        line-height: 1.75rem;
    }

    .text-2xl {
        font-size: 1.25rem;
        line-height: 1.75rem;
    }
}
</style>