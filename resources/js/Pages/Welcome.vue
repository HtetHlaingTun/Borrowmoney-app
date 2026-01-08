<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

function handleImageError() {
    document.getElementById('background')?.classList.add('hidden');
}
</script>

<template>

    <Head title="Welcome" />

    <div class="relative min-h-screen bg-gray-50 dark:bg-black text-gray-800 dark:text-white overflow-hidden">

        <!-- Background Image -->
        <img id="background" src="/BG.jpg" alt="Background"
            class="fixed inset-0 w-full h-full object-cover object-center z-0" @error="handleImageError" />

        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/60 z-10"></div>

        <!-- Header -->
        <header
            class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-black/70 backdrop-blur-md border-b border-gray-200 dark:border-white/10 text-sm">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <img src="/MD.png" alt="Logo" class="h-10 w-auto" @error="handleImageError" />
                    </div>

                    <!-- Navigation -->
                    <nav v-if="canLogin" class="flex items-center space-x-4">
                        <Link v-if="$page.props.auth.user" :href="route('admin.dashboard')"
                            class="px-4 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('login')"
                                class="px-4 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            Log in
                            </Link>
                            <Link v-if="canRegister" :href="route('register')"
                                class="px-4 py-2 bg-[#FF2D20] text-white rounded-md hover:bg-[#e6221a] transition">
                            Register
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="relative z-20 pt-32 pb-20 px-4">
            <div class="max-w-3xl mx-auto text-center space-y-6">
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white">
                    Welcome to the Luckey Platform
                </h1>
                <p class="text-lg sm:text-xl text-gray-200">
                    Secure, modern, and powerful borrowing system made for your needs.
                </p>

                <!-- Optional CTA Buttons -->
                <div class="mt-6 space-x-4">
                    <Link href="/login"
                        class="inline-block px-6 py-3 bg-white text-gray-800 font-semibold rounded-lg shadow hover:bg-gray-100 transition">
                    Get Started
                    </Link>

                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer
            class="fixed bottom-0 left-0 right-0 z-40 bg-white/80 dark:bg-black/70 backdrop-blur-md border-t border-gray-200 dark:border-white/10 py-4 text-center text-sm text-gray-600 dark:text-white/70">
            Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }}) — Developed by <strong>HTET HLAING TUN</strong>
        </footer>
    </div>
</template>
