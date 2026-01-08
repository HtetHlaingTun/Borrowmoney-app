<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    users: Array,
    borrows: Array,
    selectedUserId: [String, Number],
});

const form = useForm({
    user_id: props.selectedUserId ?? '',
});

const repayAmounts = ref({}); // Track repayment input per borrow ID
const processingRepayment = ref({}); // Track loading state per borrow ID

// Get selected user name for display
const selectedUser = computed(() => {
    if (!form.user_id) return null;
    return props.users.find(user => user.id == form.user_id);
});

// Calculate total outstanding amount
const totalOutstanding = computed(() => {
    return props.borrows.reduce((sum, borrow) => {
        return sum + (borrow.remaining_amount ?? borrow.amount);
    }, 0);
});

function filterBorrows() {
    router.get(route('admin.repayment'), {
        user_id: form.user_id,
    }, {
        preserveScroll: true,
    });
}

function submitRepayment(borrowId) {
    const amount = repayAmounts.value[borrowId];

    if (!amount || amount <= 0) {
        // Modern toast notification instead of alert
        showToast('Please enter a valid amount', 'error');
        return;
    }

    processingRepayment.value[borrowId] = true;

    router.post(route('admin.repayment.store', borrowId), {
        amount: amount
    }, {
        onSuccess: () => {
            repayAmounts.value[borrowId] = '';
            processingRepayment.value[borrowId] = false;
            showToast('Repayment submitted successfully!', 'success');
        },
        onError: (errors) => {
            processingRepayment.value[borrowId] = false;
            showToast('Failed: ' + JSON.stringify(errors), 'error');
        }
    });
}

const flashError = ref('');
const toast = ref({ show: false, message: '', type: '' });
const page = usePage();

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash.error) {
        flashError.value = flash.error;
    } else {
        flashError.value = '';
    }
}, { immediate: true });

// Modern toast notification system
function showToast(message, type = 'info') {
    toast.value = { show: true, message, type };
    setTimeout(() => {
        toast.value.show = false;
    }, 4000);
}

// Format currency with proper symbols
function formatCurrency(amount, symbol = '$') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: symbol === '$' ? 'USD' : 'USD',
        minimumFractionDigits: 2
    }).format(amount);
}

// Get status color for amounts
function getAmountStatus(amount) {
    if (amount > 10000) return 'text-red-600 dark:text-red-400';
    if (amount > 5000) return 'text-orange-600 dark:text-orange-400';
    return 'text-green-600 dark:text-green-400';
}
</script>

<template>

    <Head title="Repayment Management" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Repayment Management
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Manage loan repayments and track outstanding balances
                    </p>
                </div>
                <div v-if="selectedUser" class="text-right">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Selected User</div>
                    <div class="font-semibold text-gray-900 dark:text-white">{{ selectedUser.name }}</div>
                </div>
            </div>
        </template>

        <!-- Toast Notification -->
        <Transition enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="translate-y-[-100%] opacity-0" enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition-all duration-300 ease-in" leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-[-100%] opacity-0">
            <div v-if="toast.show" class="fixed top-4 right-4 z-50 max-w-sm">
                <div :class="[
                    'rounded-lg shadow-lg border-l-4 p-4 backdrop-blur-sm',
                    toast.type === 'success' ? 'bg-green-50 border-green-400 text-green-800' :
                        toast.type === 'error' ? 'bg-red-50 border-red-400 text-red-800' :
                            'bg-blue-50 border-blue-400 text-blue-800'
                ]">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg v-if="toast.type === 'success'" class="h-5 w-5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg v-else-if="toast.type === 'error'" class="h-5 w-5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 text-sm font-medium">{{ toast.message }}</div>
                        <button @click="toast.show = false"
                            class="ml-auto text-sm opacity-70 hover:opacity-100">×</button>
                    </div>
                </div>
            </div>
        </Transition>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div
                    class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Records</p>
                            <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ props.borrows.length }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-500 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-2xl p-6 border border-emerald-200 dark:border-emerald-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Outstanding</p>
                            <p class="text-2xl font-bold text-emerald-900 dark:text-emerald-100">{{
                                formatCurrency(totalOutstanding) }}</p>
                        </div>
                        <div class="p-3 bg-emerald-500 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-2xl p-6 border border-purple-200 dark:border-purple-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Active Users</p>
                            <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ props.users.length }}
                            </p>
                        </div>
                        <div class="p-3 bg-purple-500 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                <!-- Header Section -->
                <div
                    class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Loan Records</h3>

                        <!-- Enhanced Filter Dropdown -->
                        <div class="relative">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                Filter by User
                            </label>
                            <div class="relative">
                                <select v-model="form.user_id" @change="filterBorrows"
                                    class="appearance-none w-full sm:w-64 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 pr-10 text-sm font-medium text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                    <option value="">All Users</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="flashError"
                    class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 p-4 m-6 rounded-r-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ flashError }}</p>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="p-6">
                    <!-- No Records State -->
                    <div v-if="props.borrows.length === 0" class="text-center py-12">
                        <div
                            class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Records Found</h3>
                        <p class="text-gray-500 dark:text-gray-400">No borrow records found for the selected user.</p>
                    </div>

                    <!-- Modern Table -->
                    <div v-else class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Record
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Rate
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Repayment
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="(borrow, index) in props.borrows" :key="borrow.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 text-sm font-medium">
                                                {{ index + 1 }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(borrow.remaining_amount ?? borrow.amount) }}
                                            </span>
                                            <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                                {{ borrow.currency.symbol }}
                                            </span>
                                        </div>
                                        <div
                                            :class="['text-xs font-medium', getAmountStatus(borrow.remaining_amount ?? borrow.amount)]">
                                            Outstanding
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                borrow.rate
                                            }}%</span>
                                            <div class="ml-2 w-2 h-2 rounded-full" :class="[
                                                borrow.rate > 10 ? 'bg-red-400' :
                                                    borrow.rate > 5 ? 'bg-yellow-400' : 'bg-green-400'
                                            ]"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ new Date(borrow.borrowed_date).toLocaleDateString('en-US', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: 'numeric'
                                        }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="relative">
                                                <input v-model="repayAmounts[borrow.id]" type="number" step="0.01"
                                                    min="0" :max="borrow.remaining_amount ?? borrow.amount"
                                                    class="w-28 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-200"
                                                    placeholder="0.00" />
                                            </div>
                                            <button @click="submitRepayment(borrow.id)"
                                                :disabled="processingRepayment[borrow.id]"
                                                class="relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-medium rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                                <svg v-if="processingRepayment[borrow.id]"
                                                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                <span v-if="!processingRepayment[borrow.id]">Repay</span>
                                                <span v-else>Processing...</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom scrollbar for webkit browsers */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
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