<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';

const options = new DatatableOptions('apis', 'apis-table');
options.addColumn('id', '#');
options.addColumn('is_active', 'Active');
options.addColumn('code', 'Code');
options.addColumn('name', 'Name');
options.addColumn('auth_type', 'Auth Type');
options.addColumn('created_at', 'Created At');
options.addColumn('updated_at', 'Updated At');

options.addColumnFilter('is_active', 'checkbox');

options.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can('export api') || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'APIs' }]);
</script>
<template>
    <Head title="APIs" />
    <AuthenticatedLayout>
        <Header title="APIs" :breadcrumbs="items">
            <template #buttons>
                <Link href="/apis/create" id="api-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New API
                </Link>
            </template>
        </Header>
        <NewDatatable :options="options">
            <template #auth_type="{ value }">
                <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm">{{ value }}</span>
            </template>
            <template #updated_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #created_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #is_active="{ data, value }">
                <ToggleActive :data="data" :value="value" route="apis" />
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
