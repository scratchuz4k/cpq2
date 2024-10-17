<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, Link, router} from "@inertiajs/vue3";
import {reactive, ref} from 'vue';

const props = defineProps({
    results: {
        type: Object,
        default: null
    }
})

const items = ref([
    { label: 'My Account', url: '/myaccount' }
]);

const showForm = ref(false)
const isLoading = ref(false);
const onFileChange = (event) => {
    formFile.file = event.target.files[0];
};

const formFile = reactive({
    file: null,
});

const submitFileForm = () => {
    isLoading.value = true
    router.post(`/users/import`,
        {
            file: formFile.file
        },
        {
            onSuccess: () => {
                notyf.success(`File imported successfully`)
                isLoading.value = false
            },
            onError: (errors) => {
                let string = ''
                Object.keys(errors).forEach((key) => {
                    string += errors[key] + '<br>'
                })
                notyf.error(string)
                isLoading.value = false
            },
        }
    )
};

</script>
<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Users" :breadcrumbs="items">
                <template #buttons>
                    <Link :href="route('users.create')" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New User
                    </Link>
                    <div class="btn btn-secondary me-2" @click.prevent="showForm = !showForm">
                        <font-awesome-icon :icon="['fas', 'file']"></font-awesome-icon> Import from CSV
                    </div>
                </template>
            </Header>
            <div v-if="showForm" id="csv-import-form" class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4 pb-4 border-bottom">
                <form @submit.prevent="submitFileForm" enctype="multipart/form-data">
                    <h4>Import Users from CSV</h4>
                    <p>Your CSV must contain the following columns: name*, email*, password, sso, managername. * must not be blank</p>
                    <input type="file" accept=".csv" required @input="onFileChange" />
                    <button type="submit" class="btn btn-success ms-2"  :disabled="isLoading">
                        <span v-if="isLoading">
                            <font-awesome-icon :icon="['fa', 'spinner']" class="fa-spin"></font-awesome-icon>
                            Uploading...
                        </span>
                        <span v-else>Upload</span>
                    </button>
                </form>
            </div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable :allow="{
                    update: true,
                    delete: true,
                    link: false,
                }" :columns="[
                    { data: 'id', title: '#', style: 'width:5%' },
                    { data: 'name', title: 'Name', style: 'width:25%' },
                    { data: 'email', title: 'Email', style: 'width:30%' },
                    { data: 'roles.name', title: 'Role', style: 'width:15%' },
                    { data: 'parent.name', title: 'Manager', style: 'width:15%' }
                ]" route="/users">
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
