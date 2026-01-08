<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, watch, onMounted } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

ChartJS.register(LineElement, PointElement, CategoryScale, LinearScale, Title, Tooltip, Legend);

const props = defineProps({
    profits: Array,
    currencies: Array,
    users: Array,
    filters: Object,
    totalProfit: Number,
});

// Loading states
const isLoading = ref(false);
const isExporting = ref(false);
const isChartLoading = ref(true);

// Error handling
const error = ref(null);
const toast = ref({ show: false, message: '', type: 'success' });

// Filter debouncing
const filterTimeout = ref(null);

// Animations
const animatedProfit = ref(0);
const showContent = ref(false);

// Initialize component
onMounted(() => {
    // Simulate initial loading
    setTimeout(() => {
        isChartLoading.value = false;
        showContent.value = true;
        animateProfit();
    }, 500);
});

// Animate profit counter
const animateProfit = () => {
    const duration = 1000;
    const start = performance.now();
    const startValue = 0;
    const endValue = props.totalProfit;

    const animate = (currentTime) => {
        const elapsed = currentTime - start;
        const progress = Math.min(elapsed / duration, 1);

        animatedProfit.value = startValue + (endValue - startValue) * easeOutQuart(progress);

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

// Export with loading state and error handling
const exportToExcel = async () => {
    try {
        isExporting.value = true;
        const params = new URLSearchParams(props.filters).toString();

        // Create a temporary link for download
        const link = document.createElement('a');
        link.href = route('admin.profit-report.export') + '?' + params;
        link.download = `profit-report-${new Date().toISOString().split('T')[0]}.xlsx`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showToast('Report exported successfully!', 'success');
    } catch (err) {
        error.value = 'Failed to export report. Please try again.';
        showToast('Export failed. Please try again.', 'error');
    } finally {
        isExporting.value = false;
    }
};

// Debounced filter updates
const updateFilters = (key, value) => {
    if (filterTimeout.value) {
        clearTimeout(filterTimeout.value);
    }

    filterTimeout.value = setTimeout(() => {
        isLoading.value = true;
        error.value = null;

        const newFilters = { ...props.filters, [key]: value };

        router.get(route('admin.profit.monthly'), newFilters, {
            preserveScroll: true,
            onSuccess: () => {
                isLoading.value = false;
                showToast('Filters updated successfully!');
                animateProfit();
            },
            onError: (errors) => {
                isLoading.value = false;
                error.value = 'Failed to update filters. Please try again.';
                showToast('Failed to update filters', 'error');
            }
        });
    }, 300);
};

// Enhanced chart data with animations
const chartData = computed(() => ({
    labels: props.profits.map(p => p.period_label),
    datasets: [
        {
            label: 'Interest Profit',
            data: props.profits.map(p => p.total_interest),
            borderColor: '#10b981',
            backgroundColor: '#10b98144',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
        },
        {
            label: 'Invested Borrow',
            data: props.profits.map(p => p.total_invested),
            borderColor: '#3b82f6',
            backgroundColor: '#3b82f644',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
        }
    ]
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
            }
        },
        title: {
            display: true,
            text: 'Profit Trend',
            font: {
                size: 16,
                weight: 'bold'
            },
            padding: 20
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
                    return `${context.dataset.label}: ${Intl.NumberFormat().format(context.parsed.y)}`;
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
                    return Intl.NumberFormat().format(value);
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
</script>

<template>

    <Head title="Monthly Profit Report" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-800">Monthly Profit Report</h2>
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

        <div class="p-4 space-y-4 text-sm">
            <!-- Error Alert -->
            <Transition name="fade">
                <div v-if="error"
                    class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-red-700">{{ error }}</p>
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

            <!-- Loading Overlay -->
            <Transition name="fade">
                <div v-if="isLoading"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40">
                    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                        <span class="text-gray-700">Updating filters...</span>
                    </div>
                </div>
            </Transition>

            <!-- Filters with enhanced styling -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Filters</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div class="relative">
                        <select @change="e => updateFilters('month', e.target.value)" class="input-enhanced">
                            <option value="">All Months</option>
                            <option v-for="m in 12" :value="m" :selected="filters.month == m">Month {{ m }}</option>
                        </select>
                    </div>

                    <div class="relative">
                        <select @change="e => updateFilters('year', e.target.value)" class="input-enhanced">
                            <option value="">All Years</option>
                            <option v-for="y in [2023, 2024, 2025]" :value="y" :selected="filters.year == y">Year {{ y
                                }}
                            </option>
                        </select>
                    </div>

                    <div class="relative">
                        <select @change="e => updateFilters('currency_id', e.target.value)" class="input-enhanced">
                            <option value="">All Currencies</option>
                            <option v-for="c in currencies" :value="c.id" :selected="filters.currency_id == c.id">{{
                                c.symbol }}
                            </option>
                        </select>
                    </div>

                    <div class="relative">
                        <select @change="e => updateFilters('user_id', e.target.value)" class="input-enhanced">
                            <option value="">All Users</option>
                            <option v-for="u in users" :value="u.id" :selected="filters.user_id == u.id">{{ u.name }}
                            </option>
                        </select>
                    </div>

                    <div class="relative">
                        <select @change="e => updateFilters('period', e.target.value)" class="input-enhanced">
                            <option value="monthly" :selected="filters.period === 'monthly'">Monthly</option>
                            <option value="weekly" :selected="filters.period === 'weekly'">Weekly</option>
                            <option value="daily" :selected="filters.period === 'daily'">Daily</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stats Card -->
            <Transition name="slide-up">
                <div v-if="showContent"
                    class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Profit</p>
                            <p class="text-3xl font-bold">{{ Intl.NumberFormat().format(Math.round(animatedProfit)) }}
                            </p>
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
            </Transition>

            <!-- Export Button -->
            <div class="text-right">
                <button @click="exportToExcel" :disabled="isExporting" class="btn-primary">
                    <div v-if="isExporting" class="flex items-center space-x-2">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                        <span>Exporting...</span>
                    </div>
                    <div v-else class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Export to Excel</span>
                    </div>
                </button>
            </div>

            <!-- Chart Section -->
            <Transition name="slide-up">
                <div v-if="showContent" class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Profit Trend</h3>
                    <div class="relative">
                        <!-- Chart Loading Skeleton -->
                        <div v-if="isChartLoading"
                            class="h-72 bg-gray-100 rounded animate-pulse flex items-center justify-center">
                            <div class="text-gray-500">Loading chart...</div>
                        </div>

                        <!-- Actual Chart -->
                        <div v-else class="h-72">
                            <Line :data="chartData" :options="chartOptions" />
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- Table Section -->
            <Transition name="slide-up">
                <div v-if="showContent" class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-700">Detailed Report</h3>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!profits.length" class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500">No data available for the selected filters.</p>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm text-gray-700">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-gray-900 tracking-wider">Period</th>
                                    <th class="px-6 py-3 text-right font-medium text-gray-900 tracking-wider">Total
                                        Interest
                                    </th>
                                    <th class="px-6 py-3 text-right font-medium text-gray-900 tracking-wider">Invested
                                        Borrow
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(p, index) in profits" :key="p.period_label"
                                    class="hover:bg-gray-50 transition-colors duration-150"
                                    :style="{ animationDelay: `${index * 50}ms` }">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ p.period_label
                                        }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-green-600 font-medium">
                                        {{ Intl.NumberFormat().format(p.total_interest) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-blue-600 font-medium">
                                        {{ Intl.NumberFormat().format(p.total_invested) }}
                                    </td>
                                </tr>
                                <tr class="bg-gray-50 font-bold border-t-2 border-gray-200">
                                    <td class="px-6 py-4 text-right text-gray-900">Total Profit</td>
                                    <td class="px-6 py-4 text-right text-green-700 text-lg">
                                        {{ Intl.NumberFormat().format(totalProfit) }}
                                    </td>
                                    <td class="px-6 py-4"></td>
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
.input-enhanced {
    @apply w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 text-sm transition-all duration-200;
}

.btn-primary {
    @apply inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 disabled:cursor-not-allowed;
}

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
</style>