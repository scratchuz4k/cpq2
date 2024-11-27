<script setup>
import { reactive, ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Dialog from "primevue/dialog";


const props = defineProps({
    product: Object,
    brands: Object,
    suppliers: Object
})

const price = reactive({
    code: null,
    tax_rate: 0,
    value: 0,
})

const showModal = ref(true)
const prices = ref(props.product.prices)
const editingRows = ref([]);

const saveProduct = () => {
    router.put(`/products/${props.product.id}`, props.product,
        {
            onSuccess: (page) => {
                notyf.success("Updated with Success")
            },
            onError: (errors) => {
                let string = ''
                Object.keys(errors).forEach((key) => {
                    string += errors[key] + '<br>'
                })
                notyf.error(string)
            },
        }
    )
}

const onRowEditSave = (event) => {
    let { newData, index } = event;

    alert(newData);
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Products', url: '/products' },
    { label: 'Edit Product' },
]);

</script>
<template>

    <Head :title="'Edit Product #' + product.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Product #' + product.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveProduct()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
            <tabs class="mt-3">
                <tab title="General" :selected="true">
                    <div class="row my-4">
                        <div class="col-6">
                            <div class="row mb-3">
                                <label for="product-name" class="form-label">Name:</label>
                                <input id="product-name" class="form-control" v-model="product.name">
                            </div>
                            <div class="row mb-3">
                                <label for="supplier-name" class="form-label">Supplier:</label>
                                <select class="form-select" v-model="product.supplier_id">
                                    <option v-for="supplier in suppliers" :value="supplier.id">{{ supplier.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="row mb-3">
                                <label for="brand-name" class="form-label">Brand:</label>
                                <select class="form-select" v-model="product.brand_id">
                                    <option v-for="brand in brands" :value="brand.id">{{ brand.name }}</option>
                                </select>
                            </div>

                            <div class="row mb-3">
                                <label for="product-code" class="form-label">Code:</label>
                                <input id="product-code" class="form-control" v-model="product.code">
                            </div>

                            <div class="row mb-3">
                                <label for="product-description" class="form-label">Description:</label>
                                <textarea id="product-description" class="form-control" v-model="product.description">{{
                                    product.description
                                }}</textarea>
                            </div>
                        </div>
                    </div>
                </tab>
                <tab title="Prices">
                    <div class="my-3">
                        <DataTable v-model:editingRows="editingRows" :value="prices" editMode="row" dataKey="id"
                            @row-edit-save="onRowEditSave" :pt="{
                                table: { style: 'min-width: 50rem' },
                                column: {
                                    bodycell: ({ state }) => ({
                                        style: state['d_editing'] && 'padding-top: 0.75rem; padding-bottom: 0.75rem'
                                    })
                                }
                            }">
                            <Column field="code" header="Code" style="width: 20%">
                            </Column>
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
                            <Column field="tax" header="Tax" style="width: 20%">
                                <template #editor="{ data, field }">
                                    <InputText v-model="data[field]" fluid />
                                </template>
                            </Column>
                            <Column field="gross" header="Gross" style="width: 20%">
                                <template #editor="{ data, field }">
                                    <InputText v-model="data[field]" fluid />
                                </template>
                            </Column>

                            <Column :rowEditor="true" bodyStyle="display:flex"> </Column>
                            <Column bodyStyle="text-align:center">
                                <template #body="slotProps">
                                    <Button
                                        class="p-button p-component p-button-icon-only p-button-secondary p-button-rounded p-button-text p-datatable-row-editor-init"
                                        label="Button">
                                        <font-awesome-icon :icon="['far', 'trash-can']" />
                                    </Button>
                                </template>
                            </Column>
                        </DataTable>

                    </div>
                    <div class="mb-3 d-flex justify-content-end">
                        <button class="btn btn-primary" @click="() => showModal = !showModal">Add Price</button>
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
                            <input class="form-control" type="number" v-model="price.value" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tax Rate</label>
                            <input class="form-control" type="number" v-model="price.tax_rate" />
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-secondary me-2" @click="() => showModal = !showModal"> Cancel</button>
                            <button class="btn btn-primary" @click="savePrice()"> Save</button>
                        </div>
                    </Dialog>
                </tab>
            </tabs>

        </div>
    </AuthenticatedLayout>
</template>
