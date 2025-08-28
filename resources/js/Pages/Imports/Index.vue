<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import Header from '@/Components/Header.vue';
import Datatable from '@/Components/Datatable.vue';
import { Dialog, Button, Checkbox } from 'primevue';
import { exportData } from '@/Composables/export.js';

const page = usePage();
const showForm = ref(false);
const isLoading = ref(false);
const onFileChange = (event) => {
    formFile.file = event.target.files[0];
};

const formFile = reactive({
    file: null,
    overwrite: 0
});

const getTemplateFile = async () => {
    exportData('', '/imports');
};

const submitFile = async () => {
    isLoading.value = true;

    const formData = new FormData();
    formData.append('file', formFile.file);
    formData.append('overwrite', formFile.overwrite);

    try {
        let res = await axios.post('/imports', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        notyf.success('File imported successfully');
        showForm.value = false;

        if (res) {
            emitter.emit('datatable-refresh');
        }
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        return false;
    } finally {
        formFile.overwrite = 0;
        isLoading.value = false;
    }
};

const items = ref([{ label: 'Home', url: '/' }, { label: `Imports` }]);
</script>
<template>
    <Head title="Imports" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Imports" :breadcrumbs="items">
                <template #buttons>
                    <Button id="import-btn" class="btn btn-primary me-2" @click.prevent="showForm = !showForm">
                        <font-awesome-icon :icon="['fas', 'upload']"></font-awesome-icon>
                        Import
                    </Button>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :table-props="{ id: 'products-table' }"
                    :allow="{
                        link: true
                    }"
                    :columns="[
                        { data: 'id', title: 'ID', style: 'width:20%' },
                        { data: 'name', title: 'Name', style: 'width:20%' },
                        { data: 'status', title: 'Status', style: 'width:20%' },
                        { data: 'user.name', title: 'Imported By', style: 'width:20%' },
                        { data: 'created_at', title: 'Updated At', style: 'width:20%' }
                    ]"
                    route="/imports"
                >
                    <template #created_at="{ value }">
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
            header="Import Products by CSV file"
        >
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-2">
                <hr class="mt-0" />
                <form @submit.prevent="submitFile" enctype="multipart/form-data">
                    <input type="file" accept=".csv" required @input="onFileChange" />
                    <p class="py-2">
                        Download a
                        <a @click="getTemplateFile" class="color-purple" id="products-import-template-link"
                            >sample CSV template</a
                        >
                        to see and example of the format required.
                    </p>
                    <div class="form-check px-0 pt-2">
                        <Checkbox
                            id="overwrite-products"
                            v-model="formFile.overwrite"
                            binary
                            trueValue="1"
                            falseValue="0"
                        />
                        <label class="form-check-label ms-2" for="overwrite-products">
                            Overwrite existing products that have the same handle
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
    </AuthenticatedLayout>
</template>
