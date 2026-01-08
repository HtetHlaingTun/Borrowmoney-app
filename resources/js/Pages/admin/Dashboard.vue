<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';

import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    PointElement,
    CategoryScale,
    LinearScale,
} from 'chart.js';
import { Line, Bar } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, LineElement, BarElement, PointElement, CategoryScale, LinearScale);

const props = defineProps({
    interestStats: Array,
    repaymentStats: Array,
    users: Array,
    selectedUserId: [String, Number],
    summary: Object,
});

const form = useForm({
    user_id: props.selectedUserId ?? '',
});

// Loading and animation states
const isLoading = ref(false);
const isChartsLoading = ref(true);
const showContent = ref(false);
const animatedStats = ref({
    totalBorrows: 0,
    totalRepaid: 0,
    totalBorrowAmount: 0
});

// Toast notifications
const toast = ref({ show: false, message: '', type: 'success' });

// Error handling
const error = ref(null);

// Filter timeout for debouncing
const filterTimeout = ref(null);

// Initialize component
onMounted(() => {
    setTimeout(() => {
        isChartsLoading.value = false;
        showContent.value = true;
        animateStats();
    }, 800);
});

// Animate statistics counters
const animateStats = () => {
    const duration = 1500;
    const start = performance.now();

    const startValues = {
        totalBorrows: 0,
        totalRepaid: 0,
        totalBorrowAmount: 0,
        totalbalance: 0
    };

    const endValues = {
        totalBorrows: props.summary.totalBorrows,
        totalRepaid: props.summary.totalRepaid,
        totalBorrowAmount: props.summary.totalBorrowAmount,
        totalbalance: props.summary.totalbalance
    };

    const animate = (currentTime) => {
        const elapsed = currentTime - start;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = easeOutQuart(progress);

        animatedStats.value = {
            totalBorrows: Math.round(startValues.totalBorrows + (endValues.totalBorrows - startValues.totalBorrows) * easedProgress),
            totalRepaid: Math.round(startValues.totalRepaid + (endValues.totalRepaid - startValues.totalRepaid) * easedProgress),
            totalBorrowAmount: Math.round(startValues.totalBorrowAmount + (endValues.totalBorrowAmount - startValues.totalBorrowAmount) * easedProgress),
            totalbalance: Math.round(startValues.totalbalance + (endValues.totalbalance - startValues.totalbalance) * easedProgress)
        };

        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    };

    requestAnimationFrame(animate);
};

// Easing function
const easeOutQuart = (t) => 1 - Math.pow(1 - t, 4);

