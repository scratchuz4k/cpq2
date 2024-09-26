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
                <Datatable :allow="{
                    create: false,
                    update: true,
                    delete: true,
                    link: true,
                }" :columns="[
                    { data: 'id', title: '#' },
                    { data: 'name', title: 'Name', options: { create: true, edit: true } },
                    { data: 'account.name', title: 'Account', options: { create: true, edit: true } },
                    { data: 'solution_type.name', title: 'Solution', options: { create: true, edit: true } },
                    { data: 'user.name', title: 'User' },
                    { data: 'status', title: 'Status' },
                    { data: 'updated_at', title: 'Last Updated' },
                ]" route="/orders">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
