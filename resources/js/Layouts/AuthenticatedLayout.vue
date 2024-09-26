<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    order: Object
})
</script>

<template>
    <nav class="navbar navbar-expand-md bg-theme-secondary-dark">
        <div class="container d-flex justify-space-between">
            <a class="navbar-brand" href="/">
                <ApplicationLogo />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav navme-auto mb-2 mb-lg-0">
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">CUSTOMERS</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">ASSETS</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link active" aria-current="page" href="#">QUOTES</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">ORDER</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">BILLING</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <font-awesome-icon :icon="['far', 'building']" />
                            {{ $page.props.auth.user.name }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" :href="route('myaccount')">
                            <font-awesome-icon :icon="['far', 'user']" />
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <font-awesome-icon :icon="['far', 'bell']" />
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <font-awesome-icon :icon="['far', 'bookmark']" />
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <nav class="navbar navbar-expand bg-theme-secondary-light">
        <div class="container">
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" id="dashboard-tab" data-toggle="tab" href="/" role="tab"
                            aria-controls="dashboard" aria-selected="false">DASHBOARD</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{ 'active': $page.component == 'Order/Create' }" id="create-tab"
                            data-toggle="tab" href="/orders/create" role="tab" aria-controls="create"
                            aria-selected="false">
                            <font-awesome-icon :icon="['fas', 'plus']" />
                            NEW
                        </a>
                    </li>
                    <li class="nav-item" v-if="order">
                        <Link :href="route('orders.configure', { order: order.id })" class="nav-link"
                            :class="{ 'active': $page.component == 'Order/Configure' }">CONFIGURE</Link>
                    </li>
                    <li class="nav-item" v-if="order">
                        <Link :href="route('orders.edit', { order: order.id })" class="nav-link"
                            :class="{ 'active': $page.component == 'Order/Edit' }">PRICE</Link>
                    </li>
                    <li class="nav-item" v-if="order">
                        <Link :href="route('orders.show', { order: order.id })" class="nav-link"
                            :class="{ 'active': $page.component == 'Order/Show' }">QUOTE</Link>
                    </li>
                </ul>
                <form class="d-md-flex d-none" role="search">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Type to search">
                        <button class="btn btn-primary" type="button" id="button-addon2">
                            <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </nav>
    <!-- Page Content -->
    <main>
        <div class="container ">
            <slot />
        </div>
    </main>
    <Footer></Footer>
</template>
