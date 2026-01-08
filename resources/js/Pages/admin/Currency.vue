<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch, onUnmounted } from 'vue';

// Define props to receive currencies data from the server
const props = defineProps({
    currencies: {
        type: Array,
        default: () => []
    },
    edit_currencies: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    name: '',
    symbol: '',
});

const page = usePage();
const showAlert = ref(false);
const alertMessage = ref('');
let alertTimeout = null;
const isEditing = ref(false);
const isDelete = ref(false);
const editingCurrencyId = ref(null)

// Watch for flash messages - this only triggers once per flash message
watch(() => page.props.flash?.success, (newVal) => {
    if (newVal) {
        alertMessage.value = newVal;
        showAlert.value = true;

        if (alertTimeout) {
            clearTimeout(alertTimeout);
        }

        alertTimeout = setTimeout(() => {
            showAlert.value = false;
        }, 4000);
    }
});

// Clean up timeout on component unmount
onUnmounted(() => {
    if (alertTimeout) {
        clearTimeout(alertTimeout);
    }
});

function onSuccess(type = 'create') {
    form.reset();
    isEditing.value = false;
    editingCurrencyId.value = null;

    let message = 'Currency saved successfully!';
    if (type === 'update') message = 'Currency updated successfully!';
    if (type === 'delete') message = 'Currency deleted successfully!';

    alertMessage.value = message;
    showAlert.value = true;

    if (alertTimeout) {
        clearTimeout(alertTimeout);
    }

    alertTimeout = setTimeout(() => {
        showAlert.value = false;
    }, 4000);
}

function closeAlert() {
    showAlert.value = false;
    if (alertTimeout) {
        clearTimeout(alertTimeout);
        alertTimeout = null;
    }
}

function handleSubmit() {
    const url = isEditing.value
        ? route('admin.currency.update', editingCurrencyId.value)
        : route('admin.currency.store');

    form.post(url, {
        onSuccess: () => onSuccess(isEditing.value ? 'update' : 'create'),
        preserveScroll: true,
    });
}

function editCurrency(id) {
    const currency = props.currencies.find(c => c.id === id);
    if (currency) {
        form.name = currency.name;
        form.symbol = currency.symbol;
        editingCurrencyId.value = id;
        isEditing.value = true;
    }
}

function cancelEdit() {
    isEditing.value = false;
    editingCurrencyId.value = null;
    form.reset();
}

function deleteCurrency(id) {
    if (confirm('Are you sure you want to delete this currency?')) {
        router.delete(route('admin.currency.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                onSuccess('delete');
                router.reload({ only: ['currencies'] });
            },
        });
    }
}
</script>

<template>

    <Head title="Currency" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <div
                    class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <div>
                    <h2
                        class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 dark:from-white dark:to-gray-200 bg-clip-text text-transparent">
                        Currency Management
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage your currency settings</p>
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
                        class="mb-6 relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 p-4 border border-emerald-200 dark:border-emerald-800 shadow-lg backdrop-blur-sm"
                        role="alert">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-teal-500/5"></div>
                        <div class="relative flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex items-center justify-center w-8 h-8 bg-emerald-100 dark:bg-emerald-800 rounded-full">
                                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">
                                    {{ alertMessage }}
                                </p>
                            </div>
                            <div class="ml-auto">
                                <button @click="closeAlert"
                                    class="text-emerald-500 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 p-1 rounded-full hover:bg-emerald-100 dark:hover:bg-emerald-800 transition-colors duration-200"
                                    type="button">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Currency Form -->
                    <div class="lg:col-span-1">
                        <div
                            class="relative overflow-hidden rounded-3xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10">
                            </div>
                            <div class="relative p-8">
                                <div class="text-center mb-8">
                                    <div
                                        class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg mb-4">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                        {{ isEditing ? 'Edit Currency' : 'Add New Currency' }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ isEditing ? 'Update currency information' : 'Create a new currency entry' }}
                                    </p>
                                </div>

                                <form @submit.prevent="handleSubmit" class="space-y-6">
                                    <!-- Currency Name -->
                                    <div class="group">
                                        <label for="currency-name"
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                            Currency Name
                                        </label>
                                        <div class="relative">
                                            <input id="currency-name" type="text"
                                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                                v-model="form.name" placeholder="e.g., US Dollar"
                                                :class="{ 'border-red-500 dark:border-red-400 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500/10': form.errors.name }"
                                                required>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.name"
                                            class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ form.errors.name }}
                                        </div>
                                    </div>

                                    <!-- Currency Symbol -->
                                    <div class="group">
                                        <label for="currency-symbol"
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                            Currency Symbol
                                        </label>
                                        <div class="relative">
                                            <input id="currency-symbol" type="text"
                                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                                v-model="form.symbol" placeholder="e.g., $"
                                                :class="{ 'border-red-500 dark:border-red-400 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500/10': form.errors.symbol }"
                                                required>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.symbol"
                                            class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ form.errors.symbol }}
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button type="submit"
                                            class="flex-1 relative overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105"
                                            :disabled="form.processing">
                                            <span v-if="form.processing" class="flex items-center justify-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                Processing...
                                            </span>
                                            <span v-else class="flex items-center justify-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                {{ isEditing ? 'Update Currency' : 'Save Currency' }}
                                            </span>
                                        </button>

                                        <button v-if="isEditing" @click="cancelEdit" type="button"
                                            class="px-6 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-500/10 text-sm font-semibold transition-all duration-200 transform hover:scale-105">
                                            <span class="flex items-center justify-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Cancel
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Currency List -->
                    <div class="lg:col-span-2">
                        <div
                            class="relative overflow-hidden rounded-3xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-gray-50 to-slate-50 dark:from-gray-900/50 dark:to-slate-900/50">
                            </div>
                            <div class="relative p-8">
                                <div class="flex items-center justify-between mb-8">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v4a2 2 0 002 2h2m6-4a2 2 0 012 2v4a2 2 0 01-2 2h-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Currency List
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Manage your existing
                                                currencies
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 12l3-3 3 3 4-4" />
                                        </svg>
                                        <span>{{ currencies.length }} {{ currencies.length === 1 ? 'Currency' :
                                            'Currencies'
                                            }}</span>
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div v-if="!currencies || currencies.length === 0" class="text-center py-16">
                                    <div
                                        class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-full mb-6">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No currencies
                                        yet</h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-6">Get started by creating your first
                                        currency
                                        entry</p>
                                    <div
                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl text-sm font-medium">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Use the form on the left to add currencies
                                    </div>
                                </div>

                                <!-- Currency Grid -->
                                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div v-for="(item, index) in currencies" :key="item.id"
                                        class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                                        <div
                                            class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        </div>
                                        <div class="relative p-6">
                                            <div class="flex items-start justify-between mb-4">
                                                <div class="flex items-center space-x-3">
                                                    <div
                                                        class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl text-white font-bold text-sm">
                                                        {{ item.symbol }}
                                                    </div>
                                                    <div>
                                                        <h4 class="font-semibold text-gray-900 dark:text-white">{{
                                                            item.name }}
                                                        </h4>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">#{{ index +
                                                            1 }}</p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                    <button @click="editCurrency(item.id)"
                                                        class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors duration-200">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <button @click="deleteCurrency(item.id)"
                                                        class="flex items-center justify-center w-8 h-8 bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-800 transition-colors duration-200">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-500 dark:text-gray-400">Symbol</span>
                                                    <span
                                                        class="font-mono font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{
                                                        item.symbol }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-500 dark:text-gray-400">Status</span>
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                                                        Active
                                                    </span>
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
    </AuthenticatedLayout>
</template>