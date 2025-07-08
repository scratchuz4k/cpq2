<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ToggleSwitch from 'primevue/toggleswitch';

const brand = useForm({
    name: null,
    is_active: true
});

const saveBrand = () => {
    router.post('/brands', brand,
        {
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
    { label: 'Brands', url: '/brands' },
    { label: 'New Brand' }
]);

</script>
<template>
    <Head title="new Brand" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Brand" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-brand" @click="saveBrand()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="mb-3 d-flex flex-column">
                    <label class="form-label" for="active-filter">
                        Active:
                    </label>
                    <ToggleSwitch id="active-filter" v-model="brand.is_active">
                        <template #handle>
                            <font-awesome-icon
                                :icon="brand.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                class="text-primary"
                            />
                        </template>
                    </ToggleSwitch>
                </div>
                <div class="col-6">
                    <label for="brand-name" class="form-label">Name:</label>
                    <input id="brand-name" class="form-control" v-model="brand.name">
                    <div class="text-danger" v-if="brand.errors.name">{{ brand.errors.name }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
