<script setup>
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
DataTable.use(DataTablesCore);

defineProps({
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
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <h1>Welcome {{ $page.props.auth.user.name }}<span>.</span></h1>

        <div class="panel">
            <h2>Quotes</h2>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable :allow="{
                    update: true,
                    delete: true,
                    link: true,
                }" :columns="[
                    { data: 'id', title: '#' },
                    { data: 'account.name', title: 'Account' },
                    { data: 'solution_type.name', title: 'Solution' },
                    { data: 'user.name', title: 'Owner' },
                    { data: 'status', title: 'Status' },
                    { data: 'updated_at', title: 'Last Updated' },
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
                        {{ helpers.formatDate(value) }}
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
