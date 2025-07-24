<script setup>
import { reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({});

const category = reactive({
    name: null,
    code: null,
    is_active: true
});

const saveCate = () => {
    router.post(`/categories`, category, {
        onSuccess: (page) => {
            notyf.success('Created with Success');
        },
        onError: (errors) => {
            let string = '';
            Object.keys(errors).forEach((key) => {
                string += errors[key] + '<br>';
            });
            notyf.error(string);
        }
    });
};
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Categories', url: '/categories' },
    { label: 'New Category' }
]);
</script>
<template>
    <Head title="New Category" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Category" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-category" @click="saveCate()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="col-6">
                <div class="mb-3 d-flex flex-column">
                    <label class="form-label" for="active-filter"> Active: </label>
                    <ToggleSwitch id="active-filter" v-model="category.is_active">
                        <template #handle>
                            <font-awesome-icon
                                :icon="category.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                class="text-primary"
                            />
                        </template>
                    </ToggleSwitch>
                </div>
                <div class="mb-3">
                    <label for="category-code" class="form-label">Code:</label>
                    <input id="category-code" class="form-control" v-model="category.code" />
                </div>
                <div class="mb-3">
                    <label for="category-name" class="form-label">Name:</label>
                    <input id="category-name" class="form-control" v-model="category.name" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
