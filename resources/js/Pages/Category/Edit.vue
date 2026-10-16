<script setup>
import { reactive, ref } from 'vue';
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import { FilterOperators } from '@composables/useApi';
import { can, hasRole } from '@composables/useAuth';
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
const productIds = ref([...(props.category.products ?? [])]);

const saveCate = () => {
    const { products, ...categoryFields } = category;

    router.put(
        `/categories/${category.id}`,
        {
            ...categoryFields,
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

const setProductCategory = async (product_id, add) => {
    if (!product_id) {
        return;
    }

    const ids = add
        ? [...new Set([...productIds.value, product_id])]
        : productIds.value.filter((id) => id !== product_id);

    try {
        await axios.put('/api/categories/' + category.id, { products: ids });
        productIds.value = ids;
        product.value = null;

        notyf.success(add ? 'Product added with Success' : 'Product removed with success');

        emitter.emit('datatable-refresh:category-products');
        emitter.emit('input-select-refresh:select-product');
    } catch (ex) {
        product.value = null;
        notyf.error(ex.response?.data?.message ?? ex.response?.data);
    }
};

const productOptions = new DatatableOptions('products', 'category-products');
productOptions.addColumn('id', '#');
productOptions.addColumn('sku', 'Sku');
productOptions.addColumn('name', 'Name');
productOptions.addColumn('supplier.name', 'Supplier');
productOptions.addColumn('updated_at', 'Updated At');
productOptions.addQuery('categories.id', FilterOperators.EQUALS, category.id);
productOptions.setAllowedActions({
    allowExport: can('export product') || hasRole('Super Admin')
});

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Settings', url: '/settings' },
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
                                    :query="[
                                        { column: 'is_active', operator: 'equals', value: 1 },
                                        { column: 'id', operator: 'not_equals', value: category.id }
                                    ]"
                                />
                            </div>
                        </div>
                    </div>
                </tab>
                <tab title="Products">
                    <form class="mt-3 d-flex" @submit.prevent="setProductCategory(product?.id, true)">
                        <div class="me-3" style="width: 100%">
                            <label class="form-label">Add Products:</label>
                            <InputSelect
                                id="select-product"
                                v-model="product"
                                :fields="['name']"
                                placeholder="Select a Product"
                                :query="[
                                    { column: 'categories.id', operator: 'not_in', value: [category.id] },
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
                            <NewDatatable :options="productOptions">
                                <template #updated_at="{ value }">
                                    <p class="small">{{ format.date(value) }}</p>
                                </template>
                                <template #row-actions="{ data, index, close }">
                                    <button
                                        type="button"
                                        :id="'datatable-btn-' + index + '-delete'"
                                        class="datatable-actions-item datatable-actions-item-danger"
                                        @click="
                                            () => {
                                                setProductCategory(data.id, false);
                                                close();
                                            }
                                        "
                                    >
                                        <font-awesome-icon :icon="['fas', 'trash']" class="me-2" />
                                        Remove
                                    </button>
                                </template>
                            </NewDatatable>
                        </div>
                    </div>
                </tab>
            </tabs>
        </div>
    </AuthenticatedLayout>
</template>
