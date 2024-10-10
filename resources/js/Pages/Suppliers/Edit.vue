<script setup>
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";
import { ref } from 'vue';

const props = defineProps({
    supplier: Object
})


const saveSupplier= () => {
    router.put(`/suppliers/${props.supplier.id}`, props.supplier,
        {
            onSuccess: (page) => {
                notyf.success("Updated with Success")
            },
            onError: (errors) => {
                let string = ''
                Object.keys(errors).forEach((key) => {
                    string += errors[key] + '<br>'
                })
                notyf.error(string)
            },
        }
    )
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
            <Header :title="'Edit Supplier #' + supplier.id" :breadcrumbs="items">
                <template #buttons>
                    <Button @click="saveSupplier()" size="small">
                        <font-awesome-icon :icon="['fas', 'save']"></font-awesome-icon> Save
                    </Button>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col">
                    <label for="supplier-name" class="form-label">Name:</label>
                    <input id="supplier-name" class="form-control" v-model="supplier.name">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label for="supplier-reference" class="form-label">Reference:</label>
                    <input id="supplier-reference" class="form-control" v-model="supplier.reference">
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
