<script setup>
import { reactive, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DatePicker from 'primevue/datepicker';
import ToggleSwitch from 'primevue/toggleswitch';
import Select from 'primevue/select';
import { DateTime } from 'luxon';
import Header from '@/Components/Header.vue';
import ProductPriceTable from '@/Components/Products/ProductPriceTable.vue';
import { InputNumber, RadioButton } from 'primevue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useBillingFrequency } from '@/Composables/billingFrequency.js';

const props = defineProps({
    price_plans: Object,
    frequency_units: Array
});

const product = reactive({
    name: null,
    price_plan_id: 2,
    supplier_id: null,
    sku: null,
    description: null,
    prices: [],
    is_active: true,
    contract_term_value: null,
    contract_term_unit: null,
    billing_frequency_value: null,
    billing_frequency_unit: null
});

const { contract_length, billingFrequencyUnits, billingFrequencyValues } = useBillingFrequency(
    product,
    props.frequency_units
);

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
        <div class="panel">
            <Header title="New Product" :breadcrumbs="items" />
            <tabs class="mt-3">
                <tab title="General" :selected="true">
                    <div class="row">
                        <div class="col-6 px-0">
                            <div class="panel mt-4 mb-0">
                                <h3 class="fw-semibold">Product details</h3>
                                <p class="my-3 small">Product overview including general information.</p>
                                <div class="my-4">
                                    <div class="col-6">
                                        <div class="mb-3 d-flex flex-column">
                                            <label class="form-label" for="active-filter">Status</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <ToggleSwitch id="active-filter" v-model="product.is_active" />
                                                <span class="small">{{
                                                    product.is_active ? 'Active' : 'Inactive'
                                                }}</span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="product-sku" class="form-label">SKU</label>
                                            <input
                                                id="product-sku"
                                                class="form-control"
                                                maxlength="255"
                                                v-model="product.sku"
                                            />
                                        </div>
                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Product</label>
                                            <input
                                                id="product-name"
                                                class="form-control"
                                                maxlength="255"
                                                v-model="product.name"
                                            />
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
                                            <InputSelectCategories
                                                id="product-categories"
                                                :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                                                v-model="categories"
                                                :select-children="false"
                                            >
                                            </InputSelectCategories>
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
                            <div class="panel mb-4 mt-0 pb-1">
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
                                                    <label
                                                        class="form-label ms-2 mb-0 fs-6"
                                                        :for="'price_plan_' + plan.id"
                                                    >
                                                        {{ plan.name }}</label
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Renewal Terms</label>
                                            <div class="row">
                                                <div class="col-3">
                                                    <InputNumber
                                                        v-model="product.contract_term_value"
                                                        :disabled="product.price_plan_id !== 1"
                                                        :min="0"
                                                        :max="100"
                                                        :inputId="'contract_term_value'"
                                                        :inputClass="'w-100'"
                                                        showButtons
                                                        placeholder="Value"
                                                    />
                                                </div>
                                                <div class="col-6">
                                                    <Select
                                                        v-model="product.contract_term_unit"
                                                        :disabled="product.price_plan_id !== 1"
                                                        :inputId="'contract_term_unit'"
                                                        :inputClass="'my-auto'"
                                                        :options="frequency_units"
                                                        class="h-100"
                                                        optionLabel="value"
                                                        optionValue="name"
                                                        placeholder="Frequency"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Billing frequency</label>
                                            <div class="row">
                                                <div class="col-3">
                                                    <Select
                                                        :disabled="!contract_length"
                                                        v-model="product.billing_frequency_value"
                                                        :inputId="'billing_frequency_value'"
                                                        :inputClass="'my-auto'"
                                                        :options="billingFrequencyValues"
                                                        class="h-100"
                                                        placeholder="Value"
                                                    />
                                                </div>
                                                <div class="col-6">
                                                    <Select
                                                        :disabled="!contract_length"
                                                        v-model="product.billing_frequency_unit"
                                                        :inputId="'billing_frequency_unit'"
                                                        :inputClass="'my-auto'"
                                                        :options="billingFrequencyUnits"
                                                        class="h-100"
                                                        optionLabel="value"
                                                        optionValue="name"
                                                        placeholder="Frequency"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Price</label>
                                            <p class="mb-3 small">
                                                Prices for this product, including any active special pricing
                                                agreements.
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
