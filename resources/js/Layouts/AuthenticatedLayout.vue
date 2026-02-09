<script setup>
import _ from 'lodash';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import * as Sentry from '@sentry/vue';
import TopBarSearchable from '@/Components/TopBarSearchable.vue';
import Resources from '@/Components/Resources.vue';
import { can, hasRole } from '@/Composables/useAuth.js';
import StepActionBar from '@/Components/Steps/StepActionBar.vue';

const page = usePage();
const user = page.props.auth.user;
Sentry.setUser({
    fullName: user.name,
    email: user.email
});

defineProps({
    quote: Object,
    order: Object,
    showStepActionBar: Boolean
});

const activeTab = ref(window.location.pathname.replace(/^\/|\/$/g, ''));
const currentTenant = ref(page.props.tenant);
</script>

<template>
    <nav class="navbar navbar-expand-md bg-theme-secondary-dark">
        <div class="container d-flex justify-space-between">
            <a class="navbar-brand" href="/">
                <ApplicationLogo />
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
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav navme-auto mb-2 mb-lg-0">
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">CUSTOMERS</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">ASSETS</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" aria-current="page" :href="route('quotes.index')">QUOTES</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">BILLING</a>
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
                        <a class="nav-link" id="quotes-tab" data-toggle="tab" :href="route('quotes.index')">QUOTES</a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link"
                            :class="{ active: $page.component == 'Settings/Index' }"
                            id="settings"
                            data-toggle="tab"
                            :href="route('settings')"
                        >
                            SETTINGS
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav mb-2 mb-lg-0 d-flex justify-content-end">
                    <li class="nav-item me-2">
                        <TopBarSearchable></TopBarSearchable>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center h-100"
                            role="button"
                            v-tooltip
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            :title="currentTenant.name + ' (T' + currentTenant.id + ')'"
                        >
                            <font-awesome-icon :icon="['far', 'building']" />
                        </a>
                    </li>
                    <li class="nav-item" v-if="hasRole('Super Admin') || can('viewAny notification')">
                        <NotificationDropdown></NotificationDropdown>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center h-100"
                            :href="route('profile.index')"
                            v-tooltip
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            :title="user.name"
                        >
                            <font-awesome-icon :icon="['far', 'user']" />
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Page Content -->
    <main>
        <div class="container">
            <slot />
        </div>
    </main>
    <StepActionBar v-if="showStepActionBar">
        <slot name="actionBar"></slot>
        <!--        <template #content>-->
        <!--            <slot name="actionContent" />-->
        <!--        </template>-->
        <!--        <template #left>-->
        <!--            <slot name="actionBarLeft" />-->
        <!--        </template>-->
        <!--        <template #right>-->
        <!--            <slot name="actionBarRight" />-->
        <!--        </template>-->
    </StepActionBar>
    <Footer></Footer>
    <Resources></Resources>
</template>
<style>
.search-button:focus-visible,
.search-button:hover {
    outline: none;
}

.search-control {
    padding: 10px;
    border-radius: 5px;
    border: 1px solid whitesmoke;
}

.search-control:hover {
    border: 1px solid black;
}

.search-title {
    font-size: 0.85em;
    font-weight: 600;
    line-height: 32px;
    margin: 0;
}

.search-option {
    text-decoration: none;
    color: black;
    font-size: 0.5em;
    cursor: pointer;
    margin-bottom: 5px;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid whitesmoke;
    display: flex;
    align-items: center;
}

.search-option p {
    font-size: 14px;
}

.search-option:hover {
    color: white;
    background: black;
}

.search-option .search-option-icon {
    font-size: 1.5em;
}
</style>
