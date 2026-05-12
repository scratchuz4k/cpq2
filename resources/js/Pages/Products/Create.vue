<script setup>
import { reactive, ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import DatePicker from 'primevue/datepicker';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import ToggleSwitch from 'primevue/toggleswitch';
import Select from 'primevue/select';
import { DateTime } from 'luxon';
import InputSelectMultiple from '@/Components/InputSelectMultiple.vue';
import InputPrice from '@/Components/InputPrice.vue';
import Header from '@/Components/Header.vue';
import ProductPriceTable from '@/Components/Products/ProductPriceTable.vue';
import { InputNumber, RadioButton } from 'primevue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    price_plans: Object,
    contract_term_units: Array
});

const price = reactive({
    code: null,
    cost: 0,
    net: 0
});

const product = reactive({
    name: null,
    price_plan_id: 1,
    supplier_id: null,
    sku: null,
    description: null,
    prices: [],
    is_active: true,
    contract_term_value: null,
    contract_term_unit: null
});

const editingRows = ref([]);
const showModal = ref(false);
const categories = ref([]);
const steps = ref([]);

const saveProduct = () => {
    router.post(
        `/products`,
        {
            ...product,
            expires_at: DateTime.fromJSDate(product.expires_at).toISO() ?? null,
            supplier_id: product.supplier?.id,
            categories: categories.value.map((item) => item.id),
            steps: steps.value.map((item) => item.id)
        },
        {
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
        }
    );
};

const savePrice = async () => {
    let temp = {};
    Object.assign(temp, price);
    product.prices.push(temp);
    Object.assign(price, { code: null, cost: 0, net: 0 });
    showModal.value = !showModal.value;
};

const deletePrice = async (id) => {
    let result = await swal.fire({
        title: 'Are you sure?'
    });

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return;
    }

    product.prices.splice(0, 1);
};

const onRowEditSave = async (event) => {
    let { newData, index } = event;
    product.prices[index] = newData;
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Settings', url: '/settings' },
    { label: 'Products', url: '/products' },
    { label: 'New Product' }
]);
</script>
<template>
    <Head title="New Product" />
    <AuthenticatedLayout>
        <Header title="New Product" :breadcrumbs="items" />
        <tabs class="mt-3">
            <tab title="General" :selected="true">
                <div class="row">
                    <div class="col-6 px-0">
                        <div class="panel my-4">
                            <h3 class="fw-semibold">Product details</h3>
                            <p class="my-3">Product overview including general information.</p>
                            <div class="my-4">
                                <div class="col-6">
                                    <div class="mb-3 d-flex flex-column">
                                        <label class="form-label" for="active-filter"> Active: </label>
                                        <ToggleSwitch id="active-filter" v-model="product.is_active" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-sku" class="form-label">SKU</label>
                                        <input id="product-sku" class="form-control" v-model="product.sku" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-name" class="form-label">Product</label>
                                        <input id="product-name" class="form-control" v-model="product.name" />
                                    </div>

                                    <div class="mb-3">
                                        <label for="product-description" class="form-label">Description</label>
                                        <textarea
                                            id="product-description"
                                            class="form-control"
                                            v-model="product.description"
                                            >{{ product.description }}</textarea
                                        >
                                    </div>

                                    <div class="mb-3">
                                        <label for="product-categories" class="form-label">Category</label>
                                        <InputSelectMultiple
                                            id="product-categories"
                                            url="/categories"
                                            :fields="['name']"
                                            v-model="categories"
                                            placeholder="Select a Category"
                                            :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                                        >
                                        </InputSelectMultiple>
                                    </div>

                                    <div class="mb-3">
                                        <label for="product-expires_at" class="form-label">Expiration date</label>
                                        <DatePicker
                                            id="expire-date"
                                            class="w-100"
                                            v-model="product.expires_at"
                                            placeholder="Expires At"
                                            showIcon
                                            iconDisplay="input"
                                            dateFormat="dd/mm/yy"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel my-4 pb-1">
                            <h3 class="fw-semibold">Prices</h3>
                            <div class="my-4">
                                <div class="col">
                                    <div class="mb-3">
                                        <label class="form-label">Price Plan</label>
                                        <div class="d-flex flex-column">
                                            <div
                                                v-for="plan in props.price_plans"
                                                :key="plan.id"
                                                class="d-flex items-center gap-2 mb-2"
                                            >
                                                <RadioButton
                                                    :inputId="'price_plan_' + plan.id"
                                                    :value="plan.id"
                                                    v-model="product.price_plan_id"
                                                    name="dynamic"
                                                    class="my-auto"
                                                />
                                                <label class="form-label ms-2 mb-0 fs-6" :for="'price_plan_' + plan.id">
                                                    {{ plan.name }}</label
                                                >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Contract terms</label>
                                        <div class="d-flex">
                                            <InputNumber
                                                v-model="product.contract_term_value"
                                                :inputId="'contract_term_value'"
                                                showButtons
                                                class="me-3"
                                            />
                                            <Select
                                                v-model="product.contract_term_unit"
                                                :inputId="'contract_term_unit'"
                                                :inputClass="'my-auto'"
                                                :options="contract_term_units"
                                                class="w-100"
                                                optionLabel="value"
                                                optionValue="name"
                                                placeholder="Select an Unit"
                                            />
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Price</label>
                                        <p class="mb-3">
                                            Prices for this product, including any active special pricing agreements.
                                        </p>
                                        <ProductPriceTable v-model:prices="product.prices" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </tab>
        </tabs>
        <div>
            <PrimaryButton id="btn-save-product" @click="saveProduct()"> Save </PrimaryButton>
        </div>
    </AuthenticatedLayout>
</template>
<style>
.p-toggleswitch {
    &.p-toggleswitch-checked {
        &:has(.p-toggleswitch-input),
        &:has(.p-toggleswitch-input:hover) {
            .p-toggleswitch-slider {
                background: var(--color-primary) !important;
                .p-toggleswitch-handle {
                    background: var(--white) !important;
                }
            }
        }
    }

    &:has(.p-toggleswitch-input:hover) {
        .p-toggleswitch-slider {
            background: var(--bs-border-color) !important;
        }
    }

    .p-toggleswitch-slider {
        background: var(--white);
        border-color: var(--color-grey);
        .p-toggleswitch-handle {
            background: var(--color-grey) !important;
        }
    }
}

.p-breadcrumb-item-link {
    color: var(--color-black) !important;
    font-weight: 500;
}

.form-label {
    color: var(--color-black);
    font-weight: 500;
}

.p-inputnumber-button {
    &:hover {
        background: none !important;
    }
}
</style>
