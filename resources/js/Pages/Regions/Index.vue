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
                    { data: 'id', title: '#', width: '5%' },
                    { data: 'tenant.name', title: 'Tenant', width: '10%' },
                    { data: 'merchant_id', title: 'Merchant', width: '10%' },
                    { data: 'user.name', title: 'User', width: '15%' },
                    { data: 'name', title: 'Name', width: '20%' },
                    { data: 'created_at', title: 'Created At', width: '15%' },
                    { data: 'updated_at', title: 'Updated At', width: '15%' },
                ]" route="/regions">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
