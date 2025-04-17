<script setup>
import { reactive, ref } from "vue";
import { can, hasRole } from "@composables/useAuth";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Dialog from "primevue/dialog";

const props = defineProps({
    product: Object,
    price_plans: Object,
});

const price = reactive({
    code: null,
    tax_rate: 0,
    net: 0,
    product_id: props.product.id,
});

const showModal = ref(false);
const prices = ref(props.product.prices);
const categories = ref(props.product.categories[0]);
const editingRows = ref([]);

const saveProduct = () => {
    router.put(
        `/products/${props.product.id}`,
        {
            ...props.product,
            supplier_id: props.product.supplier?.id,
            brand_id: props.product.brand?.id,
            categories: categories.value.id,
        },
        {
            onSuccess: (page) => {
                notyf.success("Updated with Success");
            },
            onError: (errors) => {
                let string = "";
                Object.keys(errors).forEach((key) => {
                    string += errors[key] + "<br>";
                });
                notyf.error(string);
            },
        }
    );
};

const savePrice = async () => {
    try {
        let res = await axios.post(`/api/prices`, price);
        prices.value.push(res.data.data);
        showModal.value = !showModal.value;
    } catch (ex) {
        notyf.error(ex.response.data.message);
    }
};

const deletePrice = async (id) => {
    let result = await swal.fire({
        title: "Are you sure?",
    });

    if (["cancel", "backdrop"].includes(result.dismiss)) {
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
            tax_rate: newData.tax_rate,
            net: newData.net,
        });
        prices.value[index] = res.data.data;
        notyf.success("Updated with success");
    } catch (ex) {
        notyf.error(ex.response.data.message);
    }
};

const items = ref([
    { label: "Home", url: "/" },
    { label: "My Account", url: "/myaccount" },
    { label: "Products", url: "/products" },
    { label: "Edit Product" },
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
                            This section provides an overview of the product, including general
                            information, associated brands, and suppliers.
                        </p>
                    </div>
                    <div class="row my-4">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="product-code" class="form-label">Code:</label>
                                <input id="product-code" class="form-control" v-model="product.code" />
                            </div>
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Name:</label>
                                <input id="product-name" class="form-control" v-model="product.name" />
                            </div>

                            <div class="mb-5">
                                <label for="product-description" class="form-label">Description:</label>
                                <textarea id="product-description" class="form-control" v-model="product.description">{{
                                    product.description }}</textarea>
                            </div>
                            <h4>Relations</h4>
                            <hr />
                            <div class="mb-3">
                                <label class="form-label">Price Plan</label>
                                <select class="form-select" v-model="product.price_plan_id">
                                    <option v-for="price_plan in props.price_plans" :value="price_plan.id">
                                        {{ price_plan.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="supplier-name" class="form-label">Supplier:</label>
                                <InputSelect id="product-supplier" url="/suppliers" field="name"
                                    v-model="product.supplier">
                                </InputSelect>
                            </div>

                            <div class="mb-3">
                                <label for="brand-name" class="form-label">Brand:</label>
                                <InputSelect id="product-brand" url="/brands" field="name" v-model="product.brand">
                                </InputSelect>
                            </div>

                            <div class="mb-3">
                                <label for="brand-name" class="form-label">Categories:</label>
                                <InputSelect id="product-categories" url="/categories" field="name"
                                    v-model="categories">
                                </InputSelect>
                            </div>
                        </div>
                    </div>
                </tab>
                <tab title="Prices" v-if="hasRole('Super Admin') || can('viewAny price')">
                    <div class="my-3">
                        <p>
                            Here are the available prices for this product, including details like tax
                            and gross value.
                        </p>
                    </div>
                    <div class="row my-4">
                        <div class="col-8">
                            <div class="mb-3">
                                <DataTable v-model:editingRows="editingRows" :value="prices" editMode="row" dataKey="id"
                                    @row-edit-save="onRowEditSave" :pt="{
                                        table: { style: 'min-width: 50rem' },
                                        column: {
                                            bodycell: ({ state }) => ({
                                                style:
                                                    state['d_editing'] &&
                                                    'padding-top: 0.75rem; padding-bottom: 0.75rem',
                                            }),
                                        },
                                    }">
                                    <Column field="code" header="Code" style="width: 10%"> </Column>
                                    <Column field="tax_rate" header="Tax Rate" style="width: 20%">
                                        <template #editor="{ data, field }">
                                            <InputText v-model="data[field]" fluid />
                                        </template>
                                    </Column>
                                    <Column field="net" header="Net" style="width: 20%">
                                        <template #editor="{ data, field }">
                                            <InputText v-model="data[field]" fluid />
                                        </template>
                                    </Column>
                                    <Column field="tax" header="Tax" style="width: 20%"></Column>
                                    <Column field="gross" header="Gross" style="width: 20%"></Column>

                                    <Column :rowEditor="true" v-if="hasRole('Super Admin') || can('update price')">
                                    </Column>
                                    <Column bodyStyle="text-align:center"
                                        v-if="hasRole('Super Admin') || can('delete price')">
                                        <template #body="{ data }">
                                            <Button @click="deletePrice(data.id)"
                                                class="p-button p-component p-button-icon-only p-button-secondary p-button-rounded p-button-text p-datatable-row-editor-init"
                                                label="Button">
                                                <font-awesome-icon :icon="['far', 'trash-can']" />
                                            </Button>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>
                            <div class="mb-3 d-flex justify-content-end"
                                v-if="hasRole('Super Admin') || can('create price')">
                                <button class="btn btn-primary" @click="() => (showModal = !showModal)"
                                    v-if="prices.length == 0">
                                    Add Price
                                </button>
                            </div>
                            <Dialog v-model:visible="showModal" modal :style="{ width: '50rem', marginTop: '4rem' }"
                                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" :draggable="false" position="top">
                                <template #header>
                                    <h3>Add new Price</h3>
                                </template>
                                <div class="mb-3">
                                    <label class="form-label">Code:</label>
                                    <input class="form-control" v-model="price.code" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input class="form-control" type="number" v-model="price.net" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tax Rate</label>
                                    <input class="form-control" type="number" v-model="price.tax_rate" />
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
