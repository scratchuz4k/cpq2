<script setup>
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import {ref} from "vue";
import {assignOrder, createOrder} from "@/Composables/orders.js";
import Button from "primevue/button";
DataTable.use(DataTablesCore);
const props = defineProps({
    users: {
        type: Array,
        default: []
    },
    solutions: {
        type: Array,
        default: []
    },
    accounts: {
        type: Array,
        default: []
    },
    status: {
        type: Array,
        default: []
    },
})

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Orders' },
]);
</script>

<template>
    <Head title="Orders" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Orders" :breadcrumbs="items"></Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable :tableProps="{id:'dashboard-table'}" :allow="{
                    link: true
                }" :columns="[
                    { data: 'id', title: '#', style: 'width:5%' },
                    { data: 'account.name', title: 'Account', style: 'width:15%' },
                    { data: 'solution_type.name', title: 'Solution', style: 'width:25%' },
                    { data: 'user.name', title: 'Owner', style: 'width:20%' },
                    { data: 'status', title: 'Status', style: 'width:10%' },
                    { data: 'updated_at', title: 'Last Updated', style: 'width:15%' },
                    { data: 'assign_order', title: ''},
                    { data: 'items.data', title: 'Last Updated', hide: true },
                    { data: 'items.name', title: 'Last Updated', hide: true },
                    { data: 'account.contacts.email', title: 'Last Updated', hide: true },
                ]" :filters="{
                    'user.name': { type: 'options', options: users.map(x => x.name) },
                    'solution_type.name': { type: 'options', options: solutions.map(x => x.name) },
                    'status': { type: 'options', options: status }
                }" route="/orders">
                    <template #status="{ value }">
                        <span class="badge text-bg-secondary">{{ value }}</span>
                    </template>
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #assign_order="{ data, value, index, getData }">
                        <div class="d-flex justify-content-end">
                            <button v-if="data.user_id === null" :id="'datatable-btn-' + index + '-assign-order'" @click="assignOrder(data.id, getData)"
                                     class="btn px-0 py-0 border-0">
                                <font-awesome-icon :icon="['fas', 'user-plus']" />
                            </button>
                        </div>
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
