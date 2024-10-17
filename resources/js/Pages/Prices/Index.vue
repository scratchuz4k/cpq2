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
                    { data: 'id', title: '#', width: '5%' },
                    { data: 'tenant.name', title: 'Tenant', width: '11%' },
                    { data: 'spa.name', title: 'Spa', width: '12%' },
                    { data: 'product.name', title: 'Product', width: '12%' },
                    { data: 'tax_rate', title: 'Tax Rate', width: '5%' },
                    { data: 'net', title: 'Net', width: '5%' },
                    { data: 'tax', title: 'Tax', width: '5%' },
                    { data: 'gross', title: 'Gross', width: '5%' },
                    { data: 'created_at', title: 'Created At', width: '15%' },
                    { data: 'updated_at', title: 'Updated At', width: '15%' },
                ]" route="/prices">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
