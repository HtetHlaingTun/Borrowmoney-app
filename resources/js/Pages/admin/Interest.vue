<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
// import route from 'ziggy-js';
import axios from 'axios';
import { computed } from 'vue';
import { onMounted } from 'vue';

const interestResults = ref({});

const props = defineProps({
    interests: {
        type: Array,
        default: () => []
    },
    currencies: {
        type: Array,
        default: () => []
    },
    borrows: {
        type: Array,
        default: () => []
    },
    users: {
        type: Array,
        default: () => []
    },
    showInterests: {
        type: Array,
        default: () => []
    }
})

const form = useForm({
    user_id: '',
});

function getDaysInMonth(date) {
    const year = date.getFullYear();
    const month = date.getMonth();
    return new Date(year, month + 1, 0).getDate();
}

const calculatedIds = ref(new Set());
const paidIds = ref(new Set());

function calculateInterest(borrowId) {
    axios.post(route('admin.interest.calculate', borrowId))
        .then(res => {
            const data = res.data.data;

            interestResults.value[borrowId] = {
                interest: data.interest,
                days: data.days,
                start_date: data.start_date,
                end_date: data.end_date
            };

            calculatedIds.value.add(borrowId);
            onSuccess('interest_calculated');
            router.reload({ only: ['showInterests', 'borrows'] });
        })
        .catch(err => {
            console.error('Interest calculation error:', err);
        });
}

const filteredBorrows = computed(() => {
    if (!form.user_id) return [];

    return props.borrows.filter(borrow => {
        return (
            borrow.user_id === Number(form.user_id) &&
            borrow.status?.toLowerCase() === 'active'
        );
    });
});

const showAlert = ref(false);
const alertMessage = ref('');
let alertTimeout = null;

function onSuccess(type = 'create') {
    form.reset();

    let message = 'Interest was calculated successfully!';
    if (type === 'delete') message = 'Records deleted successfully!';
    if (type === 'interest_calculated') message = 'Interest calculated and saved!';
    if (type === 'paid') message = 'Marked as paid!';

    alertMessage.value = message;
    showAlert.value = true;

    if (alertTimeout) {
        clearTimeout(alertTimeout);
    }

    alertTimeout = setTimeout(() => {
        showAlert.value = false;
    }, 4000);
}

function handleSubmit() {
    const url = route('admin.borrow.store')
    form.post(url, {
        onSuccess: () => onSuccess('create'),
        preserveScroll: true,
    });
}

function deleteBorrow(id) {
    if (confirm('Are you sure you want to delete this record?')) {
        router.delete(route('admin.borrow.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                onSuccess('delete');
                router.reload({ only: ['borrows', 'showInterests'] });
            },
        });
    }
}

function markAsPaid(interestId) {
    axios.post(route('admin.interest.paid', interestId))
        .then(() => {
            paidIds.value.add(interestId);
            onSuccess('paid');
            router.reload({ only: ['showInterests', 'borrows'] });
        })
        .catch(err => {
            console.error('Error marking as paid:', err);
        });
}

