<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Header from '@/Components/Header.vue';

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

const items = ref([{ label: 'Home', url: '/' }, { label: `Imports` }]);
</script>
<template>
    <Head title="Imports" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Imports" :breadcrumbs="items">
                <template #buttons>
                    <a id="import-btn" class="btn btn-primary me-2" @click.prevent="showForm = !showForm">
                        <font-awesome-icon :icon="['fas', 'upload']"></font-awesome-icon>
                        Import
                    </a>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :table-props="{ id: 'products-table' }"
                    :allow="{
                        link: true
                    }"
                    :columns="[
                        { data: 'id', title: 'ID', style: 'width:5%' },
                        { data: 'model', title: 'Model', style: 'width:10%' },
                        { data: 'name', title: 'Name', style: 'width:25%' },
                        { data: 'user.name', title: 'Imported By', style: 'width:20%' },
                        { data: 'status', title: 'Status', style: 'width:10%' },
                        { data: 'created_at', title: 'Created At', style: 'width:15%' },
                        { data: 'updated_at', title: 'Updated At', style: 'width:15%' }
                    ]"
                    route="/imports"
                >
                    <template #status="{ value }">
                        <span class="badge text-bg-secondary text-uppercase">{{ value }}</span>
                    </template>
                    <template #model="{ value }">
                        <span class="badge border border-secondary text-secondary opacity-75 text-uppercase lh-sm">{{
                            value
                        }}</span>
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                </Datatable>
            </div>
        </div>

        <FileUploadModal v-model:visible="showForm" :imports="props.imports" @preview="handlePreview" />
        <ImportPreviewModel v-model:visible="showImportPreview" :preview-data="previewData" :import-form="importForm" />
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.preview-table-row) {
    background-color: #d4edda !important;
    color: #155724 !important;
    font-size: 14px;
}

:deep(.preview-table-row:hover) {
    background-color: #a7e5b5 !important;
}

:deep(.row-has-error) {
    background-color: #fff0f1 !important;
    color: #8c2b3a !important;
}

:deep(.row-has-error:hover) {
    background-color: #ffd9dd !important;
}

/* Keep the wide preview table inside the modal and scroll it horizontally */
.import-preview-body {
    min-width: 0;
    max-width: 100%;
}

:deep(.p-dialog-content) {
    overflow-x: hidden;
}

.preview-table-scroll {
    min-width: 0;
    max-width: 100%;
    overflow-x: auto;
}

.preview-table-scroll :deep(.p-datatable) {
    width: max-content;
    min-width: 100%;
}

.preview-table-scroll :deep(.p-datatable-table-container) {
    overflow: visible !important;
}

.preview-table-scroll :deep(th),
.preview-table-scroll :deep(td) {
    white-space: nowrap;
}
</style>
