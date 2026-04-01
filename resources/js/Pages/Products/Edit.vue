<script setup>
import { reactive, ref, watch } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputPrice from '@/Components/InputPrice.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { DataTable, DatePicker } from 'primevue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import ToggleSwitch from 'primevue/toggleswitch';
import Select from 'primevue/select';
import { DateTime } from 'luxon';
import format from '@/Composables/format.js';

const props = defineProps({
    product: Object,
    price_plans: Object
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
        parent_id: product.parent?.id ?? null,
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
    <Head :title="'Edit Product #' + product.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Product #' + product.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveProduct()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <tabs class="mt-3">
                <tab title="General" :selected="true">
                    <div class="my-3">
                        <p>
                            This section provides an overview of the product, including general information and
                            associated suppliers.
                        </p>
                    </div>
                    <div class="row my-4">
                        <div class="col-6">
                            <div class="mb-3 d-flex flex-column">
                                <label class="form-label" for="product-status"> Status: </label>
                                <div>
                                    <span
                                        v-tooltip
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-html="true"
                                        :data-bs-original-title="'This product is ' + product.status"
                                        class="badge border border-primary text-primary opacity-75 ms-1 lh-sm"
                                    >
                                        {{ product.status }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3 d-flex flex-column">
                                <label class="form-label" for="active-filter"> Active: </label>
                                <ToggleSwitch id="active-filter" v-model="product.is_active">
                                    <template #handle>
                                        <font-awesome-icon
                                            :icon="
                                                product.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']
                                            "
                                            class="text-primary"
                                        />
                                    </template>
                                </ToggleSwitch>
                            </div>
                            <div class="mb-3">
                                <label for="product-sku" class="form-label">SKU:</label>
                                <input id="product-sku" class="form-control" v-model="product.sku" />
                            </div>
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Name:</label>
                                <input id="product-name" class="form-control" v-model="product.name" />
                            </div>

                            <div class="mb-3">
                                <label for="product-description" class="form-label">Description:</label>
                                <textarea id="product-description" class="form-control" v-model="product.description">{{
                                    product.description
                                }}</textarea>
                            </div>

                            <div class="mb-5">
                                <label for="product-expires_at" class="form-label">Expires At:</label>
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

                            <h4>Relations</h4>
                            <hr />
                            <div class="mb-3">
                                <label class="form-label">Price Plan</label>
                                <Select
                                    v-model="product.price_plan_id"
                                    :inputId="'price_plan'"
                                    :options="props.price_plans"
                                    class="w-100"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Select an Price Plan"
                                />
                            </div>

                            <div class="mb-3">
                                <label for="product-categories" class="form-label">Categories:</label>
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
                                <label for="product-steps" class="form-label">Steps:</label>
                                <InputSelectMultiple
                                    id="product-steps"
                                    url="/steps"
                                    :fields="['name', 'solution_type.name']"
                                    v-model="steps"
                                    placeholder="Select a Step"
                                    :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                                >
                                </InputSelectMultiple>
                            </div>
                            <div class="mb-3">
                                <label for="product-supplier" class="form-label">Supplier:</label>
                                <InputSelect
                                    id="product-supplier"
                                    url="/suppliers"
                                    :fields="['name']"
                                    v-model="product.supplier"
                                    placeholder="Select a Supplier"
                                    :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                                >
                                </InputSelect>
                            </div>
                            <div class="mb-3">
                                <label for="product-parent" class="form-label">Parent:</label>
                                <div class="input-group p-0">
                                    <InputSelect
                                        id="product-parent"
                                        url="/products"
                                        :fields="['name']"
                                        class="w-100"
                                        v-model="product.parent"
                                        placeholder="Select a Parent"
                                        :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </tab>
                <tab title="Prices" v-if="hasRole('Super Admin') || can('viewAny price')">
                    <div class="my-3">
                        <p>
                            Here are the available prices for this product, including details like cost and net value.
                        </p>
                    </div>
                    <div class="row my-4">
                        <div class="col-8">
                            <div class="mb-3">
                                <DataTable
                                    v-model:editingRows="editingRows"
                                    :value="prices"
                                    editMode="row"
                                    dataKey="id"
                                    @row-edit-save="onRowEditSave"
                                    :pt="{
                                        table: { style: 'min-width: 50rem' },
                                        column: {
                                            bodycell: ({ state }) => ({
                                                style:
                                                    state['d_editing'] &&
                                                    'padding-top: 0.75rem; padding-bottom: 0.75rem'
                                            })
                                        }
                                    }"
                                >
                                    <Column field="code" header="Code" style="width: 10%"></Column>
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
                                    <Column field="net_spa" header="Net SPA">
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
                            </div>
                            <div
                                class="mb-3 d-flex justify-content-end"
                                v-if="hasRole('Super Admin') || can('create price')"
                            >
                                <button
                                    class="btn btn-primary"
                                    @click="() => (showModal = !showModal)"
                                    v-if="prices.length == 0"
                                >
                                    Add Price
                                </button>
                            </div>
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
                                    <input class="form-control" type="number" v-model="price.cost" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input class="form-control" type="number" v-model="price.net" />
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-secondary me-2" @click="() => (showModal = !showModal)">
                                        Cancel
                                    </button>
                                    <button class="btn btn-primary" @click="savePrice()">Save</button>
                                </div>
                            </Dialog>
                        </div>
                    </div>
                </tab>
            </tabs>
        </div>
    </AuthenticatedLayout>
</template>
