<script setup>
import { reactive, ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
    spas: Object,
    products: Object,
})

const price = reactive({
    tax_rate: 0,
    net: 0,
    tax: 0,
    gross: 0,
    spa_id: null,
    product_id: null
})

const savePrice = () => {
    router.post(`/prices`, price,
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
    { label: 'Prices', url: '/prices' },
    { label: 'New Price' },
]);

</script>
<template>

    <Head title="New Price" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Price" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="savePrice()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col-6">
                    <div class="mb-3">
                        <label for="price-name" class="form-label">Tax Rate:</label>
                        <input id="price-name" type="number" class="form-control" v-model="price.tax_rate">
                    </div>
                    <div class="mb-3">
                        <label for="price-name" class="form-label">Net:</label>
                        <input id="price-name" type="number" class="form-control" v-model="price.net">
                    </div>
                    <div class="mb-3">
                        <label for="price-name" class="form-label">Product:</label>
                        <select class="form-select" v-model="price.product_id">
                            <option v-for="product in products" :value="product.id">{{ product.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
