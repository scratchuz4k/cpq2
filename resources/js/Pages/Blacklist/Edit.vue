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
    blacklisted_item: Object,
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

const changeValueFormat = (item) => {
    if (item.operator === 'between') {
        item.value = []
    } else {
        item.value = null
    }
}

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
                    <PrimaryButton id="btn-save-item" @click="saveItem()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row">
                <div class="col-6">
                    <div class="mb-3">
                        <label class="form-label">Product Name:</label>
                        <input id="item-name" class="form-control" v-model="blacklisted_item.productName"/>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Provider:</label>
                        <select id="item-provider" class="form-select" v-model="blacklisted_item.providerName">
                            <option v-for="provider in props.providers" :value="provider.name">
                                {{ provider.name }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="item-subprovider" class="form-label">Subprovider:</label>
                            <select id="item-subprovider" class="form-select" v-model="blacklisted_item.connectivitySupplierGroup">
                                <option v-for="subprovider in props.subproviders" :value="subprovider">
                                    {{ subprovider }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bearer:</label>
                        <select id="item-bearer-operator" class="form-select" v-model="blacklisted_item.bearer.operator" @change="changeValueFormat(blacklisted_item.bearer)">
                            <option v-for="operator in props.operators" :value="operator.name">
                                {{ operator.description }}
                            </option>
                        </select>

                        <div class="d-flex mt-2">
                            <div v-if="blacklisted_item.bearer.operator === 'between' && Array.isArray(blacklisted_item.bearer.value)" class="d-flex">
                                <input id="item-bearer-value-0" placeholder="Value" type="number" class="form-control text-center input-qty me-1" v-model="blacklisted_item.bearer.value[0]">
                                <p class="mx-2 pt-1">to</p>
                                <input id="item-bearer-value-1" placeholder="Value" type="number" class="form-control text-center input-qty me-1" v-model="blacklisted_item.bearer.value[1]">
                            </div>
                            <div v-else>
                                <input id="item-bearer-value" placeholder="Value" type="number" class="form-control text-center input-qty me-1" v-model="blacklisted_item.bearer.value">
                            </div>
                            <select id="item-bearer-unit" class="form-select" v-model="blacklisted_item.bearer.unit" :style="{ width: 'auto' }">
                                <option disabled :value="null">Units</option>
                                <option v-for="unit in props.speed_units" :value="unit.value">
                                    {{ unit.value }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Speed:</label>
                        <select id="item-speed-operator" class="form-select" v-model="blacklisted_item.speed.operator" @change="changeValueFormat(blacklisted_item.bearer)">
                            <option v-for="operator in props.operators" :value="operator.name">
                                {{ operator.description }}
                            </option>
                        </select>
                        <div class="d-flex mt-2">
                            <div v-if="blacklisted_item.speed.operator === 'between' && Array.isArray(blacklisted_item.speed.value)" class="d-flex">
                                <input id="item-speed-value-0" placeholder="Value" type="number" class="form-control text-center input-qty me-1" v-model="blacklisted_item.speed.value[0]">
                                <input id="item-speed-value-1" placeholder="Value" type="number" class="form-control text-center input-qty me-1" v-model="blacklisted_item.speed.value[1]">
                            </div>
                            <div v-else>
                                <input id="item-speed-value" placeholder="Value" type="number" class="form-control text-center input-qty me-1" v-model="blacklisted_item.speed.value">
                            </div>
                            <select id="item-speed-unit" class="form-select" v-model="blacklisted_item.speed.unit" :style="{ width: 'auto' }">
                                <option disabled :value="null">Units</option>
                                <option v-for="unit in props.speed_units" :value="unit.value">
                                    {{ unit.value }}
                                </option>
                            </select>
                        </div>
                    </div>


                    <div class="mb-3">
                        <label class="form-label">Contract Term:</label>
                        <select id="item-contract_terms" class="form-select" v-model="blacklisted_item.contractTermMonths">
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
