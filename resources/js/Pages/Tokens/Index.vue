<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Tokens' },
]);
</script>

<template>

    <Head title="Tokens" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Tokens" :breadcrumbs="items">
                <template #buttons>
                    <Link :href="route('tokens.create')" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New Token
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable :allow="{
                    delete: true,
                    update: false,
                    link: true,
                }" :columns="[
                    { data: 'id', title: '#' },
                    { data: 'name', title: 'Name' },
                    { data: 'entity.name', title: 'Entity' },
                    { data: 'last_used_at', title: 'Last Used At' },
                    { data: 'expires_at', title: 'Expires At' },
                    { data: 'created_at', title: 'Created At' },
                ]" route="/tokens">
                    <template #last_used_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #expires_at="{ value }">
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
