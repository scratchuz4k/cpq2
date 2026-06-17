<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ToggleActive from '@/Components/ToggleActive.vue';

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Categories' }]);
</script>
<template>
    <Head title="Categories" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Categories" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/categories/create" id="category-create-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                        New Category
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :table-props="{ id: 'categories-table' }"
                    :allow="{
                        update: true,
                        delete: true,
                        link: false
                    }"
                    :columns="[
                        { data: 'id', title: '#', style: 'width:5%' },
                        { data: 'is_active', title: 'Active' },
                        { data: 'code', title: 'Code', style: 'width:10%' },
                        { data: 'name', title: 'Name', style: 'width:10%' },
                        { data: 'parent.name', title: 'Parent' },
                        { data: 'created_at', title: 'Created At' },
                        { data: 'updated_at', title: 'Updated At' }
                    ]"
                    route="/categories"
                >
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #is_active="{ data, value }">
                        <ToggleActive :data="data" :value="value" route="categories" />
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
