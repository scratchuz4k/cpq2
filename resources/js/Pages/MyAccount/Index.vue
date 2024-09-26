<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Permissions from "@/Components/Permissions.vue";
import { Link } from "@inertiajs/vue3";
defineProps({
    permissions: Array,
    tenants: Array
})

</script>

<template>
    <AuthenticatedLayout>
        <div class="panel">
            <div class="containter">
                <h1>My Account</h1>
                <div class="row">
                    <div class="col">

                        <div class="mb-3">
                            <p>Your email: {{ $page.props.auth.user.email }}</p>
                            <hr class="dropdown-divider">

                            <form @submit.prevent="submit">
                                <Link :href="route('logout')" method="post" as="button" class="btn btn-danger">Log Out
                                </Link>
                            </form>

                        </div>

                        <h2 v-if="true">Company Accounts</h2>
                        <ul>
                            <li v-for="tenant in tenants"> {{ tenant.name }} (T{{ tenant.id }})</li>
                        </ul>

                        <h2>User Permissions</h2>
                        <Permissions :permissions="permissions" />

                        <h2>User Info</h2>
                        <p>UID: {{ $page.props.auth.user.id }}</p>
                    </div>
                    <div class="col">
                        <h4>
                            <a :href="route('tenant.index')">
                                Admin
                            </a>
                        </h4>
                        <ul>
                            <li>
                                <Link :href="route('accounts.index')">Accounts</Link>
                            </li>
                            <li>Brands</li>
                            <li>Prices</li>
                            <li>Products</li>
                            <li>Regions</li>
                            <li>Suppliers</li>
                            <li>SPAs</li>
                            <li>Users</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
