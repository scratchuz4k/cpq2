<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const items = ref([{ label: 'Home', url: '/' }, { label: 'My Account', url: '/myaccount' }, { label: 'Products' }]);
</script>
<template>
    <Head title="Products" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Products" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/blacklists" id="blacklist-index-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'shop-lock']" />
                        Blacklisted Items
                    </Link>
                    <Link href="/products/create" id="product-create-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                        New Product
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :table-props="{ id: 'products-table' }"
                    :allow="{
                        update: true,
                        delete: true,
                        link: false
                    }"
                    :columns="[
                        { data: 'is_active', title: 'Active', style: 'width:5%' },
                        { data: 'code', title: 'Code', style: 'width:20%' },
                        { data: 'name', title: 'Name', style: 'width:25%' },
                        { data: 'parent.name', title: 'Parent', style: 'width:12%' },
                        { data: 'categories.name', title: 'Category', style: 'width:12%' },
                        { data: 'updated_at', title: 'Updated At', style: 'width:15%' }
                    ]"
                    route="/products"
                >
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #is_active="{ data, value }">
                        <ToggleActive :data="data" :value="value" route="products" />
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
