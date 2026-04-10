<script setup>
import { reactive, ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import DatePicker from 'primevue/datepicker';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import ToggleSwitch from 'primevue/toggleswitch';
import Select from 'primevue/select';
import { DateTime } from 'luxon';
import InputSelectMultiple from '@/Components/InputSelectMultiple.vue';
import InputPrice from '@/Components/InputPrice.vue';

const props = defineProps({
    price_plans: Object
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
    is_active: true
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
        <div class="panel">
            <Header title="New Product" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-product" @click="saveProduct()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <tabs class="mt-3">
                <tab title="General" :selected="true">
                    <div class="my-3">
                        <p>
                            This section provides an overview of the product, including general information, and
                            associated suppliers.
                        </p>
                    </div>
                    <div class="my-3 col-6">
                        <div class="mb-3 d-flex flex-column">
                            <label class="form-label" for="active-filter"> Active: </label>
                            <ToggleSwitch id="active-filter" v-model="product.is_active">
                                <template #handle>
                                    <font-awesome-icon
                                        :icon="product.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
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
                            <textarea
                                id="product-description"
                                class="form-control"
                                v-model="product.description"
                            ></textarea>
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
                                placeholder="Select a Categories"
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
                                placeholder="Select Steps"
                                :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                            >
                            </InputSelectMultiple>
                        </div>
                        <div class="mb-3">
                            <label for="supplier-name" class="form-label">Supplier:</label>
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
                                    :value="product.prices"
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
                                    id="add-price-btn"
                                    class="btn btn-primary"
                                    @click="() => (showModal = !showModal)"
                                    v-if="product.prices.length == 0"
                                >
                                    Add Price
                                </button>
                            </div>
                            <Dialog
                                v-model:visible="showModal"
                                modal
                                :style="{ width: '50rem', marginTop: '4rem' }"
                                :breakpoints="{
                                    '1199px': '75vw',
                                    '575px': '90vw'
                                }"
                                :draggable="false"
                                position="top"
                            >
                                <template #header>
                                    <h3>Add new Price</h3>
                                </template>
                                <div class="mb-3">
                                    <label class="form-label">Code:</label>
                                    <input id="price-code" class="form-control" v-model="price.code" />
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
