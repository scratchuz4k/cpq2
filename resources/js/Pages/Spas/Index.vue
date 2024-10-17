<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'My Account', url: '/myaccount' }
]);

</script>
<template>
    <Head title="Spas" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Spas" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/spas/create" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New SPA
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
                    { data: 'supplier.name', title: 'Supplier', style: 'width:15%' },
                    { data: 'user.name', title: 'User', style: 'width:15%' },
                    { data: 'name', title: 'Name', style: 'width:20%' },
                    { data: 'data', title: 'Data', style: 'width:35%' }
                ]" route="/spas">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
