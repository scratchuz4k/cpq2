<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Permissions from "@/Components/Permissions.vue";
import { Link } from "@inertiajs/vue3";
import { can, hasRole } from '@composables/useAuth'
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

                        <h2>User Roles</h2>
                        <ul>
                            <li v-for="role in $page.props.auth.roles">{{ role }}</li>
                        </ul>

                        <h2>User Permissions</h2>
                        <Permissions :permissions="permissions" />

                        <h2>User Info</h2>
                        <p>UID: {{ $page.props.auth.user.id }}</p>
                    </div>
                    <div class="col">
                        <ul>
                            <li v-if="hasRole('Super Admin') || hasRole('Company Admin')">
                                <a v-if="hasRole('Super Admin')" :href="route('tenant.index')">
                                    Tenants
                                </a>
                                <a v-else :href="route('tenant.edit', { tenant: 1 })">
                                    Tenant
                                </a>
                            </li>
                            <li>
                                <Link :href="route('accounts.index')">Accounts</Link>
                            </li>
                            <li><Link :href="route('brands.index')">Brands</Link></li>
                            <li><Link :href="route('prices.index')">Prices</Link></li>
                            <li><Link :href="route('products.index')">Products</Link></li>
                            <li><Link :href="route('regions.index')">Regions</Link></li>
                            <li><Link :href="route('suppliers.index')">Suppliers</Link></li>
                            <li><Link :href="route('spas.index')">SPAs</Link></li>
                            <li><Link :href="route('users.index')">Users</Link></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
