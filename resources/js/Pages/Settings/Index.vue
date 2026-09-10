<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { can, hasRole } from '@composables/useAuth';
import { ref } from 'vue';

import { usePage } from '@inertiajs/vue3';

const { props } = usePage();

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
        title: 'Solutions',
        url: route('solution-types.index'),
        has_permission: hasRole('Super Admin') || can('viewAny solution_type'),
        icon: ['fas', 'circle-nodes']
    },
    {
        title: 'Steps',
        url: route('steps.index'),
        has_permission: hasRole('Super Admin') || can('viewAny step'),
        icon: ['fa', 'stairs']
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
        title: 'Tenant',
        url: hasRole('Super Admin')
            ? route('tenants.index')
            : route('tenants.edit', { tenant: props.auth.user.current_tenant.id }),
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
        title: 'APIs',
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
        has_permission: hasRole('Super Admin') || can('viewAny report'),
        icon: ['fas', 'gauge']
    },
    {
        title: 'Imports',
        url: route('imports.index'),
        has_permission: hasRole('Super Admin') || can('viewAny import'),
        icon: ['fas', 'file-import']
    }
];
const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings' }]);
</script>

<template>
    <Head title="Settings" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Settings" :breadcrumbs="items"></Header>
            <div class="row">
                <div class="col-12">
                    <div class="row row-cols-1 row-cols-md-4 g-4">
                        <div v-for="option in options.filter((x) => x.has_permission)" class="col">
                            <div class="card bg-light border-0">
                                <a
                                    :id="'link-' + format.toLowerCase(option.title)"
                                    :href="option.url"
                                    class="card-body text-center py-3 px-2 text-decoration-none"
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
