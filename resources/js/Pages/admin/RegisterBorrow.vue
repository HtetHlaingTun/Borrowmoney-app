<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
    currencies: Array,
    borrows: Array,
});

const showAlert = ref(false);
const alertMessage = ref('');
let alertTimeout = null;

const form = useForm({
    user_id: '',
    currency_id: '',
    borrowed_date: '',
    interest_date: '',
    rate: '',
    amount: '',
});

function closeAlert() {
    showAlert.value = false;
    if (alertTimeout) {
        clearTimeout(alertTimeout);
        alertTimeout = null;
    }
}

function onSuccess(type = 'create') {
    form.reset();

    alertMessage.value = type === 'delete'
        ? 'Borrow deleted successfully!'
        : 'Borrowing was registered successfully!';

    showAlert.value = true;

    if (alertTimeout) clearTimeout(alertTimeout);
    alertTimeout = setTimeout(() => (showAlert.value = false), 4000);
}

function handleSubmit() {
    form.post(route('admin.borrow.store'), {
        onSuccess: () => onSuccess('create'),
        preserveScroll: true,
    });
}

function deleteBorrow(id) {
    if (confirm('Are you sure you want to delete this borrow record?')) {
        router.delete(route('admin.borrow.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                onSuccess('delete');
                router.reload({ only: ['borrows'] });
            },
        });
    }
}
</script>

<template>

    <Head title="Borrow Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">Borrow</h2>
        </template>

        <div class="py-12 space-y-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-6">

                    <!-- Alert -->
                    <Transition name="fade">
                        <div v-if="showAlert"
                            class="mb-6 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700 dark:bg-green-800/10">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-green-600 dark:text-green-300" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11.414l-4 4a1 1 0 101.414 1.414L11 9.414l2.293 2.293a1 1 0 001.414-1.414l-3-3z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm text-green-800 dark:text-green-100 font-medium">{{ alertMessage
                                    }}</span>
                            </div>
                            <button @click="closeAlert" class="text-green-600 hover:text-green-800 dark:text-green-300">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </Transition>

                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Register New Borrow</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-300 mb-6">Fill out the fields to register a new
                        borrowing
                        record.</p>

                    <form @submit.prevent="handleSubmit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">User</label>
                            <select v-model="form.user_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                                <option value="" disabled>Select a user</option>
                                <option v-for="user in props.users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">{{ form.errors.user_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                            <select v-model="form.currency_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                                <option value="" disabled>Select a currency</option>
                                <option v-for="currency in props.currencies" :key="currency.id" :value="currency.id">
                                    {{ currency.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.currency_id" class="mt-1 text-sm text-red-600">{{
                                form.errors.currency_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Borrowed
                                Date</label>
                            <input type="datetime-local" v-model="form.borrowed_date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                            <div v-if="form.errors.borrowed_date" class="mt-1 text-sm text-red-600">{{
                                form.errors.borrowed_date
                                }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Interest
                                Date</label>
                            <input type="datetime-local" v-model="form.interest_date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                            <div v-if="form.errors.interest_date" class="mt-1 text-sm text-red-600">{{
                                form.errors.interest_date
                                }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rate</label>
                            <input type="number" step="0.01" v-model="form.rate"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g., 1.2" required>
                            <div v-if="form.errors.rate" class="mt-1 text-sm text-red-600">{{ form.errors.rate }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                            <input type="number" step="0.01" v-model="form.amount"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Enter amount" required>
                            <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit"
                                class="w-full md:w-auto inline-flex justify-center px-6 py-2 rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none disabled:opacity-50"
                                :disabled="form.processing">
                                <span v-if="form.processing" class="flex items-center">
                                    <svg class="animate-spin mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Creating...
                                </span>
                                <span v-else>Save</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Borrow List -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Borrow List</h3>

                    <div v-if="!borrows || borrows.length === 0"
                        class="text-center py-8 text-gray-500 dark:text-gray-300">
                        <p>No borrow records found. Start by creating one.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        #
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        Name
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        Date
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        Amount</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        Rate
                                    </th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        Currency</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="(item, index) in borrows" :key="item.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2 text-sm">{{ index + 1 }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        {{props.users.find(u => u.id === item.user_id)?.name || 'N/A'}}
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ new Date(item.borrowed_date).toLocaleDateString()
                                        }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ new Intl.NumberFormat().format(item.amount) }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ item.rate }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        {{props.currencies.find(c => c.id === item.currency_id)?.symbol || 'N/A'}}
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <button @click="deleteBorrow(item.id)"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                                            Delete
                                        </button>
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
