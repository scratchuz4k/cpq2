<script setup>
import { reactive, ref, watch } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import InputPrice from '@/Components/InputPrice.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { DataTable, DatePicker } from 'primevue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import ToggleSwitch from 'primevue/toggleswitch';
import Select from 'primevue/select';
import RadioButton from 'primevue/radiobutton';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { DateTime } from 'luxon';
import format from '@/Composables/format.js';
import InputNumber from 'primevue/inputnumber';
import axios from 'axios';

const props = defineProps({
    product: Object,
    price_plans: Object,
    contract_term_units: Array
});

const product = reactive({
    ...props.product,
    expires_at: props.product.expires_at ? new Date(props.product.expires_at) : null
});

const price = reactive({
    code: null,
    cost: 0,
    net: 0,
    product_id: product.id
});

const showModal = ref(false);
const prices = ref(product.prices);
const categories = ref(product.categories ?? []);
const steps = ref(product.steps ?? []);
const editingRows = ref([]);

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

const savePrice = async () => {
    try {
        let res = await axios.post(`/api/prices`, {
            ...price,
            net: parseFloat(price.net) ?? 0,
            cost: parseFloat(price.cost) ?? 0
        });
        prices.value.push(res.data.data);
        showModal.value = !showModal.value;
    } catch (ex) {
        notyf.error(ex.response.data.message);
    }
};

const deletePrice = async (id) => {
    let result = await swal.fire({
        title: 'Are you sure?'
    });

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return;
    }

    try {
        let index = prices.value.findIndex((e) => e.id == id);
        await axios.delete(`/api/prices/${id}`);
        prices.value.splice(index, 1);
    } catch (ex) {
        notyf.error(ex.response.data.message);
    }
};

