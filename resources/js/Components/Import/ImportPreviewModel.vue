<script setup>
import { ref } from 'vue';
import { Dialog, Button } from 'primevue';
import Table from 'primevue/datatable';
import Column from 'primevue/column';
import CountUp from 'vue-countup-v3';
import { importConfirm } from '@/Composables/import.js';
import { formatResponseErrors } from '@composables/useApiErrors';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    previewData: {
        type: Object,
        default: null
    },
    importForm: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['update:visible', 'imported']);

const isLoading = ref(false);

const rowClass = (rowData) => {
    const errors = props.previewData?.results?.errors ?? [];
    const hasError = errors.some((errorItem) => errorItem.row_index === rowData.index);
    const rowClass = 'preview-table-row';

    return hasError ? rowClass + ' row-has-error' : rowClass;
};

const importData = async () => {
    isLoading.value = true;

    try {
        let res = await importConfirm(props.importForm.file, props.importForm.model, props.importForm.overwrite);
        if (res) {
            window.location.href = route('imports.index');
        }
    } catch (err) {
        notyf.error(formatResponseErrors(err));
        return false;
    } finally {
        emit('update:visible', false);
        isLoading.value = false;
    }
};
</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="(val) => emit('update:visible', val)"
        id="import-preview"
        modal
        :style="{ marginTop: '4rem', width: '80vw' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        :draggable="false"
        position="top"
        header="Import Preview"
    >
        <div class="import-preview-body max-w-7xl mx-auto sm:px-6 lg:px-8 pb-2">
            <hr class="mt-0" />
            <div v-if="previewData" class="preview-table-scroll mt-2 mb-3">
                <Table
                    :value="previewData.results?.data"
                    :rowClass="rowClass"
                    tableStyle="min-width: max-content"
                    :size="'small'"
                    class="p-datatable-sm preview-table"
                >
                    <Column
                        v-for="(column, key) in previewData.columns"
                        :key="key"
                        :field="key"
                        :header="column"
                    ></Column>
                </Table>
            </div>
            <div class="mt-2 row row-cols-1 row-cols-md-3 g-3 mb-2">
                <div class="col">
                    <div class="card py-4 bg-light border-0">
                        <div class="card-body text-center p-2 text-decoration-none">
                            <div class="fw-bold mb-1">Rows will be Processed</div>
                            <span class="display-6">
                                <count-up :endVal="previewData?.results?.processed ?? 0" />
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card py-4 bg-light border-0">
                        <div class="card-body text-center p-2 text-decoration-none">
                            <div class="fw-bold mb-1">Rows will be Imported</div>
                            <span class="display-6">
                                <count-up :endVal="previewData?.results?.success ?? 0" />
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card py-4 bg-light border-0">
                        <div class="card-body text-center p-2 text-decoration-none">
                            <div class="fw-bold mb-1">Rows will be Skipped</div>
                            <span class="display-6">
                                <count-up :endVal="previewData?.results?.skipped ?? 0" />
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <br />
            <p class="fw-semibold">Errors</p>
            <ul v-if="previewData?.results?.errors?.length > 0">
                <li class="rows" v-for="(row, index) in previewData.results.errors" :key="index">
                    {{ previewData.model }} {{ row.row?.name || 'Unknown' }}:
                    <ul>
                        <li v-if="row.errors" v-for="(error, key) in row.errors" :key="key">
                            {{ error }}
                        </li>
                        <li v-else>{{ row.error }}</li>
                    </ul>
                </li>
            </ul>
            <p v-else>No errors occurred.</p>
            <hr class="mt-0" />
            <div class="d-flex justify-content-end mt-3">
                <div class="btn btn-secondary me-2" @click="emit('update:visible', false)">Cancel</div>
                <Button class="btn btn-primary" :disabled="isLoading" @click="importData"> Import </Button>
            </div>
        </div>
    </Dialog>
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