function isCalculateDisabled(borrow) {
    const interestDate = new Date(borrow.interest_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return interestDate > today;
}

const interestMap = computed(() => {
    const map = new Map();
    props.showInterests.forEach(interest => {
        map.set(interest.borrow_id, interest);
    });
    return map;
});

onMounted(() => {
    props.showInterests.forEach(interest => {
        if (interest.paid) {
            paidIds.value.add(interest.id);
        }
    });
});

function closeAlert() {
    showAlert.value = false;
}
</script>

<template>

    <Head title="Interest Calculator" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Interest Calculator</h1>
                    <p class="text-gray-600 dark:text-gray-400">Calculate and manage loan interests</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Success Alert -->
                <Transition enter-active-class="transition duration-300 ease-out transform"
                    enter-from-class="translate-y-2 opacity-0 scale-95"
                    enter-to-class="translate-y-0 opacity-100 scale-100"
                    leave-active-class="transition duration-200 ease-in transform"
                    leave-from-class="translate-y-0 opacity-100 scale-100"
                    leave-to-class="translate-y-2 opacity-0 scale-95">
                    <div v-if="showAlert"
                        class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 shadow-lg backdrop-blur-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                    {{ alertMessage }}
                                </p>
                            </div>
                            <button @click="closeAlert"
                                class="ml-3 flex-shrink-0 text-green-400 hover:text-green-600 dark:hover:text-green-300 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </Transition>

                <!-- Main Card -->
                <div
                    class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <!-- Header Section -->
                        <div class="mb-8">
                            <div class="flex items-center gap-2 mb-2">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Interest Management</h2>
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">Select a user to calculate and manage their loan
                                interests</p>
                        </div>

                        <!-- User Selection -->
                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Select User
                            </label>
                            <div class="relative">
                                <select v-model="form.user_id"
                                    class="w-full sm:w-80 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-all duration-200">
                                    <option value="" disabled>Choose a user...</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            <div v-if="form.errors.user_id" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.user_id }}
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div v-if="form.user_id && filteredBorrows.length" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Active Loans</h3>
                                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    {{ filteredBorrows.length }} active loan{{ filteredBorrows.length !== 1 ? 's' : ''
                                    }}
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead
                                            class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">
                                            <tr>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                                    #</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                                    Date</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                                    Amount</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                                    Rate</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                                    Currency</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                                    Interest</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                                    Action</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                                    Manage</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="(item, index) in filteredBorrows" :key="item.id"
                                                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-200">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div
                                                        class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                        <span
                                                            class="text-sm font-medium text-blue-600 dark:text-blue-400">{{
                                                                index + 1 }}</span>
                                                    </div>
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ new Date(item.interest_date).toLocaleDateString() }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ Intl.NumberFormat().format(item.remaining_amount ??
                                                            item.amount) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                                        {{ item.rate }}%
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{props.currencies.find(c => c.id === item.currency_id)?.symbol ??
                                                        'N/A'}}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div v-if="interestMap.get(item.id)" class="space-y-1">
                                                        <div
                                                            class="text-sm font-semibold text-rose-600 dark:text-rose-400">
                                                            {{
                                                                Intl.NumberFormat().format(interestMap.get(item.id).interest
                                                                    ||
                                                                    0) }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ interestMap.get(item.id).days }} days
                                                        </div>
                                                    </div>
                                                    <div v-else class="text-sm text-gray-400 dark:text-gray-500 italic">
                                                        Not calculated
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex flex-col gap-2">
                                                        <template v-if="interestMap.get(item.id)">
                                                            <template v-if="interestMap.get(item.id).paid">
                                                                <span
                                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                                    <svg class="w-3 h-3 mr-1" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                    Paid
                                                                </span>
                                                            </template>
                                                            <template v-else>
                                                                <button @click="markAsPaid(interestMap.get(item.id).id)"
                                                                    :disabled="paidIds.has(interestMap.get(item.id).id)"
                                                                    class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white text-xs font-medium rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                                                                    <svg class="w-3 h-3 mr-1" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                                                    </svg>
                                                                    {{ paidIds.has(interestMap.get(item.id).id) ?
                                                                        'Processing...' : 'Mark Paid' }}
                                                                </button>
                                                            </template>
                                                        </template>
                                                        <template v-else>
                                                            <button @click="calculateInterest(item.id)"
                                                                :disabled="isCalculateDisabled(item) || interestMap.has(item.id)"
                                                                class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-xs font-medium rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                                                                <svg class="w-3 h-3 mr-1" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                                </svg>
                                                                Calculate
                                                            </button>
                                                        </template>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <button @click="deleteBorrow(item.id)"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <div
                                class="w-24 h-24 mx-auto bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Data Available</h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                {{ form.user_id ? 'No active loans found for this user.' :
                                    'Please select a user to view their loans.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>