// Show toast notification
const showToast = (message, type = 'success') => {
    toast.value = { show: true, message, type };
    setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

// Debounced filter function
function filterReport() {
    if (filterTimeout.value) {
        clearTimeout(filterTimeout.value);
    }

    filterTimeout.value = setTimeout(() => {
        isLoading.value = true;
        error.value = null;

        router.get(route('admin.dashboard'), { user_id: form.user_id || '' }, {
            preserveScroll: true,
            onSuccess: () => {
                isLoading.value = false;
                showToast('Dashboard updated successfully!');
                animateStats();
            },
            onError: (errors) => {
                isLoading.value = false;
                error.value = 'Failed to update dashboard. Please try again.';
                showToast('Failed to update dashboard', 'error');
            }
        });
    }, 300);
}

// Enhanced chart: Interest over time
const interestChartData = computed(() => {
    const groupedByCurrency = {};

    props.interestStats.forEach(item => {
        if (!groupedByCurrency[item.currency]) {
            groupedByCurrency[item.currency] = {
                label: `Interest (${item.currency})`,
                data: [],
                borderColor: item.currency === '$' ? '#10b981' : '#f59e0b',
                backgroundColor: item.currency === '$' ? 'rgba(16,185,129,0.1)' : 'rgba(245,158,11,0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: item.currency === '$' ? '#10b981' : '#f59e0b',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointHoverBorderWidth: 3,
            };
        }
        groupedByCurrency[item.currency].data.push(item.total_interest);
    });

    const labels = [...new Set(props.interestStats.map(i => {
        const date = new Date(i.month + '-01');
        return [
            date.toLocaleString('default', { month: 'short' }), // "Jul"
            date.getFullYear().toString()                        // "2025"
        ];
    }))];

    return {
        labels,
        datasets: Object.values(groupedByCurrency),
    };
});

// Enhanced chart: Repaid vs Balance
const repaymentChartData = computed(() => ({
    labels: props.repaymentStats.map(i => `Borrow #${i.borrow_id}`),
    datasets: [
        {
            label: 'Repaid',
            backgroundColor: '#10b981',
            borderColor: '#059669',
            borderWidth: 1,
            borderRadius: 4,
            data: props.repaymentStats.map(i => i.total_repaid),
        },
        {
            label: 'Balance',
            backgroundColor: '#ef4444',
            borderColor: '#dc2626',
            borderWidth: 1,
            borderRadius: 4,
            data: props.repaymentStats.map(i => i.balance),
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        intersect: false,
        mode: 'index',
    },
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 20,
                usePointStyle: true,
                font: {
                    size: 12
                }
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#ffffff',
            bodyColor: '#ffffff',
            borderColor: '#374151',
            borderWidth: 1,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
                label: function (context) {
                    return `${context.dataset.label}: ${new Intl.NumberFormat().format(context.parsed.y)}`;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)',
            },
            ticks: {
                callback: function (value) {
                    return new Intl.NumberFormat().format(value);
                }
            }
        },
        x: {
            grid: {
                display: false,
            }
        }
    },
    animation: {
        duration: 1000,
        easing: 'easeOutQuart',
    }
};

// Dismiss error
const dismissError = () => {
    error.value = null;
};

// Computed stats for progress indicators
const repaymentProgress = computed(() => {
    return props.repaymentStats.map(item => ({
        ...item,
        progress: Math.round((item.total_repaid / item.borrow_amount) * 100)
    }));
});
</script>

<template>

    <Head title="Reports Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Reports Dashboard</h2>
        </template>

        <!-- Toast Notifications -->
        <Transition name="toast">
            <div v-if="toast.show" :class="[
                'fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium',
                toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'
            ]">
                {{ toast.message }}
            </div>
        </Transition>

        <!-- Loading Overlay -->
        <Transition name="fade">
            <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                    <span class="text-gray-700 dark:text-gray-300">Updating dashboard...</span>
                </div>
            </div>
        </Transition>

        <div class="py-8 max-w-7xl mx-auto space-y-8">
            <!-- Error Alert -->
            <Transition name="fade">
                <div v-if="error"
                    class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-red-700 dark:text-red-300">{{ error }}</p>
                    </div>
                    <button @click="dismissError" class="text-red-500 hover:text-red-700">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </Transition>

            <!-- User Filter -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Filter by User</label>
                <div class="max-w-xs relative">
                    <select v-model="form.user_id" @change="filterReport"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="">All Users</option>
                        <option v-for="user in props.users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <Transition name="slide-up">
                <div v-if="showContent" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Borrows Card -->
                    <div
                        class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Total Borrows</p>
                                <p class="text-3xl font-bold">{{ animatedStats.totalBorrows }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Repaid Card -->
                    <div
                        class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Total Repaid</p>
                                <p class="text-3xl font-bold">
                                    {{ new Intl.NumberFormat().format(animatedStats.totalRepaid) }}
                                </p>
                                <p class="text-green-100 text-sm">{{ props.summary.currency }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Borrowed Amount Card -->
                    <div
                        class="bg-gradient-to-br from-red-500 to-red-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm font-medium">Total Borrowed Amount</p>
                                <p class="text-3xl font-bold">
                                    {{ new Intl.NumberFormat().format(animatedStats.totalBorrowAmount) }}
                                </p>
                                <p class="text-red-100 text-sm">{{ props.summary.currency }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Balance Amount Card -->
                    <div
                        class="bg-gradient-to-br from-yellow-500 to-red-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm font-medium">Total Balance Amount</p>
                                <p class="text-3xl font-bold">
                                    {{ new Intl.NumberFormat().format(animatedStats.totalbalance) }}
                                </p>
                                <p class="text-red-100 text-sm">{{ props.summary.currency }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-3">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- Charts -->
            <Transition name="slide-up">
                <div v-if="showContent" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Interest Chart -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Interest Over Time</h3>
                        <div class="relative">
                            <!-- Chart Loading Skeleton -->
                            <div v-if="isChartsLoading"
                                class="h-80 bg-gray-100 dark:bg-gray-700 rounded animate-pulse flex items-center justify-center">
                                <div class="text-gray-500 dark:text-gray-400">Loading chart...</div>
                            </div>

                            <!-- Actual Chart -->
                            <div v-else class="h-80">
                                <Line :data="interestChartData" :options="chartOptions" />
                            </div>
                        </div>
                    </div>

                    <!-- Repayment Chart -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Repaid vs Balance</h3>
                        <div class="relative">
                            <!-- Chart Loading Skeleton -->
                            <div v-if="isChartsLoading"
                                class="h-80 bg-gray-100 dark:bg-gray-700 rounded animate-pulse flex items-center justify-center">
                                <div class="text-gray-500 dark:text-gray-400">Loading chart...</div>
                            </div>

                            <!-- Actual Chart -->
                            <div v-else class="h-80">
                                <Bar :data="repaymentChartData" :options="chartOptions" />
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- Enhanced Table -->
            <Transition name="slide-up">
                <div v-if="showContent" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Repayment Details</h3>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!props.repaymentStats.length" class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">No repayment data available.</p>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        #</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        User</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        Borrow ID</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        Currency</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        Borrowed</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        Repaid</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        Balance</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        Progress</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="(item, index) in repaymentProgress" :key="item.borrow_id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150"
                                    :style="{ animationDelay: `${index * 50}ms` }">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{
                                        item.user }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <span
                                            class="px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">
                                            #{{ item.borrow_id }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{
                                        item.currency }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600 dark:text-red-400">
                                        {{ new Intl.NumberFormat().format(item.borrow_amount) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                        {{ new Intl.NumberFormat().format(item.total_repaid) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-orange-600 dark:text-orange-400">
                                        {{ new Intl.NumberFormat().format(item.balance) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                                <div class="h-2 rounded-full transition-all duration-1000"
                                                    :class="item.progress >= 100 ? 'bg-green-500' : item.progress >= 50 ? 'bg-yellow-500' : 'bg-red-500'"
                                                    :style="{ width: `${Math.min(item.progress, 100)}%` }">
                                                </div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                                {{ item.progress }}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </Transition>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Animations */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-up-enter-active {
    transition: all 0.4s ease-out;
}

.slide-up-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100px);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100px);
}

/* Table row animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

tbody tr {
    animation: fadeInUp 0.3s ease-out forwards;
}

/* Card hover effects */
.transform:hover {
    transform: translateY(-2px);
}
</style>