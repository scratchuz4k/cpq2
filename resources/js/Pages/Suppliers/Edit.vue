<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    supplier: Object
});

const saveSupplier = () => {
    router.put(`/suppliers/${props.supplier.id}`, props.supplier, {
        onSuccess: (page) => {
            notyf.success('Updated with Success');
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
    { label: 'Suppliers', url: '/suppliers' },
    { label: 'Edit Supplier' }
]);
</script>
<template>
    <Head :title="'Edit Supplier #' + supplier.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Supplier #' + supplier.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveSupplier()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="mb-3 d-flex flex-column">
                <label class="form-label" for="active-filter"> Active: </label>
                <ToggleSwitch id="active-filter" v-model="supplier.is_active">
                    <template #handle>
                        <font-awesome-icon
                            :icon="supplier.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                            class="text-primary"
                        />
                    </template>
                </ToggleSwitch>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="supplier-name" class="form-label">Name:</label>
                    <input id="supplier-name" class="form-control" v-model="supplier.name" />
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="supplier-reference" class="form-label">Reference:</label>
                    <input id="supplier-reference" class="form-control" v-model="supplier.reference" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
