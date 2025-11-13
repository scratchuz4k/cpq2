<script setup>
import { reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from 'primevue';
import ToggleSwitch from 'primevue/toggleswitch';
import Select from 'primevue/select';
import Tabs from '@/Components/Tabs.vue';
import Tab from '@/Components/Tab.vue';

const props = defineProps({
    category: Object
});

const product = ref(null);
const category = reactive(props.category);

const saveCate = () => {
    router.put(
        `/categories/${category.id}`,
        {
            ...category,
            parent_id: category.parent.id ?? null
        },
        {
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
        }
    );
};

const setProductCategory = async (product_id, category_id) => {
    if (!product_id) {
        return;
    }
    try {
        await axios.put('/api/products/' + product_id, { categories: category_id });
        product.value = null;

        notyf.success(category_id ? 'Product added with Success' : 'Product removed with success');

        emitter.emit('datatable-refresh:category-products');
        emitter.emit('input-select-refresh:select-product');
    } catch (ex) {
        product.value = null;
        notyf.error(ex.response?.data?.message ?? ex.response?.data);
    }
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Categories', url: '/categories' },
    { label: 'Edit Category' }
]);
</script>
<template>
    <Head title="Edit Category" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Edit Category" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-category" @click="saveCate()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <tabs>
                <tab :selected="true" title="General">
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
                            <label class="form-label" for="category-code">Code:</label>
                            <input id="category-code" v-model="category.code" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="category-name">Name:</label>
                            <input id="category-name" v-model="category.name" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="category-parent" class="form-label">Parent:</label>
                            <div class="input-group p-0">
                                <InputSelect
                                    id="category-parent"
                                    url="/categories"
                                    :fields="['name']"
                                    class="w-100"
                                    v-model="category.parent"
                                    placeholder="Select a Parent"
                                    :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                                />
                            </div>
                        </div>
                    </div>
                </tab>
                <tab title="Products">
                    <form class="mt-3 d-flex" @submit.prevent="setProductCategory(product?.id, category.id, 'add')">
                        <div class="me-3" style="width: 100%">
                            <label class="form-label">Add Products:</label>
                            <InputSelect
                                id="select-product"
                                v-model="product"
                                :fields="['name']"
                                placeholder="Select a Product"
                                :query="[
                                    { column: 'categories.id', operator: 'is_null', value: 'category.id' },
                                    { column: 'is_active', operator: 'equals', value: 1 }
                                ]"
                                url="/products"
                            >
                            </InputSelect>
                        </div>
                        <div class="d-flex align-items-end justify-content-end">
                            <Button id="button-add-product-to-category" class="btn btn-secondary" type="submit">
                                <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                                Add
                            </Button>
                        </div>
                    </form>
                    <div class="row mt-3">
                        <div class="col">
                            <!-- <label class="form-label">Products:</label> -->
                            <Datatable
                                id="category-products"
                                :columns="[
                                    { data: 'id', title: '#', style: 'width:5%' },
                                    { data: 'sku', title: 'Sku', style: 'width:20%' },
                                    { data: 'name', title: 'Name', style: 'width:30%' },
                                    { data: 'supplier.name', title: 'Supplier', style: 'width:12%' },
                                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                                    { data: 'actions', title: ' At', style: 'width:5%' }
                                ]"
                                :query="[{ column: 'categories.id', operator: 'equals', value: category.id }]"
                                route="/products"
                            >
                                <template #updated_at="{ value }">
                                    {{ format.date(value) }}
                                </template>
                                <template #created_at="{ value }">
                                    {{ format.date(value) }}
                                </template>
                                <template #actions="{ data, value, index }">
                                    <button
                                        class="btn px-2 py-0 mx-1 border-0"
                                        :id="'datatable-btn-' + index + '-delete'"
                                        @click="setProductCategory(data.id, null)"
                                    >
                                        <font-awesome-icon :icon="['fas', 'trash-alt']"></font-awesome-icon>
                                    </button>
                                </template>
                            </Datatable>
                        </div>
                    </div>
                </tab>
            </tabs>
        </div>
    </AuthenticatedLayout>
</template>
