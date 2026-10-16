<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';

const props = defineProps({
    operators: Array
});
const options = new DatatableOptions('blacklists', 'blacklists-table');
options.addColumn('is_active', 'Active');
options.addColumn('productName', 'Name');
options.addColumn('providerKey', 'Provider');
options.addColumn('connectivitySupplierGroup', 'Subprovider');
options.addColumn('bearer', 'Bearer');
options.addColumn('speed', 'Speed');
options.addColumn('contractTermMonths', 'Renewal Term');
options.addColumn('created_at', 'Created At');
options.addColumn('updated_at', 'Updated At');
options.addColumnFilter('is_active', 'checkbox');
options.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can('export blacklist') || hasRole('Super Admin')
});

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Settings', url: '/settings' },
    { label: 'Products', url: '/products' },
    { label: 'Blacklists' }
]);
</script>
<template>
    <Head title="Blacklist" />
    <AuthenticatedLayout>
        <Header title="Blacklist" :breadcrumbs="items">
            <template #buttons>
                <Link href="/blacklists/create" id="blacklist-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Item
                </Link>
            </template>
        </Header>
        <NewDatatable :options="options">
            <template #bearer="{ value }">
                <span
                    v-if="!Array.isArray(value.value)"
                    v-tooltip
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    :title="
                        operators.find((op) => op.name === value?.operator)?.description +
                        ' ' +
                        value.value +
                        ' ' +
                        value.unit
                    "
                >
                    {{ value.value }} {{ value.unit }}
                </span>

                <span
                    v-else
                    v-tooltip
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    :title="
                        operators.find((op) => op.name === value?.operator)?.description +
                        ' ' +
                        value.value[0] +
                        ' and ' +
                        value.value[1] +
                        ' ' +
                        value.unit
                    "
                >
                    {{ value.value[0] }} to {{ value.value[1] }} {{ value.unit }}
                </span>
            </template>
            <template #speed="{ value }">
                <span
                    v-if="!Array.isArray(value.value)"
                    v-tooltip
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    :title="
                        operators.find((op) => op.name === value?.operator)?.description +
                        ' ' +
                        value.value +
                        ' ' +
                        value.unit
                    "
                >
                    {{ value.value }} {{ value.unit }}
                </span>

                <span
                    v-else
                    v-tooltip
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    :title="
                        operators.find((op) => op.name === value?.operator)?.description +
                        ' ' +
                        value.value[0] +
                        ' and ' +
                        value.value[1] +
                        ' ' +
                        value.unit
                    "
                >
                    {{ value.value[0] }} to {{ value.value[1] }} {{ value.unit }}
                </span>
            </template>
            <template #contractTermMonths="{ value }">
                <p class="small">{{ value ? value + ' months' : '' }}</p>
            </template>
            <template #updated_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #created_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #is_active="{ data, value }">
                <ToggleActive :data="data" :value="value" route="blacklists" />
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
