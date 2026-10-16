<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import ToggleActive from '@/Components/ToggleActive.vue';

const options = new DatatableOptions('categories', 'categories-table');
options.addColumn('id', '#');
options.addColumn('is_active', 'Active');
options.addColumn('code', 'Code');
options.addColumn('name', 'Name');
options.addColumn('parent.name', 'Parent');
options.addColumn('created_at', 'Created At');
options.addColumn('updated_at', 'Updated At');

options.addColumnFilter('is_active', 'checkbox');

options.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can('export category') || hasRole('Super Admin'),
    allowImport: true
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Categories' }]);
</script>
<template>
    <Head title="Categories" />
    <AuthenticatedLayout>
        <Header title="Categories" :breadcrumbs="items">
            <template #buttons>
                <Link href="/categories/create" id="category-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Category
                </Link>
            </template>
        </Header>
        <NewDatatable :options="options">
            <template #updated_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #created_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #is_active="{ data, value }">
                <ToggleActive :data="data" :value="value" route="categories" />
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
