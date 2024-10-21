<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Prices' },
]);

</script>
<template>

    <Head title="Prices" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Prices" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/prices/create" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New Price
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
                    { data: 'tenant.name', title: 'Tenant', style: 'width:11%' },
                    { data: 'spa.name', title: 'Spa', style: 'width:12%' },
                    { data: 'product.name', title: 'Product', style: 'width:12%' },
                    { data: 'tax_rate', title: 'Tax Rate', style: 'width:5%' },
                    { data: 'net', title: 'Net', style: 'width:5%' },
                    { data: 'tax', title: 'Tax', style: 'width:5%' },
                    { data: 'gross', title: 'Gross', style: 'width:5%' },
                    { data: 'created_at', title: 'Created At', style: 'width:15%' },
                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                ]" route="/prices">
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
