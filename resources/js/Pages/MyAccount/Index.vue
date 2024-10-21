<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Permissions from "@/Components/Permissions.vue";
import { Head, Link } from "@inertiajs/vue3";
import { can, hasRole } from '@composables/useAuth'
import { ref } from 'vue';

defineProps({
    permissions: Array,
    tenants: Array
})
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account' },
]);


</script>

<template>
    <Head title="My Account" />
    <AuthenticatedLayout>

        <div class="panel">
            <Header title="My Account" :breadcrumbs="items">
                <template #buttons>
                    <form @submit.prevent="submit">
                        <Link :href="route('logout')" method="post" as="button" class="btn btn-primary px-4">Log Out</Link>
                    </form>
                </template>
            </Header>
            <div class="row">
                <div class="col-4">

                    <div class="text-center">
                        <font-awesome-icon class="bg-light p-5 rounded-circle text-secondary" :icon="['fas', 'user']" size="4x"></font-awesome-icon>
                    </div>
                    <div class="text-center mt-3">
                        <span v-for="role in $page.props.auth.roles" class="badge bg-secondary p-1 px-2 rounded text-white">{{ role }}</span>
                        <h5 class="mt-2 mb-0">{{ $page.props.auth.user.name }}</h5>
                        <span>{{ $page.props.auth.user.email }}</span>

                        <div v-if="true" class="buttons mt-3">
                            <Link class="btn btn-outline-primary px-4" :href="route('profile.edit')">Edit Profile</Link>
                        </div>

                        <hr class="login-seperator">
                        <h5 class="mt-4">Company Accounts</h5>
                        <ul class="nav flex-column">
                            <li v-for="tenant in tenants">
                                <Link :href="route('tenant.make-current', { tenant: tenant.id })">
                                {{ tenant.name }} (T{{ tenant.id }})
                                </Link>
                            </li>
                        </ul>
                        <hr class="login-seperator">
                        <h5 class="mt-4">User Permissions</h5>
                        <div class="px-2">
                            <Permissions :permissions="permissions" />
                        </div>
                    </div>
                </div>
                <div class="col-8">

                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <div class="col" v-if="hasRole('Super Admin') || can('viewAny account')">
                            <div class="card bg-light border-0">
                                <a :href="route('accounts.index')" class="card-body text-center p-2 text-decoration-none">
                                    <font-awesome-icon class="py-3 opacity-25" :icon="['far', 'address-card']" size="2x"></font-awesome-icon>
                                    <span class="d-block h5">Accounts</span>
                                </a>
                            </div>
                        </div>
                        <div class="col" v-if="hasRole('Super Admin') || can('viewAny brand')">
                            <div class="card bg-light border-0">
                                <a :href="route('brands.index')" class="card-body text-center p-2 text-decoration-none">
                                    <font-awesome-icon class="py-3 opacity-25" :icon="['far', 'registered']" size="2x"></font-awesome-icon>
                                    <span class="d-block h5">Brands</span>
                                </a>
                            </div>
                        </div>
                        <div class="col" v-if="hasRole('Super Admin') || can('viewAny price')">
                            <div class="card bg-light border-0">
                                <a :href="route('prices.index')" class="card-body text-center p-2 text-decoration-none">
                                    <font-awesome-icon class="py-3 opacity-25" :icon="['far', 'money-bill-1']" size="2x"></font-awesome-icon>
                                    <span class="d-block h5">Prices</span>
                                </a>
                            </div>
                        </div>
                        <div class="col" v-if="hasRole('Super Admin') || can('viewAny product')">
                            <div class="card bg-light border-0">
                                <a :href="route('products.index')" class="card-body text-center p-2 text-decoration-none">
                                    <font-awesome-icon class="py-3 opacity-25" :icon="['far', 'rectangle-list']" size="2x"></font-awesome-icon>
                                    <span class="d-block h5">Products</span>
                                </a>
                            </div>
                        </div>
                        <div class="col" v-if="hasRole('Super Admin') || can('viewAny region')">
                            <div class="card bg-light border-0">
                                <a :href="route('regions.index')" class="card-body text-center p-2 text-decoration-none">
                                    <font-awesome-icon class="py-3 opacity-25" :icon="['far', 'map']" size="2x"></font-awesome-icon>
                                    <span class="d-block h5">Regions</span>
                                </a>
                            </div>
                        </div>
                        <div class="col" v-if="hasRole('Super Admin') || can('viewAny spa')">
                            <div class="card bg-light border-0">
                                <a :href="route('spas.index')" class="card-body text-center p-2 text-decoration-none">
                                    <font-awesome-icon class="py-3 opacity-25" :icon="['far', 'handshake']" size="2x"></font-awesome-icon>
                                    <span class="d-block h5">SPAs</span>
                                </a>
                            </div>
                        </div>
                        <div class="col" v-if="hasRole('Super Admin') || can('viewAny supplier')">
                            <div class="card bg-light border-0">
                                <a :href="route('suppliers.index')" class="card-body text-center p-2 text-decoration-none">
                                    <font-awesome-icon class="py-3 opacity-25" :icon="['far', 'handshake']" size="2x"></font-awesome-icon>
                                    <span class="d-block h5">Suppliers</span>
                                </a>
                            </div>
                        </div>
                        <div class="col" v-if="hasRole('Super Admin') || can('viewAny tenant')">
                            <div class="card bg-light border-0">
                                <a :href="hasRole('Super Admin') ? route('tenant.index') : route('tenant.edit', { tenant: tenant.id })" class="card-body text-center p-2 text-decoration-none">
                                    <font-awesome-icon class="py-3 opacity-25" :icon="['far', 'building']" size="2x"></font-awesome-icon>
                                    <span class="d-block h5">Tenants</span>
                                </a>
                            </div>
                        </div>
                        <div class="col" v-if="hasRole('Super Admin') || can('viewAny token')">
                            <div class="card bg-light border-0">
                                <a href="#" class="card-body text-center p-2 text-decoration-none">
                                    <font-awesome-icon class="py-3 opacity-25" :icon="['far', 'bookmark']" size="2x"></font-awesome-icon>
                                    <span class="d-block h5">Tokens</span>
                                </a>
                            </div>
                        </div>
                        <div class="col" v-if="hasRole('Super Admin') || can('viewAny user')">
                            <div class="card bg-light border-0">
                                <a :href="route('users.index')" class="card-body text-center p-2 text-decoration-none">
                                    <font-awesome-icon class="py-3 opacity-25" :icon="['far', 'user']" size="2x"></font-awesome-icon>
                                    <span class="d-block h5">Users</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
    .login-seperator {
        content: '';
        width: 80%;
        margin-left:auto;
        margin-right:auto;
        height:1px;
        background-color:var(--light-grey);
        position: relative;
        overflow: hidden
    }

</style>
