<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ToggleSwitch from 'primevue/toggleswitch';

const supplier = useForm({
    name: null,
    reference: null,
    is_active: true
});

const saveSupplier = () => {
    router.post('/suppliers', supplier, {
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
    { label: 'Settings', url: '/settings' },
    { label: 'Suppliers', url: '/suppliers' },
    { label: 'New Supplier' }
]);
</script>
<template>
    <Head title="New Supplier" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Supplier" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-supplier" @click="saveSupplier()">
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
                    <input id="supplier-name" class="form-control" v-model="supplier.name" required />
                    <div class="text-danger" v-if="supplier.errors.name">{{ supplier.errors.name }}</div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="supplier-reference" class="form-label">Reference:</label>
                    <input id="supplier-reference" class="form-control" v-model="supplier.reference" />
                    <div class="text-danger" v-if="supplier.errors.reference">{{ supplier.errors.reference }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
