<script setup>
import { ref } from "vue";
import Breadcrumb from 'primevue/breadcrumb';
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from "@inertiajs/vue3";

const supplier = useForm({
    name: null,
    reference: null
})

const saveSupplier = () => {
    supplier.post("/suppliers", supplier)
}
const items = ref([
    { label: 'My Account', url: '/myaccount' },
    { label: 'Suppliers', url: '/suppliers' },
]);

</script>
<template>

    <Head title="Suppliers" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Supplier" :breadcrumbs="items">
                <template #buttons>
                    <Button @click="saveSupplier()" size="small">
                        <font-awesome-icon :icon="['fas', 'save']"></font-awesome-icon> Save
                    </Button>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="supplier-name" class="form-label">Name:</label>
                    <input id="supplier-name" class="form-control" v-model="supplier.name" required>
                    <div class="text-danger" v-if="supplier.errors.name">{{ supplier.errors.name }}</div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="supplier-reference" class="form-label">Reference:</label>
                    <input id="supplier-reference" class="form-control" v-model="supplier.reference">
                    <div class="text-danger" v-if="supplier.errors.reference">{{ supplier.errors.reference }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
