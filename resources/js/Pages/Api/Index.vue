<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Apis' },
]);

</script>
<template>

    <Head title="Api" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Api" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/apis/create" id="api-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New Api
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
                    { data: 'code', title: 'Code', },
                    { data: 'name', title: 'Name', },
                    { data: 'auth_type', title: 'Auth Type', },
                    { data: 'username', title: 'Username', },
                ]" route="/apis">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
