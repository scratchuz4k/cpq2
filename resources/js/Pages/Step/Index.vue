<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';

const props = defineProps({
    solution_types: {
        type: Array,
        default: []
    }
});

const options = new DatatableOptions('steps', 'steps-table');
options.addColumn('id', '#');
options.addColumn('is_active', 'Active');
options.addColumn('name', 'Name');
options.addColumn('solution_type.name', 'Solution Type');
options.addColumn('created_at', 'Created At');
options.addColumn('updated_at', 'Updated At');

options.addColumnFilter('is_active', 'checkbox');
options.addColumnFilter(
    'solution_type.name',
    'select',
    props.solution_types.map((x) => x.name)
);

options.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can('export step') || hasRole('Super Admin'),
    allowImport: true
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Steps' }]);
</script>
<template>
    <Head title="Steps" />
    <AuthenticatedLayout>
        <Header title="Steps" :breadcrumbs="items">
            <template #buttons>
                <Link href="/steps/create" id="step-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Step
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
                <ToggleActive :data="data" :value="value" route="steps" />
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