const onRowEditSave = async (event) => {
    let { newData, index } = event;
    try {
        let res = await axios.put(`/api/prices/${newData.id}`, {
            cost: parseFloat(newData.cost),
            net: parseFloat(newData.net)
        });
        prices.value[index] = res.data.data;
        notyf.success('Updated with success');
    } catch (ex) {
        notyf.error(ex.response.data.message);
    }
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
        <Header :title="'Edit Products: ' + product.name" :breadcrumbs="items">
            <template #badge>
                <span :id="'product-' + product.id + '-status'" class="badge badge-success rounded-pill ms-2">
                    {{ product.status }}
                </span>
            </template>
        </Header>
        <tabs id="tabs-product-edit" class="mt-3">
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
                                        <DataTable
                                            v-model:editingRows="editingRows"
                                            :value="prices"
                                            editMode="row"
                                            dataKey="id"
                                            @row-edit-save="onRowEditSave"
                                            :pt="{
                                                column: {
                                                    bodycell: ({ state }) => ({
                                                        style:
                                                            state['d_editing'] &&
                                                            'padding-top: 0.75rem; padding-bottom: 0.75rem',
                                                        class: 'small'
                                                    }),
                                                    headercell: {
                                                        style: 'color: var(--table-header-dark-grey); background: var(--table-header-light-grey)',
                                                        class: 'small'
                                                    },
                                                    columntitle: {
                                                        class: 'custom-column-title'
                                                    }
                                                }
                                            }"
                                        >
                                            <template #empty>
                                                <div class="d-flex justify-content-end">
                                                    <Button
                                                        class="p-button p-component p-button-icon-only p-button-secondary p-button-rounded p-button-text"
                                                        @click="() => (showModal = !showModal)"
                                                    >
                                                        <font-awesome-icon
                                                            class="text-primary"
                                                            :icon="['fas', 'plus']"
                                                        />
                                                    </Button>
                                                </div>
                                            </template>
                                            <Column field="code" haeder="Code"></Column>
                                            <Column field="cost" header="Cost">
                                                <template #body="{ data, field }">
                                                    {{ format.GBP(data[field]) }}
                                                </template>
                                                <template #editor="{ data, field }">
                                                    <InputPrice v-model="data[field]" />
                                                </template>
                                            </Column>
                                            <Column field="net" header="Net">
                                                <template #body="{ data, field }">
                                                    {{ format.GBP(data[field]) }}
                                                </template>
                                                <template #editor="{ data, field }">
                                                    <InputPrice v-model="data[field]" />
                                                </template>
                                            </Column>
                                            <Column field="net" header="Net SPA">
                                                <template #body="{ data, field }">
                                                    {{ format.GBP(data[field]) }}
                                                </template>
                                            </Column>
                                            <Column field="active_spas" header="Active SPA">
                                                <template #body="slotProps">
                                                    <p v-for="item in slotProps.data.active_spas">
                                                        {{ item.operation.code }}:
                                                        {{
                                                            item.percentage
                                                                ? format.decimalPlaces(item.percentage) + '%'
                                                                : format.GBP(item.margin)
                                                        }}
                                                    </p>
                                                </template>
                                            </Column>
                                            <Column
                                                :rowEditor="true"
                                                v-if="hasRole('Super Admin') || can('update price')"
                                                style="width: 5%"
                                            >
                                                <template #roweditoriniticon>
                                                    <Button
                                                        class="p-button p-component p-button-icon-only p-button-secondary p-button-rounded p-button-text p-datatable-row-editor-init"
                                                        label="Button"
                                                    >
                                                        <font-awesome-icon
                                                            class="text-primary"
                                                            :icon="['far', 'pen-to-square']"
                                                        />
                                                    </Button>
                                                </template>
                                            </Column>
                                            <Column
                                                bodyStyle="text-align:center"
                                                v-if="hasRole('Super Admin') || can('delete price')"
                                                style="width: 5%"
                                            >
                                                <template #body="{ data }">
                                                    <Button
                                                        @click="deletePrice(data.id)"
                                                        class="p-button p-component p-button-icon-only p-button-secondary p-button-rounded p-button-text p-datatable-row-editor-init"
                                                        label="Button"
                                                    >
                                                        <font-awesome-icon :icon="['far', 'trash-can']" />
                                                    </Button>
                                                </template>
                                            </Column>
                                        </DataTable>
                                        <Dialog
                                            v-model:visible="showModal"
                                            modal
                                            :style="{ width: '50rem', marginTop: '4rem' }"
                                            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
                                            :draggable="false"
                                            position="top"
                                        >
                                            <template #header>
                                                <h3>Add new Price</h3>
                                            </template>
                                            <div class="mb-3">
                                                <label class="form-label">Code:</label>
                                                <input class="form-control" v-model="price.code" />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Cost</label>
                                                <InputPrice id="price-cost" v-model="price.cost" />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Price</label>
                                                <InputPrice id="price-price" v-model="price.net" />
                                            </div>

                                            <div class="d-flex justify-content-end">
                                                <button
                                                    class="btn btn-secondary me-2"
                                                    @click="() => (showModal = !showModal)"
                                                >
                                                    Cancel
                                                </button>
                                                <button class="btn btn-primary" @click="savePrice()">Save</button>
                                            </div>
                                        </Dialog>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </tab>
            <tab
                :title="`Linked Products <span class='badge badge-primary rounded-pill ms-2'>${product.children.length}</span>`"
            >
                <ProductLinkedManage v-model="product" />
            </tab>
            <tab :title="`Addons <span class='badge badge-primary rounded-pill ms-2'>${product.addons.length}</span>`">
                <ProductAddonManage v-model="product" />
            </tab>
        </tabs>

        <div>
            <PrimaryButton @click="saveProduct()"> Save </PrimaryButton>
            <button id="delete-button" class="btn btn-outline-primary ms-2" @click="deleteProduct(product.id)">
                Delete
            </button>
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

#contract_term_value {
    max-width: 120px;
}

.p-inputnumber-button {
    &:hover {
        background: none !important;
    }
}
.custom-column-title {
    &.p-datatable-column-title {
        font-weight: 500;
        color: var(--table-header-dark-grey) !important;
    }
}
</style>
