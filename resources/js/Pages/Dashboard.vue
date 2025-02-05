<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
const props = defineProps({
    users: {
        type: Array,
        default: []
    },
    solutions: {
        type: Array,
        default: []
    },
    accounts: {
        type: Array,
        default: []
    },
    status: {
        type: Array,
        default: []
    },
})
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <h1 id="welcome-msg">Welcome {{ $page.props.auth.user.name }}<span>.</span></h1>

        <div class="panel">
            <h2>Quotes</h2>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable :tableProps="{id:'dashboard-table'}" :allow="{
                    update: true,
                    delete: true,
                    link: true,
                }" :columns="[
                    { data: 'id', title: '#', style: 'width:5%' },
                    { data: 'account.name', title: 'Account', style: 'width:15%' },
                    { data: 'solution_type.name', title: 'Solution', style: 'width:25%' },
                    { data: 'user.name', title: 'Owner', style: 'width:20%' },
                    { data: 'status', title: 'Status', style: 'width:10%' },
                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                    { data: 'items.data', title: 'Data', hide: true },
                    { data: 'items.name', title: 'Name', hide: true },
                    { data: 'account.contacts.email', title: 'Email', hide: true },
                ]" :filters="{
                    'user.name': { type: 'options', options: users.map(x => x.name) },
                    'solution_type.name': { type: 'options', options: solutions.map(x => x.name) },
                    'status': { type: 'options', options: status }
                }" route="/quotes">
                    <template #status="{ value }">
                        <span class="badge text-bg-secondary">{{ value }}</span>
                    </template>
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
