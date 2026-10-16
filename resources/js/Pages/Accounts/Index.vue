<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';

const props = defineProps({
    imports: {
        type: Array,
        default: []
    }
});

const options = new DatatableOptions('accounts', 'accounts-table');
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
    allowExport: can('export account') || hasRole('Super Admin'),
    allowImport: true
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Accounts' }]);
</script>

<template>
    <Head title="Accounts" />
    <AuthenticatedLayout>
        <Header title="Accounts" :breadcrumbs="items">
            <template #buttons>
                <Link href="/accounts/create" id="account-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Account
                </Link>
            </template>
        </Header>
        <NewDatatable :options="options" :imports="props.imports">
            <template #updated_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #created_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #is_active="{ data, value }">
                <ToggleActive :data="data" :value="value" route="accounts" />
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
