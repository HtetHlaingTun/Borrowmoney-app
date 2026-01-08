<script setup>
import { ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { Capacitor } from '@capacitor/core';

const showingNavigationDropdown = ref(false);

// Safe area insets for iOS
const safeAreaInsets = ref({
    top: 0,
    bottom: 0,
    left: 0,
    right: 0
});

// Check if running on native platform
const isNative = Capacitor.isNativePlatform();

// Configure safe areas on mount
onMounted(async () => {
    if (isNative && Capacitor.getPlatform() === 'ios') {
        try {
            // Try to get safe area from CSS environment variables
            const computedStyle = getComputedStyle(document.documentElement);
            const topInset = computedStyle.getPropertyValue('env(safe-area-inset-top)').replace('px', '');
            const bottomInset = computedStyle.getPropertyValue('env(safe-area-inset-bottom)').replace('px', '');

            safeAreaInsets.value = {
                top: parseInt(topInset) || 47, // Reduced fallback for iPhone 16 Pro Max
                bottom: parseInt(bottomInset) || 34,
                left: 0,
                right: 0
            };

            // Apply as CSS custom properties
            document.documentElement.style.setProperty('--safe-area-inset-top', `${safeAreaInsets.value.top}px`);
            document.documentElement.style.setProperty('--safe-area-inset-bottom', `${safeAreaInsets.value.bottom}px`);

        } catch (error) {
            console.error('Error getting safe area:', error);
            // Fallback values for iPhone 16 Pro Max
            safeAreaInsets.value = { top: 47, bottom: 34, left: 0, right: 0 };
        }
    }
});

// Handle status bar configuration
onMounted(async () => {
    if (isNative) {
        try {
            // Import StatusBar dynamically to avoid issues if not installed
            const { StatusBar, Style } = await import('@capacitor/status-bar');
            await StatusBar.setStyle({ style: Style.Default });
            await StatusBar.setBackgroundColor({ color: '#ffffff' });
        } catch (error) {
            console.log('StatusBar plugin not available:', error);
        }
    }
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 pb-14 sm:pb-0">
            <!-- Desktop Navigation (Top) - Hidden on Mobile -->
            <nav class="hidden sm:block border-b border-gray-100 bg-white/95 backdrop-blur-md dark:bg-gray-800/95 dark:border-gray-700"
                :style="{
                    paddingTop: isNative ? `${Math.max(safeAreaInsets.top - 12, 8)}px` : '8px',
                    paddingLeft: isNative ? `${safeAreaInsets.left}px` : '0',
                    paddingRight: isNative ? `${safeAreaInsets.right}px` : '0'
                }">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('admin.dashboard')">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('user.homePage')" :active="route().current('user.homePage')">
                                    Home
                                </NavLink>
                                <NavLink :href="route('user.borrow')" :active="route().current('user.borrow')">
                                    Borrowed Money
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            <i class="fas fa-user-circle me-2 text-secondary"></i> Profile
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            <i class="fas fa-sign-out-alt me-2 text-secondary"></i> Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Mobile Top Bar (Minimal) - Visible on Mobile Only -->
            <div class="sm:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between" :style="{
                paddingTop: isNative ? `${Math.max(safeAreaInsets.top + 12, 16)}px` : '12px',
                paddingLeft: isNative ? `${safeAreaInsets.left + 16}px` : '16px',
                paddingRight: isNative ? `${safeAreaInsets.right + 16}px` : '16px'
            }">
                <Link :href="route('admin.dashboard')" class="flex items-center">
                <ApplicationLogo class="block h-8 w-auto fill-current text-gray-800" />
                </Link>

                <!-- Profile Button -->
                <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                    class="flex items-center space-x-2 bg-gray-100 rounded-full px-3 py-2">
                    <div
                        class="w-6 h-6 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-xs">
                            {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <span class="text-sm font-medium text-gray-700 max-w-20 truncate">
                        {{ $page.props.auth.user.name.split(' ')[0] }}
                    </span>
                </button>
            </div>

            <!-- Mobile Profile Dropdown -->
            <div v-show="showingNavigationDropdown" class="sm:hidden fixed inset-0 z-[9999] flex items-end">
                <!-- Backdrop -->
                <div @click="showingNavigationDropdown = false"
                    class="absolute inset-0 bg-black bg-opacity-25 transition-opacity duration-300"></div>

                <!-- Dropdown Content -->
                <div class="relative w-full bg-white rounded-t-xl shadow-2xl transform transition-transform duration-300 ease-out"
                    :style="{
                        paddingBottom: isNative ? `${safeAreaInsets.bottom + 10}px` : '10px'
                    }">
                    <!-- Handle bar -->
                    <div class="flex justify-center pt-2 pb-1">
                        <div class="w-8 h-1 bg-gray-300 rounded-full"></div>
                    </div>

                    <!-- Profile Section -->
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold text-lg">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-base font-semibold text-gray-900 truncate">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="text-sm text-gray-500 truncate">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Items -->
                    <div class="py-1">
                        <ResponsiveNavLink :href="route('profile.edit')" @click="showingNavigationDropdown = false"
                            class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">Profile</div>
                                    <div class="text-xs text-gray-500">Manage your account</div>
                                </div>
                            </div>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink :href="route('logout')" method="post" as="button"
                            @click="showingNavigationDropdown = false"
                            class="w-full flex items-center px-4 py-3 hover:bg-red-50 transition-colors duration-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-red-600">Log Out</div>
                                    <div class="text-xs text-red-400">Sign out of your account</div>
                                </div>
                            </div>
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 min-h-screen relative">
                <!-- Page Heading -->
                <header class="bg-white shadow sticky top-0 sm:top-16 z-20" v-if="$slots.header">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main class="relative z-10 mb-5">
                    <slot />
                </main>
            </div>

            <!-- Mobile Bottom Navigation - Fixed at Bottom -->
            <nav class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-[9998] shadow-lg"
                :style="{
                    paddingBottom: isNative ? `${safeAreaInsets.bottom + 8}px` : '8px',
                    paddingLeft: isNative ? `${safeAreaInsets.left}px` : '0',
                    paddingRight: isNative ? `${safeAreaInsets.right}px` : '0'
                }">
                <div class="flex justify-around items-center py-1">
                    <!-- Home Tab -->
                    <Link :href="route('user.homePage')"
                        class="flex flex-col items-center justify-center px-2 py-2 min-w-0 flex-1 transition-colors duration-200">
                    <div class="relative">
                        <svg class="w-6 h-6 mb-1"
                            :class="route().current('user.homePage') ? 'text-lime-500' : 'text-gray-400'" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium"
                        :class="route().current('user.homePage') ? 'text-lime-500' : 'text-gray-400'">
                        Home
                    </span>
                    </Link>

                    <!-- Borrowed Money Tab -->
                    <Link :href="route('user.borrow')"
                        class="flex flex-col items-center justify-center px-2 py-2 min-w-0 flex-1 transition-colors duration-200">
                    <div class="relative">
                        <svg class="w-6 h-6 mb-1"
                            :class="route().current('user.borrow') ? 'text-lime-500' : 'text-gray-400'" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1z" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium"
                        :class="route().current('user.borrow') ? 'text-lime-500' : 'text-gray-400'">
                        Borrowed
                    </span>
                    </Link>





                    <!-- Profile Tab -->
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="flex flex-col items-center justify-center px-2 py-2 min-w-0 flex-1 transition-colors duration-200">
                        <div class="relative">
                            <!-- Profile circle with user initial -->
                            <div class="w-6 h-6 rounded-full mb-1 flex items-center justify-center"
                                :class="showingNavigationDropdown ? 'bg-lime-500' : 'bg-gray-400'">
                                <span class="text-white text-xs font-bold">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                        </div>
                        <span class="text-xs font-medium"
                            :class="showingNavigationDropdown ? 'text-lime-500' : 'text-gray-400'">
                            Profile
                        </span>
                    </button>
                </div>
            </nav>
        </div>
    </div>
