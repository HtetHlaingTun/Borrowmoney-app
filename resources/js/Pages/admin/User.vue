<!-- Vue Component -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, onUnmounted, computed } from 'vue';

const editingUserId = ref(null)
const page = usePage()
const currentUser = page.props.auth.user;
const props = defineProps({
    users: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
});

const showAlert = ref(false);
const alertMessage = ref('');
let alertTimeout = null;
const search = ref('');
const selectedRole = ref('');

const filteredUsers = computed(() => {
    return props.users.filter(user => {
        const matchesSearch = user.email.toLowerCase().includes(search.value.toLowerCase());
        const matchesRole = selectedRole.value ? user.role === selectedRole.value : true;
        return matchesSearch && matchesRole;
    });
});

onUnmounted(() => {
    if (alertTimeout) {
        clearTimeout(alertTimeout);
    }
});

function onSuccess(type = 'create') {
    form.reset();
    let message = 'User created successfully!';
    if (type === 'delete') message = 'User deleted successfully!';
    alertMessage.value = message;
    showAlert.value = true;
    if (alertTimeout) clearTimeout(alertTimeout);
    alertTimeout = setTimeout(() => {
        showAlert.value = false;
    }, 4000);
}

function handleSubmit() {
    form.post(route('admin.user.store'), {
        onSuccess: () => onSuccess('create'),
        preserveScroll: true,
    });
}

function closeAlert() {
    showAlert.value = false;
    if (alertTimeout) clearTimeout(alertTimeout);
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.user.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                onSuccess('delete');
                router.reload({ only: ['users'] });
            },
        });
    }
}
</script>

<template>

    <Head title="User Management" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-white">
                User Management
            </h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">

            <!-- Smart Toolbar -->
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-md shadow">
                <div class="flex flex-col md:flex-row gap-2 items-center">
                    <input type="text" v-model="search" placeholder="Search by email"
                        class="w-full md:w-64 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-indigo-500 focus:border-indigo-500" />

                    <select v-model="selectedRole"
                        class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
            </div>

            <!-- Alert -->
            <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <div v-if="showAlert"
                    class="rounded-md bg-green-100 dark:bg-green-900 border border-green-200 dark:border-green-800 p-4">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-green-700 dark:text-green-100">{{ alertMessage }}</p>
                        <button @click="closeAlert"
                            class="text-green-700 dark:text-green-100 hover:underline text-sm">Dismiss</button>
                    </div>
                </div>
            </Transition>

            <!-- Form -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Register New User</h3>
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm text-gray-700 dark:text-gray-300">Email</label>
                        <input id="email" type="email" v-model="form.email"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                        <div v-if="form.errors.email" class="text-sm text-red-500 mt-1">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label for="password" class="block text-sm text-gray-700 dark:text-gray-300">Password</label>
                        <input id="password" type="password" v-model="form.password"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                        <div v-if="form.errors.password" class="text-sm text-red-500 mt-1">{{ form.errors.password }}
                        </div>
                    </div>
                    <div>
                        <label for="password_confirmation"
                            class="block text-sm text-gray-700 dark:text-gray-300">Confirm
                            Password</label>
                        <input id="password_confirmation" type="password" v-model="form.password_confirmation"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                        <div v-if="form.errors.password_confirmation" class="text-sm text-red-500 mt-1">{{
                            form.errors.password_confirmation }}</div>
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full flex justify-center items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span v-else>Save</span>
                    </button>
                </form>
            </div>

            <!-- Users Table -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">User List</h3>
                <div v-if="filteredUsers.length === 0" class="text-center text-sm text-gray-500 dark:text-gray-400">
                    No users found.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    #
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Email</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Role</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="(item, index) in filteredUsers" :key="item.id">
                                <td class="px-4 py-2 text-sm text-gray-800 dark:text-white">{{ index + 1 }}</td>
                                <td class="px-4 py-2 text-sm text-gray-800 dark:text-white">{{ item.email }}</td>
                                <td class="px-4 py-2 text-sm text-gray-800 dark:text-white">{{ item.role }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <button @click="deleteUser(item.id)"
                                        :disabled="item.role === 'admin' || item.id === currentUser.id"
                                        class="text-red-600 dark:text-red-400 hover:underline disabled:opacity-40 disabled:cursor-not-allowed">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
