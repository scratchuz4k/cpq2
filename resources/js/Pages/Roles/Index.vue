<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const items = ref([{ label: 'Home', url: '/' }, { label: 'My Account', url: '/myaccount' }, { label: 'Roles' }]);
</script>
<template>
    <Head title="Roles" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Roles" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/roles/create" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                        New Role
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :allow="{
                        update: true,
                        delete: true,
                        link: false
                    }"
                    :columns="[
                        { data: 'id', title: '#', style: 'width:5%' },
                        { data: 'name', title: 'Name', style: 'width:40%' },
                        { data: 'parent.name', title: 'Parent', style: 'width:25%' },
                        { data: 'created_at', title: 'Created At', style: 'width:15%' },
                        { data: 'updated_at', title: 'Updated At', style: 'width:15%' }
                    ]"
                    route="/roles"
                >
                    <template #parent.name="{ value }">
                        <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm">
                            {{ value }}
                        </span>
                    </template>
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #is_active="{ data, value }">
                        <ToggleActive :data="data" :value="value" route="roles" />
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
