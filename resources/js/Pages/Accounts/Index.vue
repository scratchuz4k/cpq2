<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from "@inertiajs/vue3";

defineProps({
})

</script>

<template>

    <Head title="Accounts" />
    <AuthenticatedLayout>
        <div class="panel">
            <h1>Accounts</h1>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <Datatable :allow="{
                    create: false,
                    update: true,
                    delete: true,
                    link: false,
                }" :columns="[
                    { data: 'id', title: '#' },
                    { data: 'name', title: 'Name', options: { create: true, edit: true } },
                    { data: 'reference', title: 'Reference', options: { create: true, edit: true } },
                    { data: 'tenant.name', title: 'Tenant', options: { create: true, edit: true } },
                    { data: 'created_at', title: 'Created At', options: { create: true, edit: true } },
                    { data: 'updated_at', title: 'Updated At', options: { create: true, edit: true } },
                ]" route="/accounts">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
