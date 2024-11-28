<script setup>
import _ from "lodash";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from "@inertiajs/vue3";

const page = usePage();
import * as Sentry from "@sentry/vue";
Sentry.setUser({
    fullName: page.props.auth.user.name,
    email: page.props.auth.user.email,
});


defineProps({
    order: Object
})

</script>

<template>
    <AuthenticatedLayout>
        <nav class="navbar navbar-expand bg-theme-secondary-light">
            <div class="container">
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" id="dashboard-tab" data-toggle="tab" href="/" role="tab"
                                aria-controls="dashboard" aria-selected="false">ze</a>
                        </li>
                    </ul>
                    <TopBarSearchable></TopBarSearchable>
                </div>
            </div>
        </nav>
        <main>
            <div class="container ">
                <slot />
            </div>
        </main>
    </AuthenticatedLayout>
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
    font-size: .85em;
    font-weight: 600;
    line-height: 32px;
    margin: 0;
}

.search-option {
    text-decoration: none;
    color: black;
    font-size: .5m;
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
