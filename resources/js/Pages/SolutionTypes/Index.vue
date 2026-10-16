<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import axios from 'axios';

const deleteRecord = async (id) => {
    swal.fire({
        title: 'Are you sure?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return;
        }
        try {
            let res = await axios.delete('/api/solution-types/' + id);
            if (res) {
                emitter.emit('datatable-refresh:solution-type-table');
            }
            notyf.success('Removed with success');
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        }
    });
};

const options = new DatatableOptions('solution-types', 'solution-type-table');
options.addColumn('id', '#');
options.addColumn('is_active', 'Active');
options.addColumn('solution_type.name', 'Name');
options.addColumn('created_at', 'Created At');
options.addColumn('updated_at', 'Updated At');
options.addColumn('solution_type.id', 'Solution Type Id', { isHidden: true });

options.addColumnFilter('is_active', 'checkbox');

options.setAllowedActions({
    allowExport: can('export solution-type') || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Solution Types' }]);
</script>
<template>
    <Head title="Solution Types" />
    <AuthenticatedLayout>
        <Header title="Solution Types" :breadcrumbs="items">
            <template #buttons>
                <Link href="/solution-types/create" id="solution-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Solution Type
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
            <template #row-actions="{ data, index, close }">
                <Link
                    :id="'datatable-btn-' + index + '-edit'"
                    :href="'/solution-types/' + data.solution_type_id + '/edit'"
                    class="datatable-actions-item"
                >
                    <font-awesome-icon :icon="['fas', 'pen-to-square']" class="me-2" />
                    Edit
                </Link>
                <button
                    type="button"
                    :id="'datatable-btn-' + index + '-delete'"
                    class="datatable-actions-item datatable-actions-item-danger"
                    @click="
                        () => {
                            deleteRecord(data.solution_type_id);
                            close();
                        }
                    "
                >
                    <font-awesome-icon :icon="['fas', 'trash']" class="me-2" />
                    Delete
                </button>
            </template>
            <template #is_active="{ data, value }">
                <ToggleActive :data="data.solution_type" :value="value" route="solution-types" />
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
