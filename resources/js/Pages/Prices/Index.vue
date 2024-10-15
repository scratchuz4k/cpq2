<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'My Account', url: '/myaccount' }
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
                    { data: 'id', title: '#' },
                    { data: 'tenant.name', title: 'Tenant' },
                    { data: 'spa.name', title: 'Spa' },
                    { data: 'product.name', title: 'Product' },
                    { data: 'tax_rate', title: 'Tax Rate' },
                    { data: 'net', title: 'Net' },
                    { data: 'tax', title: 'Tax' },
                    { data: 'gross', title: 'Gross' },
                    { data: 'created_at', title: 'Created At' },
                    { data: 'updated_at', title: 'Updated At' },
                ]" route="/prices">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
