<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: "Products", url: "/products" },
    { label: 'BlackList' },
]);

</script>
<template>
    <Head title="BlackList" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="BlackList" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/blacklist/create" id="blacklist-create-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New Item
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
                    { data: 'code', title: 'Code', style: 'width:20%' },
                    { data: 'name', title: 'Name', style: 'width:30%' },
                    { data: 'supplier.name', title: 'Supplier', style: 'width:12%' },
                    { data: 'brand.name', title: 'Brand', style: 'width:12%' },
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
