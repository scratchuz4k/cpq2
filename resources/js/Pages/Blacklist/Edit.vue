<script setup>
import {reactive, ref} from "vue";
import {can, hasRole} from "@composables/useAuth";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head, router, useForm} from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Select from "primevue/select";

const props = defineProps({
    blacklisted_item: Array,
    providers: Array,
    subproviders: Array,
    operators: Array,
    contract_terms: Array,
    speed_units: Array
});

const blacklisted_item = useForm(props.blacklisted_item)

const saveItem = () => {
    router.put(
        `/blacklist/${blacklisted_item.id}`,
        blacklisted_item,
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

const items = ref([
    {label: "Home", url: "/"},
    {label: "My Account", url: "/myaccount"},
    {label: "Products", url: "/products"},
    {label: "BlackList", url: "/blacklist"},
    {label: "Edit Item"},
]);
</script>
<template>
    <Head :title="'Edit Item #' + blacklisted_item.id"/>
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Item #' + blacklisted_item.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-product" @click="saveItem()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row">
                <div class="col-6">
                    <div class="mb-3">
                        <label class="form-label">Product Name:</label>
                        <input class="form-control" v-model="blacklisted_item.productName"/>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Provider:</label>
                        <select class="form-select" v-model="blacklisted_item.providerName">
                            <option v-for="provider in props.providers" :value="provider.name">
                                {{ provider.name }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="subprovider" class="form-label">Subprovider:</label>
                            <Select :inputId="'subprovider'" v-model="blacklisted_item.connectivitySupplierGroup" editable :options="props.subproviders"
                                    placeholder="Enter a Subprovider" class="w-100" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bearer:</label>
                        <select class="form-select" v-model="blacklisted_item.bearer.operator">
                            <option v-for="operator in props.operators" :value="operator.name">
                                {{ operator.description }}
                            </option>
                        </select>

                        <div class="d-flex mt-2">
                            <input placeholder="Value" type="number" class="form-control text-center input-qty me-1" v-model="blacklisted_item.bearer.value">
                            <select class="form-select" v-model="blacklisted_item.bearer.unit" :style="{ width: 'auto' }">
                                <option disabled :value="null">Units</option>
                                <option v-for="unit in props.speed_units" :value="unit.value">
                                    {{ unit.value }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Speed:</label>
                        <select class="form-select" v-model="blacklisted_item.speed.operator">
                            <option v-for="operator in props.operators" :value="operator.name">
                                {{ operator.description }}
                            </option>
                        </select>
                        <div class="d-flex mt-2">
                            <input placeholder="Value" type="number" class="form-control text-center input-qty me-1" v-model="blacklisted_item.speed.value">
                            <select class="form-select" v-model="blacklisted_item.speed.unit" :style="{ width: 'auto' }">
                                <option disabled :value="null">Units</option>
                                <option v-for="unit in props.speed_units" :value="unit.value">
                                    {{ unit.value }}
                                </option>
                            </select>
                        </div>
                    </div>


                    <div class="mb-3">
                        <label class="form-label">Contract Term:</label>
                        <select class="form-select" v-model="blacklisted_item.contractTermMonths">
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
