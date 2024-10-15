<script setup>
import { reactive, ref } from "vue";
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
    price: Object,
    spas: Object,
    products: Object
})

const savePrice = () => {
    router.put(`/prices/${props.price.id}`, props.price,
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
    { label: 'Prices', url: '/prices' },
]);

</script>
<template>

    <Head title="Prices" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Price" :breadcrumbs="items">
                <template #buttons>
                    <Button @click="savePrice()" size="small">
                        <font-awesome-icon :icon="['fas', 'save']"></font-awesome-icon> Save
                    </Button>
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
                        <label for="price-name" class="form-label">Tax:</label>
                        <input id="price-name" type="number" class="form-control" v-model="price.tax">
                    </div>
                    <div class="mb-3">
                        <label for="price-name" class="form-label">Gross:</label>
                        <input id="price-name" type="number" class="form-control" v-model="price.gross">
                    </div>
                    <div class="mb-3">
                        <label for="price-name" class="form-label">SPA:</label>
                        <select class="form-select" v-model="price.spa_id">
                            <option v-for="spa in spas" :value="spa.id">{{ spa.name }}</option>
                        </select>
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
