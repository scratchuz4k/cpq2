<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';
import Header from '@/Components/Header.vue';
import Datatable from '@/Components/Datatable.vue';
import { Dialog, Button, Checkbox, Select } from 'primevue';
import { exportData } from '@/Composables/export.js';
import queryString from 'query-string';
import CountUp from 'vue-countup-v3';
import Table from 'primevue/datatable';
import Column from 'primevue/column';
import SvgIcon from '@/Components/Custom/SvgIcon.vue';

const page = usePage();
const props = defineProps({
    imports: Array,
    model: {
        type: String,
        default: 'Product'
    }
});
const showForm = ref(false);
const showImportPreview = ref(false);
const isLoading = ref(false);
const previewData = ref(null);

const rowClass = (rowData) => {
    const errors = previewData.value.results?.errors ?? [];
    const hasError = errors.some((errorItem) => errorItem.row?.sku === rowData.sku);
    const rowClass = 'preview-table-row';

    return hasError ? rowClass + ' row-has-error' : rowClass;
};

const onFileChange = (event) => {
    form.file = event.target.files[0];
};

const form = reactive({
    file: null,
    overwrite: 0,
    model: props.model ?? props.imports[0]
});

const getTemplateFile = async () => {
    exportData(
        queryString.stringify({
            model: form.model,
            template: true
        }),
        '/imports'
    );
};

const submitFile = async () => {
    isLoading.value = true;

    const formData = new FormData();
    formData.append('file', form.file);
    formData.append('overwrite', form.overwrite);
    formData.append('model', form.model);

    try {
        let res = await axios.post('/api/imports', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (res) {
            previewData.value = res.data;
        }
        showImportPreview.value = true;
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        return false;
    } finally {
        showForm.value = false;
        isLoading.value = false;
        isLoading.value = null;
    }
};

const importData = async () => {
    isLoading.value = true;

    const formData = new FormData();
    formData.append('file', form.file);
    formData.append('overwrite', form.overwrite);
    formData.append('model', form.model);

    try {
        let res = await axios.post('/imports', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        notyf.success('File imported successfully');

        if (res) {
            emitter.emit('datatable-refresh');
        }
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        return false;
    } finally {
        form.overwrite = 0;
        showForm.value = false;
        showImportPreview.value = false;
        isLoading.value = false;
        isLoading.value = null;
    }
};

const items = ref([{ label: 'Home', url: '/' }, { label: `Imports` }]);

watch(
    () => form.model,
    () => {
        form.overwrite = 0;
    }
);
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
        <Dialog
            v-model:visible="showForm"
            id="csv-import-form"
            modal
            :style="{ marginTop: '4rem', width: '50rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
            :draggable="false"
            position="top"
            header="Import by CSV file"
        >
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-2">
                <hr class="mt-0" />
                <form @submit.prevent="submitFile" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-3 mb-1">
                            <label for="import" class="form-label">Import Type:</label>
                            <Select
                                inputId="import"
                                v-model="form.model"
                                :options="props.imports"
                                placeholder="Select a Import"
                                class="w-100"
                            />
                        </div>
                        <div class="col-md-9 mb-1">
                            <label for="csv-file" class="form-label">CSV file:</label>
                            <input
                                id="csv-file"
                                type="file"
                                accept=".csv"
                                required
                                @input="onFileChange"
                                class="form-control"
                            />
                        </div>
                    </div>
                    <p class="py-2 text-muted">
                        <SvgIcon width="20" height="20" name="info" />
                        <i class="small">
                            Download a
                            <a
                                role="button"
                                @click="getTemplateFile"
                                class="color-purple"
                                id="products-import-template-link"
                                >sample CSV template</a
                            >
                            to see and example of the format required.
                        </i>
                    </p>
                    <div v-if="form.model === 'Product'" class="form-check px-0 pt-2">
                        <Checkbox
                            inputId="overwrite-products"
                            v-model="form.overwrite"
                            binary
                            trueValue="1"
                            falseValue="0"
                        />
                        <label class="form-check-label ms-2" for="overwrite-products">
                            Overwrite existing products that have the same SKU
                        </label>
                    </div>
                    <hr />
                    <button type="submit" class="btn btn-primary" :disabled="isLoading">
                        <span v-if="isLoading">
                            <font-awesome-icon :icon="['fa', 'spinner']" class="fa-spin"></font-awesome-icon>
                            Uploading...
                        </span>
                        <span v-else>Upload</span>
                    </button>
                </form>
            </div>
        </Dialog>
        <Dialog
            v-model:visible="showImportPreview"
            id="import-preview"
            modal
            :style="{ marginTop: '4rem', width: '80vw' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
            :draggable="false"
            position="top"
            header="Import Preview"
        >
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-2">
                <hr class="mt-0" />
                <div v-if="previewData" class="mt-2 row row-cols-1 g-3 mb-2">
                    <Table
                        :value="previewData.results?.data"
                        :rowClass="rowClass"
                        tableStyle="min-width: 50rem"
                        :size="'small'"
                        class="p-datatable-sm preview-table"
                    >
                        <Column v-for="(column, key) in previewData.columns" :field="key" :header="column"></Column>
                    </Table>
                </div>
                <div class="mt-2 row row-cols-1 row-cols-md-3 g-3 mb-2">
                    <div class="col">
                        <div class="card py-4 bg-light border-0">
                            <div class="card-body text-center p-2 text-decoration-none">
                                <div class="fw-bold mb-1">Rows will be Processed</div>
                                <span class="display-6">
                                    <count-up :endVal="previewData.results?.processed ?? 0" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card py-4 bg-light border-0">
                            <div class="card-body text-center p-2 text-decoration-none">
                                <div class="fw-bold mb-1">Rows will be Imported</div>
                                <span class="display-6">
                                    <count-up :endVal="previewData.results?.success ?? 0" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card py-4 bg-light border-0">
                            <div class="card-body text-center p-2 text-decoration-none">
                                <div class="fw-bold mb-1">Rows will be Skipped</div>
                                <span class="display-6">
                                    <count-up :endVal="previewData.results?.skipped ?? 0" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <br />
                <p class="fw-semibold">Errors</p>
                <ul v-if="previewData.results?.errors?.length > 0">
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
                    <div class="btn btn-secondary me-2" @click="showImportPreview = false">Cancel</div>
                    <Button class="btn btn-primary" @click="importData"> Import </Button>
                </div>
            </div>
        </Dialog>
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
</style>
