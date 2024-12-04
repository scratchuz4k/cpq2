<script setup>
import { reactive, ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
    price_plans: Object,
})

const product = reactive({
    name: null,
    price_plan_id: 1,
    supplier_id: null,
    brand_id: null,
    code: null,
    description: null
})
const categories = ref([])

const saveProduct = () => {
    router.post(`/products`, { ...product, categories: categories.value.map(item => item.id) },
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
            <tabs class="mt-3">
                <tab title="General" :selected="true">
                    <div class="col-6">
                        <div class="row mb-3">
                            <label for="product-code" class="form-label">Code:</label>
                            <input id="product-code" class="form-control" v-model="product.code">
                        </div>
                        <div class="row mb-3">
                            <label for="product-name" class="form-label">Name:</label>
                            <input id="product-name" class="form-control" v-model="product.name">
                        </div>
                        <div class="row mb-3">
                            <label for="product-description" class="form-label">Description:</label>
                            <textarea id="product-description" class="form-control"
                                v-model="product.description"></textarea>
                        </div>
                        <h4>Relations</h4>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Price Plan</label>
                            <select class="form-select" v-model="product.price_plan_id">
                                <option v-for="price_plan in props.price_plans" :value="price_plan.id">
                                    {{ price_plan.name }}
                                </option>
                            </select>
                        </div>
                        <div class=" mb-3">
                            <label for="supplier-name" class="form-label">Supplier:</label>
                            <InputSelect id="product-supplier" url="/suppliers" field="name" v-model="product.supplier">
                            </InputSelect>
                        </div>

                        <div class=" mb-3">
                            <label for="brand-name" class="form-label">Brand:</label>
                            <InputSelect id="product-brand" url="/brands" field="name" v-model="product.brand">
                            </InputSelect>
                        </div>


                        <div class=" mb-3">
                            <label for="brand-name" class="form-label">Categories:</label>
                            <InputSelectMultiple id="product-categories" url="/categories" field="name"
                                v-model="categories">
                            </InputSelectMultiple>
                        </div>
                    </div>
                </tab>
            </tabs>

        </div>
    </AuthenticatedLayout>
</template>
