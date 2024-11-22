<script setup>
import { reactive, ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
    brands: Object,
    suppliers: Object
})

const product = reactive({
    name: null,
    supplier_id: null,
    brand_id: null,
    code: null,
    description: null
})

const saveProduct = () => {
    router.post(`/products`, product,
        {
            onSuccess: (page) => {
                notyf.success("Created with Success")
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
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Products', url: '/products' },
    { label: 'New Product' },
]);

</script>
<template>
    <Head title="New Product" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Product" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-product" @click="saveProduct()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="col-6">
                <div class="row mb-3">
                    <label for="product-name" class="form-label">Name:</label>
                    <input id="product-name" class="form-control" v-model="product.name">
                </div>
                <div class="row mb-3">
                    <label for="supplier-name" class="form-label">Supplier:</label>
                    <select id="supplier-name" class="form-select" v-model="product.supplier_id">
                        <option v-for="supplier in suppliers" :value="supplier.id">{{ supplier.name }}</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <label for="brand-name" class="form-label">Brand:</label>
                    <select id="brand-name" class="form-select" v-model="product.brand_id">
                        <option v-for="brand in brands" :value="brand.id">{{ brand.name }}</option>
                    </select>
                </div>

                <div class="row mb-3">
                    <label for="product-code" class="form-label">Code:</label>
                    <input id="product-code" class="form-control" v-model="product.code">
                </div>

                <div class="row mb-3">
                    <label for="product-description" class="form-label">Description:</label>
                    <textarea id="product-description" class="form-control" v-model="product.description"></textarea>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
