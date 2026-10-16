<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';

const options = new DatatableOptions('tokens', 'tokens-table');
options.addColumn('id', '#');
options.addColumn('is_active', 'Active');
options.addColumn('name', 'Name');
options.addColumn('entity.name', 'Entity');
options.addColumn('last_used_at', 'Last Used At');
options.addColumn('expires_at', 'Expires At');
options.addColumn('created_at', 'Created At');

options.addColumnFilter('is_active', 'checkbox');
options.setSort('desc', 'created_at');

options.setAllowedActions({
    link: true,
    destroy: true,
    allowExport: can('export token') || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Tokens' }]);
</script>

<template>
    <Head title="Tokens" />
    <AuthenticatedLayout>
        <Header title="Tokens" :breadcrumbs="items">
            <template #buttons>
                <Link :href="route('tokens.create')" id="token-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Token
                </Link>
            </template>
        </Header>
        <NewDatatable :options="options">
            <template #entity.name="{ value }">
                <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm">{{ value }}</span>
            </template>
            <template #last_used_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #expires_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #created_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #is_active="{ data, value }">
                <ToggleActive :data="data" :value="value" route="tokens" />
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
