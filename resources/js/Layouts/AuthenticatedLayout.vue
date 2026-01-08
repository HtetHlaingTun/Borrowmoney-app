<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <nav class="border-b border-gray-100 bg-white dark:bg-gray-800 dark:border-gray-700 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <!-- Logo -->
                        <Link :href="route('admin.dashboard')" class="flex items-center">
                        <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                        </Link>

                        <!-- Desktop Nav Links -->
                        <div class="hidden sm:flex space-x-8">
                            <NavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                                Dashboard
                            </NavLink>
                            <NavLink :href="route('admin.currency')" :active="route().current('admin.currency')">
                                Currency
                            </NavLink>
                            <NavLink :href="route('admin.user')" :active="route().current('admin.user')">
                                User
                            </NavLink>
                            <NavLink :href="route('admin.borrow')" :active="route().current('admin.borrow')">
                                Borrow
                            </NavLink>
                            <NavLink :href="route('admin.interest')" :active="route().current('admin.interest')">
                                Interest
                            </NavLink>
                            <NavLink :href="route('admin.repayment')" :active="route().current('admin.repayment')">
                                Repayment
                            </NavLink>

                            <!-- Report Dropdown -->
                            <Dropdown align="left" class="mt-0">
                                <template #trigger>
                                    <button type="button"
                                        class="inline-flex items-center rounded-md border border-transparent bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white focus:outline-none transition">
                                        Report
                                        <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('admin.report.interest')"
                                        :active="route().current('admin.report.interest')">
                                        Interest
                                    </DropdownLink>
                                    <DropdownLink :href="route('admin.report.repayment')"
                                        :active="route().current('admin.report.repayment')">
                                        Repayment
                                    </DropdownLink>
                                    <DropdownLink :href="route('admin.profit.monthly')"
                                        :active="route().current('admin.profit.monthly')">
                                        Profit Monthly
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4">
                        <!-- Dark Mode Toggle -->
                        <DarkModeToggle />

                        <!-- Settings Dropdown -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-transparent bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white focus:outline-none transition">
                                    {{ $page.props.auth.user.name }}
                                    <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    <i class="fas fa-user-circle mr-2 text-secondary"></i> Profile
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    <i class="fas fa-sign-out-alt mr-2 text-secondary"></i> Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>

                        <!-- Hamburger for Mobile -->
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="sm:hidden inline-flex items-center justify-center rounded-md p-2 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-900 hover:text-gray-500 dark:hover:text-gray-400 focus:outline-none transition"
                            aria-label="Toggle Navigation">
                            <svg v-if="!showingNavigationDropdown" class="h-6 w-6" stroke="currentColor" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div v-show="showingNavigationDropdown" class="sm:hidden border-t border-gray-200 dark:border-gray-700">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')"
                        @click="showingNavigationDropdown = false">
                        Dashboard
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.currency')" :active="route().current('admin.currency')"
                        @click="showingNavigationDropdown = false">
                        Currency
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.user')" :active="route().current('admin.user')"
                        @click="showingNavigationDropdown = false">
                        User
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.borrow')" :active="route().current('admin.borrow')"
                        @click="showingNavigationDropdown = false">
                        Borrow
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.interest')" :active="route().current('admin.interest')"
                        @click="showingNavigationDropdown = false">
                        Interest
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.repayment')" :active="route().current('admin.repayment')"
                        @click="showingNavigationDropdown = false">
                        Repayment
                    </ResponsiveNavLink>

                    <!-- Report Links -->
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2">
                        <ResponsiveNavLink :href="route('admin.report.interest')"
                            :active="route().current('admin.report.interest')"
                            @click="showingNavigationDropdown = false">
                            Interest Report
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.report.repayment')"
                            :active="route().current('admin.report.repayment')"
                            @click="showingNavigationDropdown = false">
                            Repayment Report
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.profit.monthly')"
                            :active="route().current('admin.profit.monthly')"
                            @click="showingNavigationDropdown = false">
                            Monthly Profit
                        </ResponsiveNavLink>
                    </div>
                </div>

                <!-- Mobile Settings -->
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-4 space-y-1">
                    <div class="font-medium text-gray-800 dark:text-gray-200">
                        {{ $page.props.auth.user.name }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                        {{ $page.props.auth.user.email }}
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Dark Mode</span>
                        <DarkModeToggle />
                    </div>

                    <ResponsiveNavLink :href="route('profile.edit')" @click="showingNavigationDropdown = false">
                        <i class="fas fa-user-circle mr-2 text-secondary"></i> Profile
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('logout')" method="post" as="button"
                        @click="showingNavigationDropdown = false">
                        <i class="fas fa-sign-out-alt mr-2 text-secondary"></i> Log Out
                    </ResponsiveNavLink>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <slot />
        </main>
    </div>
</template>
