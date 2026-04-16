<script setup>
import { Link } from '@inertiajs/vue3';
import { useColors } from '@/Composables/layouts.js';

useColors();
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <Link href="/"> </Link>
        </div>

        <nav class="navbar navbar-expand-lg bg-theme-secondary-dark">
            <div class="container d-flex justify-space-between">
                <a class="navbar-brand" href="/">
                    <AppLogo />
                </a>
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </nav>

        <main>
            <div class="container">
                <slot />
            </div>
        </main>

        <Footer></Footer>
    </div>
</template>
