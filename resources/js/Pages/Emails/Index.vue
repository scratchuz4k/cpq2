<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';

const templateOptions = new DatatableOptions('email-templates', 'email-templates-table');
templateOptions.addColumn('id', '#');
templateOptions.addColumn('is_active', 'Active');
templateOptions.addColumn('code', 'Code');
templateOptions.addColumn('description', 'Description');
templateOptions.addColumn('notes', 'Notes');
templateOptions.addColumn('created_at', 'Created At');
templateOptions.addColumn('updated_at', 'Updated At');
templateOptions.addColumnFilter('is_active', 'checkbox');
templateOptions.setAllowedActions({
    update: true,
    allowExport: can('export email-template') || hasRole('Super Admin')
});

const historyOptions = new DatatableOptions('email-history', 'email-history-table');
historyOptions.addColumn('id', '#');
historyOptions.addColumn('email.code', 'Code');
historyOptions.addColumn('user.name', 'User');
historyOptions.addColumn('from', 'From');
historyOptions.addColumn('to', 'To');
historyOptions.addColumn('updated_at', 'Updated At');
historyOptions.setAllowedActions({
    link: true,
    allowExport: can('export email-history') || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: "Email's" }]);
</script>
<template>
    <Head title="Email's" />
    <AuthenticatedLayout>
        <Header :breadcrumbs="items" title="Email's"></Header>
        <tabs class="mt-3">
            <tab :selected="true" title="Templates">
                <div class="mt-3">
                    <NewDatatable :options="templateOptions">
                        <template #code="{ value }">
                            <span class="badge text-bg-secondary">{{ value }}</span>
                        </template>
                        <template #notes="{ value }">
                            <!-- Native title: table cells clip overflow, so an inline tooltip would be cut off. -->
                            <span v-if="value" :title="value">
                                <font-awesome-icon :icon="['fas', 'circle-info']" />
                            </span>
                        </template>
                        <template #updated_at="{ value }">
                            <p class="small">{{ format.date(value) }}</p>
                        </template>
                        <template #created_at="{ value }">
                            <p class="small">{{ format.date(value) }}</p>
                        </template>
                        <template #is_active="{ data, value }">
                            <ToggleActive :data="data" :value="value" route="email-templates" />
                        </template>
                    </NewDatatable>
                </div>
            </tab>
            <tab title="History">
                <div class="mt-3">
                    <NewDatatable :options="historyOptions">
                        <template #email.code="{ value }">
                            <span class="badge text-bg-secondary">{{ value }}</span>
                        </template>
                        <template #updated_at="{ value }">
                            <p class="small">{{ format.date(value) }}</p>
                        </template>
                        <template #created_at="{ value }">
                            <p class="small">{{ format.date(value) }}</p>
                        </template>
                    </NewDatatable>
                </div>
            </tab>
        </tabs>
    </AuthenticatedLayout>
</template>
