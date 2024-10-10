<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'My Account', url: '/myaccount' }
]);

</script>
<template>
    <Head title="Products" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Products" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/products/create" class="btn p-0 me-2">
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
                    { data: 'id', title: '#' },
                    { data: 'tenant_id', title: 'Tenant' },
                    { data: 'supplier_id', title: 'Supplier' },
                    { data: 'brand_id', title: 'Brand' },
                    { data: 'name', title: 'Name' },
                    { data: 'created_at', title: 'Created At' },
                    { data: 'updated_at', title: 'Updated At' },
                ]" route="/products">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
