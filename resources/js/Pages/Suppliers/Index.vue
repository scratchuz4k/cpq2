<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';

const options = new DatatableOptions('suppliers', 'suppliers-table');
options.addColumn('id', '#');
options.addColumn('is_active', 'Active');
options.addColumn('name', 'Name');
options.addColumn('reference', 'Reference');
options.addColumn('created_at', 'Created At');
options.addColumn('updated_at', 'Updated At');

options.addColumnFilter('is_active', 'checkbox');

options.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can('export supplier') || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Suppliers' }]);
</script>
<template>
    <Head title="Suppliers" />
    <AuthenticatedLayout>
        <Header title="Suppliers" :breadcrumbs="items">
            <template #buttons>
                <Link id="supplier-create-btn" href="/suppliers/create" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Supplier
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
                <ToggleActive :data="data" :value="value" route="suppliers" />
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
