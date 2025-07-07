<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
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
                emitter.emit('datatable-refresh');
            }
            notyf.success('Removed with success');

        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        }
    });
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Solution Types' }
]);
</script>
<template>
    <Head title="Solution Types" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Solution Types" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/solution-types/create" id="solution-create-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                        New Solution Type
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :table-props="{ id: 'solution-type-table' }"
                    :allow="{}"
                    :columns="[
                        { data: 'id', title: '#', style: 'width:5%' },
                        { data: 'is_active', title: 'Active', style: 'width:5%' },
                        { data: 'solution_type.code', title: 'Code', style: 'width:10%' },
                        { data: 'solution_type.name', title: 'Name', style: 'width:40%' },
                        { data: 'created_at', title: 'Created At', style: 'width:15%' },
                        { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                        { data: 'actions', title: '', style: 'width:13%' },
                        { data: 'solution_type.id', hide: true }
                    ]"
                    route="/solution-types"
                >
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #actions="{ data, value, index, getData }">
                        <div class="d-flex justify-content-end">
                            <Link
                                :id="'datatable-btn-' + index + '-edit'"
                                :href="'/solution-types' + '/' + data.solution_type_id + '/edit'"
                                class="btn px-2 py-0 mx-1 border-0"
                            >
                                <font-awesome-icon :icon="['fas', 'edit']" />
                            </Link>
                            <button
                                :id="'datatable-btn-' + index + '-delete'"
                                class="btn px-2 py-0 mx-1 border-0"
                                @click="deleteRecord(data.solution_type_id)"
                            >
                                <font-awesome-icon icon="fas fa-trash-alt" />
                            </button>
                        </div>
                    </template>
                    <template #is_active="{ data, value }">
                        <ToggleActive :data="data" :value="value" route="solution-types" />
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
