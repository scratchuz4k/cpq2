<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
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

    <main class="d-flex flex-grow-1">
        <div class="container flex-grow-1">
            <slot />
        </div>
    </main>

    <Footer></Footer>
</template>
