<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import Header from '@/Components/Header.vue';
import format from '@/Composables/format.js';

const props = defineProps({
    imports: {
        type: Array,
        default: () => []
    }
});
const showForm = ref(false);
const showImportPreview = ref(false);
const previewData = ref(null);
const importForm = ref(null);

const handlePreview = ({ data, form }) => {
    previewData.value = data;
    importForm.value = form;
    showImportPreview.value = true;
};

const page = usePage();
const modelLabel = (name) =>
    page.props.importModels?.find((item) => item.name === name)?.label ??
    format.friendlyString(format.toSnakeCase(name));

const options = new DatatableOptions('imports', 'imports-table');
options.addColumn('id', 'ID');
options.addColumn('model', 'Model');
options.addColumn('name', 'Name');
options.addColumn('user.name', 'Imported By');
options.addColumn('status', 'Status');
options.addColumn('created_at', 'Created At');
options.addColumn('updated_at', 'Updated At');

options.setAllowedActions({
    link: true,
    allowExport: can('export import') || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: `Imports` }]);
</script>
<template>
    <Head title="Imports" />
    <AuthenticatedLayout>
        <Header title="Imports" :breadcrumbs="items">
            <template #buttons>
                <a id="import-btn" class="btn btn-primary me-2" @click.prevent="showForm = !showForm">
                    <font-awesome-icon :icon="['fas', 'upload']"></font-awesome-icon>
                    Import
                </a>
            </template>
        </Header>
        <NewDatatable :options="options">
            <template #status="{ value }">
                <span class="badge text-bg-secondary text-uppercase">{{ value }}</span>
            </template>
            <template #model="{ value }">
                <span class="badge border border-secondary text-secondary opacity-75 text-uppercase lh-sm">{{
                    modelLabel(value)
                }}</span>
            </template>
            <template #created_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #updated_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
        </NewDatatable>

        <FileUploadModal v-model:visible="showForm" :imports="props.imports" @preview="handlePreview" />
        <ImportPreviewModel v-model:visible="showImportPreview" :preview-data="previewData" :import-form="importForm" />
    </AuthenticatedLayout>
</template>
