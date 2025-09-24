<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Permissions from '@/Components/Permissions.vue';
import { Head, Link } from '@inertiajs/vue3';
import { can, hasRole } from '@composables/useAuth';
import { ref } from 'vue';

defineProps({
    permissions: Array,
    tenants: Array
});

const options = [
    {
        title: 'Accounts',
        url: route('accounts.index'),
        has_permission: hasRole('Super Admin') || can('viewAny account'),
        icon: ['far', 'address-card']
    },
    {
        title: 'Brands',
        url: route('brands.index'),
        has_permission: hasRole('Super Admin') || can('viewAny brand'),
        icon: ['far', 'registered']
    },
    {
        title: 'Categories',
        url: route('categories.index'),
        has_permission: hasRole('Super Admin') || can('viewAny category'),
        icon: ['far', 'folder']
    },
    {
        title: 'Products',
        url: route('products.index'),
        has_permission: hasRole('Super Admin') || can('viewAny product'),
        icon: ['far', 'rectangle-list']
    },
    {
        title: 'Regions',
        url: route('regions.index'),
        has_permission: hasRole('Super Admin') || can('viewAny region'),
        icon: ['far', 'map']
    },
    {
        title: 'Solutions',
        url: route('solution-types.index'),
        has_permission: hasRole('Super Admin') || can('viewAny solution_type'),
        icon: ['fas', 'circle-nodes']
    },
    {
        title: 'SPAs',
        url: route('spas.index'),
        has_permission: hasRole('Super Admin') || can('viewAny spa'),
        icon: ['far', 'handshake']
    },
    {
        title: 'Suppliers',
        url: route('suppliers.index'),
        has_permission: hasRole('Super Admin') || can('viewAny supplier'),
        icon: ['fas', 'industry']
    },
    {
        title: 'Merchants',
        url: route('merchants.index'),
        has_permission: hasRole('Super Admin') || can('viewAny merchant'),
        icon: ['fas', 'shop']
    },
    {
        title: 'Tenant',
        url: hasRole('Super Admin')
            ? route('tenants.index')
            : route('tenants.edit', { tenant: $page.props.auth.user.current_tenant.id }),
        has_permission: hasRole('Super Admin') || can('viewAny tenant'),
        icon: ['far', 'building']
    },
    {
        title: 'Tokens',
        url: route('tokens.index'),
        has_permission: hasRole('Super Admin') || can('viewAny token'),
        icon: ['far', 'bookmark']
    },
    {
        title: "Api's",
        url: route('apis.index'),
        has_permission: hasRole('Super Admin') || can('viewAny api'),
        icon: ['fas', 'cloud']
    },
    {
        title: 'Users',
        url: route('users.index'),
        has_permission: hasRole('Super Admin') || can('viewAny user'),
        icon: ['far', 'user']
    },
    {
        title: 'Roles',
        url: route('roles.index'),
        has_permission: hasRole('Super Admin') || can('viewAny role'),
        icon: ['far', 'id-badge']
    },
    {
        title: "Email's",
        url: route('email-templates.index'),
        has_permission: hasRole('Super Admin') || can('viewAny email_template'),
        icon: ['fas', 'envelope']
    },
    {
        title: 'Reporting',
        url: route('reporting'),
        has_permission: hasRole('Super Admin') || can('viewAny export'),
        icon: ['fas', 'gauge']
    }
];
const items = ref([{ label: 'Home', url: '/' }, { label: 'My Account' }]);
</script>

<template>
    <Head title="My Account" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="My Account" :breadcrumbs="items">
                <template #buttons>
                    <form @submit.prevent="submit">
                        <Link :href="route('logout')" method="post" as="button" class="btn btn-primary px-4"
                            >Log Out
                        </Link>
                    </form>
                </template>
            </Header>
            <div class="row">
                <div class="col-4">
                    <div class="text-center">
                        <font-awesome-icon
                            class="bg-light p-5 rounded-circle text-secondary"
                            :icon="['fas', 'user']"
                            size="4x"
                        ></font-awesome-icon>
                    </div>
                    <div class="text-center mt-3">
                        <span
                            v-for="role in $page.props.auth.roles"
                            class="badge bg-secondary p-1 px-2 rounded text-white"
                            >{{ role }}</span
                        >
                        <h5 class="mt-2 mb-0">
                            {{ $page.props.auth.user.name }}
                        </h5>
                        <span>{{ $page.props.auth.user.email }}</span>

                        <div v-if="true" class="buttons mt-3">
                            <Link class="btn btn-outline-primary px-4" :href="route('profile.edit')">Edit Profile</Link>
                        </div>

                        <hr class="login-seperator" />
                        <h5 class="mt-4">Company Accounts</h5>
                        <ul class="nav flex-column">
                            <li v-for="tenant in tenants">
                                <Link
                                    :href="
                                        route('tenant.make-current', {
                                            tenant: tenant.id
                                        })
                                    "
                                >
                                    {{ tenant.name }} (T{{ tenant.id }})
                                </Link>
                            </li>
                        </ul>
                        <hr class="login-seperator" />
                        <h5 class="mt-4">User Permissions</h5>
                        <div class="px-2">
                            <Permissions :permissions="permissions" />
                        </div>
                    </div>
                </div>
                <div class="col-8">
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <div v-for="option in options.filter((x) => x.has_permission)" class="col">
                            <div class="card bg-light border-0">
                                <a
                                    :id="'link-' + format.toLowerCase(option.title)"
                                    :href="option.url"
                                    class="card-body text-center p-2 text-decoration-none"
                                >
                                    <font-awesome-icon
                                        class="py-3 opacity-25"
                                        :icon="option.icon"
                                        size="2x"
                                    ></font-awesome-icon>
                                    <span class="d-block h5">{{ option.title }}</span>
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
    margin-left: auto;
    margin-right: auto;
    height: 1px;
    background-color: var(--light-grey);
    position: relative;
    overflow: hidden;
}
</style>
