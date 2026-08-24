<script setup>
import { reactive, ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { DatePicker, ToggleSwitch, Select, RadioButton, InputNumber } from 'primevue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { DateTime } from 'luxon';
import ProductPriceTable from '@/Components/Products/ProductPriceTable.vue';
import InputSelectCategories from '@/Components/Categories/InputSelectCategories.vue';
import { useBillingFrequency } from '@/Composables/billingFrequency.js';

const props = defineProps({
    product: Object,
    price_plans: Object,
    frequency_units: Array
});

const product = reactive({
    ...props.product,
    expires_at: props.product.expires_at ? new Date(props.product.expires_at) : null
});

const price = reactive({
    code: null,
    cost: 0,
    net: 0,
    net_spa: 0,
    product_id: product.id
});

const prices = ref(product.prices);
const categories = ref(product.categories ?? []);
const steps = ref(product.steps ?? []);

const { contract_length, billingFrequencyUnits, billingFrequencyValues } = useBillingFrequency(
    product,
    props.frequency_units
);

const expiresAt = computed(() => {
    if (!product.expires_at) {
        return null;
    }
    return new Date(product.expires_at);
});

const isExpired = computed(() => {
    const date = expiresAt.value;
    if (!date) {
        return false;
    }
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return date < today;
});

const canToggleActive = computed(() => {
    return !(product.status !== 'Inactive' && isExpired.value);
});

const notifyExpired = () => {
    notyf.error('This product could not be activated because the expiration date is in the past.');
};

const isActiveProxy = computed({
    get: () => product.is_active,
    set: (value) => {
        if (value && !canToggleActive.value) {
            notifyExpired();
            return;
        }
        product.is_active = value;
    }
});

const saveProduct = () => {
    let temp = {
        ...product,
        expires_at: product.expires_at ? DateTime.fromJSDate(product.expires_at).toISO() : null,
        supplier_id: product.supplier?.id ?? null,
        categories: categories.value.map((item) => item.id),
        steps: steps.value.map((item) => item.id)
    };

    router.put(`/products/${product.id}`, temp, {
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

const deleteProduct = async (id) => {
    swal.fire({
        title: 'Are you sure?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return;
        }
        router.delete(`/products/${id}`, {
            onError: (errors) => {
                notyf.error(errors?.message ?? 'Something went wrong');
            }
        });
    });
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Settings', url: '/settings' },
    { label: 'Products', url: '/products' },
    { label: 'Edit Product' }
]);
</script>
<template>
    <Head :title="'Edit Products: ' + product.name" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Products: ' + product.name" :breadcrumbs="items">
                <template #badge>
                    <span
                        :id="'product-' + product.id + '-status'"
                        :class="[
                            'badge',
                            'rounded-pill',
                            'ms-2',
                            product.status === 'Active'
                                ? 'badge-success'
                                : product.status === 'Inactive'
                                  ? 'badge-danger'
                                  : 'badge-muted'
                        ]"
                    >
                        {{ product.status }}
                    </span>
                </template>
            </Header>
            <tabs id="tabs-product-edit" class="mt-3">
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
                                            <div class="d-flex align-items-center gap-2 position-relative">
                                                <ToggleSwitch
                                                    id="active-filter"
                                                    v-model="isActiveProxy"
                                                    :disabled="!canToggleActive"
                                                    style="z-index: 0"
                                                />
                                                <button
                                                    v-if="!canToggleActive"
                                                    type="button"
                                                    class="position-absolute top-0 start-0 w-100 h-100 border-0 bg-transparent"
                                                    @click.prevent="notifyExpired"
                                                    aria-label="Cannot activate because expiration date is in the past"
                                                />
                                                <span class="small">
                                                    {{
                                                        isActiveProxy
                                                            ? 'Active'
                                                            : product.status === 'Retired'
                                                              ? 'Retired'
                                                              : 'Inactive'
                                                    }}
                                                </span>
                                            </div>
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
                                                        placeholder="Select an Unit"
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
                                                        placeholder="Select a Value"
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
                                                        placeholder="Select an Unit"
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
                                            <ProductPriceTable v-model:prices="prices" :productId="product.id" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </tab>
                <tab
                    :title="`Linked Products <span class='badge badge-primary rounded-pill ms-2'>${product.linked_children.length}</span>`"
                >
                    <ProductLinkedManage v-model="product" />
                </tab>
                <tab
                    :title="`Addons <span class='badge badge-primary rounded-pill ms-2'>${product.addons_children.length}</span>`"
                >
                    <ProductAddonManage v-model="product" />
                </tab>

                <tab
                    :title="`Bundle <span class='badge badge-primary rounded-pill ms-2'>${product.bundles_children.length}</span>`"
                >
                    <ProductBundleManage v-model="product" />
                </tab>
            </tabs>

            <div>
                <PrimaryButton @click="saveProduct()"> Save </PrimaryButton>
                <button id="delete-button" class="btn btn-outline-primary ms-2" @click="deleteProduct(product.id)">
                    Delete
                </button>
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

    &.p-disabled,
    &.p-toggleswitch-disabled {
        .p-toggleswitch-slider {
            background: var(--white) !important;
            border-color: var(--color-grey) !important;

            .p-toggleswitch-handle {
                background: var(--color-grey) !important;
                opacity: 1 !important;
            }
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