</template>

<style scoped>
/* Enhanced mobile navigation */
@media (max-width: 640px) {

    /* Ensure hamburger menu is easily tappable */
    button {
        min-height: 44px;
        min-width: 44px;
    }

    /* Better touch targets for bottom navigation */
    .bottom-nav-item {
        min-height: 56px;
        min-width: 56px;
    }

    /* Prevent body scroll when menu is open */
    .navigation-open {
        overflow: hidden;
        position: fixed;
        width: 100%;
        height: 100%;
    }
}

/* Safe area support */
.safe-area-top {
    padding-top: var(--safe-area-inset-top, 0px);
}

.safe-area-bottom {
    padding-bottom: var(--safe-area-inset-bottom, 0px);
}

/* Fix for iOS Safari viewport units */
.min-h-screen {
    min-height: 100vh;
    min-height: -webkit-fill-available;
}

/* Ensure navigation stays on top with proper z-index hierarchy */
nav {
    z-index: 9998;
}

/* Mobile dropdown gets highest z-index */
.mobile-dropdown {
    z-index: 9999;
}

/* Prevent horizontal scroll on mobile */
body {
    overflow-x: hidden;
}

/* Bottom navigation styling */
@media (max-width: 640px) {

    /* Add padding to body to account for bottom nav */
    .pb-14 {
        padding-bottom: 3.5rem;
    }

    /* Smooth transitions for active states */
    .bottom-nav-item {
        transition: all 0.2s ease-in-out;
    }

    /* Active state styling */
    .bottom-nav-active {
        transform: translateY(-2px);
    }
}

/* Backdrop blur support */
@supports (backdrop-filter: blur(20px)) {
    .backdrop-blur-md {
        backdrop-filter: blur(20px);
    }
}
</style>