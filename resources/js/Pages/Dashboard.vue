<script setup>
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
DataTable.use(DataTablesCore);
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <h1>Welcome {{ $page.props.auth.user.name }}<span>.</span></h1>

        <div class="panel">
            <h2>Quotes</h2>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Datatable route="/orders" :columns="[
                        { data: 'id', title: '#' },
                        { data: 'account.name', title: 'Account' },
                        { data: 'solution_type_name', title: 'Solution' },
                        { data: 'user.name', title: 'User' },
                        { data: 'status', title: 'Status' },
                        { data: 'updated_at', title: 'Last Updated' },
                    ]">
                    </Datatable>
                    <!-- <DataTable class="table table-striped" :columns="[
                        { data: 'id', title: '#' },
                        { data: 'account.name', title: 'Account' },
                        { data: 'solution_type_name', title: 'Solution' },
                        { data: 'user.name', title: 'User' },
                        { data: 'status', title: 'Status' },
                        { data: 'updated_at', title: 'Last Updated' },
                    ]" :ajax="{
                        url: '/api/orders',
                    }" :options="options">
                        <template #column-0="props">
                            <Link :href="route('orders.show', { order: props.cellData })" method="get">
                            {{ props.cellData }}
                            </Link>
                        </template>
                    </DataTable> -->
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>