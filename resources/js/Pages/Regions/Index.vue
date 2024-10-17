<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'My Account', url: '/myaccount' }
]);

</script>
<template>
    <Head title="Regions" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Regions" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/regions/create" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New Region
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
                    { data: 'tenant.name', title: 'Tenant', style: 'width:10%' },
                    { data: 'merchant_id', title: 'Merchant', style: 'width:10%' },
                    { data: 'user.name', title: 'User', style: 'width:15%' },
                    { data: 'name', title: 'Name', style: 'width:20%' },
                    { data: 'created_at', title: 'Created At', style: 'width:15%' },
                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                ]" route="/regions">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
