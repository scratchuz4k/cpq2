<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';

const options = new DatatableOptions('roles', 'roles-table');
options.addColumn('id', '#');
options.addColumn('name', 'Name');
options.addColumn('parent.name', 'Parent');
options.addColumn('created_at', 'Created At');
options.addColumn('updated_at', 'Updated At');

options.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can('export role') || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Roles' }]);
</script>
<template>
    <Head title="Roles" />
    <AuthenticatedLayout>
        <Header title="Roles" :breadcrumbs="items">
            <template #buttons>
                <Link href="/roles/create" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Role
                </Link>
            </template>
        </Header>
        <NewDatatable :options="options">
            <template #parent.name="{ value }">
                <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm">
                    {{ value }}
                </span>
            </template>
            <template #updated_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #created_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
