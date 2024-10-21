<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Accounts' },
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
                    { data: 'id', title: '#', style: 'width:5%' },
                    { data: 'tenant.name', title: 'Tenant', style: 'width:15%' },
                    { data: 'name', title: 'Name', style: 'width:25%' },
                    { data: 'reference', title: 'Reference', style: 'width:15%' },
                    { data: 'created_at', title: 'Created At', style: 'width:15%' },
                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                ]" route="/accounts">
                    <template #updated_at="{ value }">
                        {{ helpers.formatDate(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ helpers.formatDate(value) }}
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
