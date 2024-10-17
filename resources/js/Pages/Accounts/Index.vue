<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'My Account', url: '/myaccount' }
]);


</script>

<template>

    <Head title="Accounts" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Accounts" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/accounts/create" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New Account
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable :allow="{
                    update: true,
                    delete: true,
                    link: false,
                }" :columns="[
                    { data: 'id', title: '#', width: '5%' },
                    { data: 'tenant.name', title: 'Tenant', width: '15%' },
                    { data: 'name', title: 'Name', width: '25%' },
                    { data: 'reference', title: 'Reference', width: '15%' },
                    { data: 'created_at', title: 'Created At', width: '15%' },
                    { data: 'updated_at', title: 'Updated At', width: '15%' },
                ]" route="/accounts">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
