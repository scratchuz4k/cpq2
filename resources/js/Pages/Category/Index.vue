<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Categories' },
]);

</script>
<template>
    <Head title="Categories" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Categories" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/categories/create" id="category-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New Category
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable  :table-props="{ id: 'categories-table' }" :allow="{
                    update: true,
                    delete: true,
                    link: false,
                }" :columns="[
                    { data: 'id', title: '#', style: 'width:5%' },
                    { data: 'code', title: 'Code', style: 'width:20%' },
                    { data: 'name', title: 'Name' },
                ]" route="/categories">
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
