<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Tokens' }]);
</script>

<template>
    <Head title="Tokens" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Tokens" :breadcrumbs="items">
                <template #buttons>
                    <Link :href="route('tokens.create')" id="token-create-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                        New Token
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :allow="{
                        delete: true,
                        update: false,
                        link: true
                    }"
                    :columns="[
                        { data: 'id', title: '#', style: 'width:5%' },
                        { data: 'is_active', title: 'Active', style: 'width:5%' },
                        { data: 'name', title: 'Name', style: 'width:20%' },
                        { data: 'entity.name', title: 'Entity', style: 'width:15%' },
                        { data: 'last_used_at', title: 'Last Used At', style: 'width:15%' },
                        { data: 'expires_at', title: 'Expires At', style: 'width:15%' },
                        { data: 'created_at', title: 'Created At', style: 'width:15%' }
                    ]"
                    route="/tokens"
                >
                    <template #entity.name="{ value }">
                        <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm">{{
                            value
                        }}</span>
                    </template>
                    <template #last_used_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #expires_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #is_active="{ data, value }">
                        <ToggleActive :data="data" :value="value" route="tokens" />
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
