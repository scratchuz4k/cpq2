<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Products' },
]);

</script>
<template>
    <Head title="Products" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Products" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/products/create" id="product-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New Product
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
                    { data: 'supplier.name', title: 'Supplier', style: 'width:12%' },
                    { data: 'brand.name', title: 'Brand', style: 'width:12%' },
                    { data: 'name', title: 'Name', style: 'width:30%' },
                    { data: 'created_at', title: 'Created At', style: 'width:15%' },
                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                ]" route="/products">
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
