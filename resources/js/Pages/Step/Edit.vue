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
    step: Object,
    solution_types: Array
});

const product = ref(null);
const step = reactive({
    ...props.step,
    solution_type_id: props.step.solution_type?.id ?? null
});
delete step.solution_type;
const productIds = ref([...(props.step.products ?? [])]);

const saveStep = () => {
    const { products, ...stepFields } = step;
    router.put(`/steps/${step.id}`, stepFields, {
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

const setStepProduct = async (product_id, add) => {
    if (!product_id) {
        return;
    }

    const ids = add
        ? [...new Set([...productIds.value, product_id])]
        : productIds.value.filter((id) => id !== product_id);

    try {
        await axios.put('/api/steps/' + step.id, { products: ids });
        productIds.value = ids;
        product.value = null;

        notyf.success(add ? 'Product added with Success' : 'Product removed with success');

        emitter.emit('datatable-refresh:step-products');
        emitter.emit('input-select-refresh:select-product');
    } catch (ex) {
        product.value = null;
        notyf.error(ex.response?.data?.message ?? ex.response?.data);
    }
};

const productOptions = new DatatableOptions('products', 'step-products');
productOptions.addColumn('id', '#');
productOptions.addColumn('sku', 'Code');
productOptions.addColumn('name', 'Name');
productOptions.addColumn('supplier.name', 'Supplier');
productOptions.addColumn('updated_at', 'Updated At');
productOptions.addQuery('steps.id', FilterOperators.EQUALS, step.id);
productOptions.setAllowedActions({
    allowExport: can('export product') || hasRole('Super Admin')
});

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Settings', url: '/settings' },
    { label: 'Steps', url: '/steps' },
    { label: 'Edit Step' }
]);
</script>
<template>
    <Head title="Edit Step" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Edit Step" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-step" @click="saveStep()">
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
                            <ToggleSwitch id="active-filter" v-model="step.is_active">
                                <template #handle>
                                    <font-awesome-icon
                                        :icon="step.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                        class="text-primary"
                                    />
                                </template>
                            </ToggleSwitch>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="step-name">Name:</label>
                            <input id="step-name" v-model="step.name" class="form-control" />
                        </div>

                        <h4>Relations</h4>
                        <hr />
                        <div class="mb-3">
                            <label for="step-solution-type" class="form-label">Solution Type:</label>
                            <Select
                                inputId="step-solution-type"
                                v-model="step.solution_type_id"
                                :options="props.solution_types"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Select a Solution Type"
                                class="w-100"
                            />
                        </div>
                    </div>
                </tab>
                <tab title="Products">
                    <form @submit.prevent="setStepProduct(product?.id, true)" class="mt-3 d-flex">
                        <div class="me-3" style="width: 100%">
                            <label class="form-label">Add Products:</label>
                            <InputSelect
                                id="select-product"
                                url="/products"
                                :fields="['name']"
                                v-model="product"
                                placeholder="Select an Product"
                                :query="[
                                    { column: 'steps.id', operator: 'not_in', value: step.id },
                                    { column: 'is_active', operator: 'equals', value: 1 }
                                ]"
                            >
                            </InputSelect>
                        </div>
                        <div class="d-flex align-items-end justify-content-end">
                            <Button id="button-add-product-to-step" type="submit" class="btn btn-secondary">
                                <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                                Add
                            </Button>
                        </div>
                    </form>
                    <div class="row mt-3">
                        <div class="col">
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
                                                setStepProduct(data.id, false);
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
