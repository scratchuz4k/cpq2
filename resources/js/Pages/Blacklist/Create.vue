<script setup>
import {reactive, ref} from "vue";
import {can, hasRole} from "@composables/useAuth";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head, router} from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Select from "primevue/select";

const props = defineProps({
    providers: Array,
    operators: Array,
    contract_terms: Array,
    speed_units: Array
});

const blacklisted_item = reactive({
    name: null,
    provider: null,
    sub_provider: null,
    bearer: {
        operator: null,
        value: null,
        unit: null
    },
    speed: {
        operator: null,
        value: null,
        unit: null
    },
    contract_term: null
});

const editingRows = ref([]);
const showModal = ref(false);
const categories = ref([]);

const saveProduct = () => {
    router.post(
        `/products`,
        {
            ...product,
            supplier_id: product.supplier?.id,
            brand_id: product.brand?.id,
            categories: categories.value.map((item) => item.id),
        },
        {
            onSuccess: (page) => {
                notyf.success("Created with Success");
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
    price.tax = parseFloat((price.net * (price.tax_rate / 100)).toFixed(2));
    price.gross = parseFloat((price.net * (1 + price.tax_rate / 100)).toFixed(2));
    let temp = {};
    Object.assign(temp, price);
    product.prices.push(temp);
    Object.assign(price, {code: null, tax_rate: 0, net: 0, tax: 0, gross: 0});
    showModal.value = !showModal.value;
};

const deletePrice = async (id) => {
    let result = await swal.fire({
        title: "Are you sure?",
    });

    if (["cancel", "backdrop"].includes(result.dismiss)) {
        return;
    }

    product.prices.splice(0, 1);
};

const onRowEditSave = async (event) => {
    let {newData, index} = event;

    newData.tax = parseFloat((newData.net * (newData.tax_rate / 100)).toFixed(2));
    newData.gross = parseFloat((newData.net * (1 + newData.tax_rate / 100)).toFixed(2));

    product.prices[index] = newData;
};

const items = ref([
    {label: "Home", url: "/"},
    {label: "My Account", url: "/myaccount"},
    {label: "Products", url: "/products"},
    {label: "New Product"},
]);
</script>
<template>
    <Head title="New Product"/>
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
            <div class="row">
                <div class="col-6">
                    <div class="mb-3">
                        <label class="form-label">Product Name:</label>
                        <input class="form-control" v-model="blacklisted_item.name"/>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Provider:</label>
                        <select class="form-select" v-model="blacklisted_item.provider">
                            <option v-for="provider in props.providers" :value="provider.value">
                                {{ provider.name }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subprovider:</label>
                        <select class="form-select" v-model="blacklisted_item.provider">
                            <option v-for="provider in props.providers" :value="provider.value">
                                {{ provider.name }}
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bearer:</label>
                        <select class="form-select" v-model="blacklisted_item.bearer.operator">
                            <option v-for="operator in props.operators" :value="operator.value">
                                {{ operator.description }}
                            </option>
                        </select>

                        <div class="d-flex mt-2">
                            <input placeholder="Value" type="number" class="form-control text-center input-qty me-1" v-model="blacklisted_item.bearer.value">
                            <select class="form-select" v-model="blacklisted_item.bearer.operator" :style="{ width: 'auto' }">
                                <option disabled :value="null">Units</option>
                                <option v-for="unit in props.speed_units" :value="blacklisted_item.bearer.unit">
                                    {{ unit.value }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Speed:</label>
                        <select class="form-select" v-model="blacklisted_item.speed">
                            <option v-for="operator in props.operators" :value="operator.value">
                                {{ operator.description }}
                            </option>
                        </select>

                        <div class="d-flex mt-2">
                            <input placeholder="Value" type="number" class="form-control text-center input-qty me-1" v-model="blacklisted_item.speed.value">
                            <select class="form-select" v-model="blacklisted_item.speed.operator" :style="{ width: 'auto' }">
                                <option disabled :value="null">Units</option>
                                <option v-for="unit in props.speed_units" :value="blacklisted_item.speed.unit">
                                    {{ unit.value }}
                                </option>
                            </select>
                        </div>

                    </div>


                    <div class="mb-3">
                        <label class="form-label">Contract Term:</label>
                        <select class="form-select" v-model="blacklisted_item.contract_term">
                            <option v-for="term in props.contract_terms" :value="term.value">
                                {{ term.value }} months
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
