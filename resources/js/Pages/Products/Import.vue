<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import Header from '@/Components/Header.vue';
import Datatable from '@/Components/Datatable.vue';
import { Dialog, Button } from 'primevue';
import { toUpper } from 'lodash';

const page = usePage();
const showForm = ref(false);
const isLoading = ref(false);
const onFileChange = (event) => {
    formFile.file = event.target.files[0];
};

const formFile = reactive({
    file: null
});

const submitFile = async () => {
    isLoading.value = true;

    const formData = new FormData();
    formData.append('file', formFile.file);

    try {
        let res = await axios.post('/products/import', formData, {
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
        isLoading.value = false;
    }
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Products', url: `/products` },
    { label: `Products Import` }
]);
</script>
<template>
    <Head title="Products" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Products" :breadcrumbs="items">
                <template #buttons>
                    <Button id="product-import-btn" class="btn btn-primary me-2" @click.prevent="showForm = !showForm">
                        <font-awesome-icon :icon="['fas', 'upload']"></font-awesome-icon>
                        Import
                    </Button>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!--                <Datatable-->
                <!--                    :table-props="{ id: 'products-table' }"-->
                <!--                    :allow="{-->
                <!--                        update: true,-->
                <!--                        delete: true,-->
                <!--                        link: false-->
                <!--                    }"-->
                <!--                    :columns="[-->
                <!--                        { data: 'id', title: 'ID', style: 'width:20%' },-->
                <!--                        { data: 'name', title: 'Name', style: 'width:25%' },-->
                <!--                        { data: 'status', title: 'Status', style: 'width:5%' },-->
                <!--                        { data: 'user.name', title: 'Imported By', style: 'width:15%' },-->
                <!--                        { data: 'created_at', title: 'Updated At', style: 'width:15%' }-->
                <!--                    ]"-->
                <!--                    route="/import"-->
                <!--                >-->
                <!--                    <template #created_at="{ value }">-->
                <!--                        {{ format.date(value) }}-->
                <!--                    </template>-->
                <!--                </Datatable>-->
            </div>
        </div>
        <Dialog
            v-model:visible="showForm"
            id="csv-import-form"
            modal
            :style="{ marginTop: '4rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
            :draggable="false"
            position="top"
        >
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4 pb-4 border-bottom">
                <form @submit.prevent="submitFile" enctype="multipart/form-data">
                    <h4>Import Users from CSV</h4>
                    <p>
                        Your CSV must contain the following columns: name*, email*, password, sso, managername. * must
                        not be blank
                    </p>
                    <input type="file" accept=".csv" required @input="onFileChange" />
                    <button type="submit" class="btn btn-primary ms-2" :disabled="isLoading">
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
