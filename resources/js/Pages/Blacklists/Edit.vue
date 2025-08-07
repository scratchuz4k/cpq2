<script setup>
import { reactive, ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    blacklisted_item: Object,
    providers: Array,
    subproviders: Array,
    operators: Array,
    contract_terms: Array,
    speed_units: Array
});

const blacklisted_item = reactive(props.blacklisted_item.data);

const saveItem = () => {
    router.put(`/blacklists/${blacklisted_item.id}`, blacklisted_item, {
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

const changeValueFormat = (item) => {
    if (item.operator === 'between') {
        item.value = [];
    } else {
        item.value = null;
    }
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Products', url: '/products' },
    { label: 'Blacklist', url: '/blacklists' },
    { label: 'Edit Item' }
]);
</script>
<template>
    <Head :title="'Edit Item #' + blacklisted_item.id" />
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
                    <div class="mb-3 d-flex flex-column">
                        <label class="form-label" for="active-filter"> Active: </label>
                        <ToggleSwitch id="active-filter" v-model="blacklisted_item.is_active">
                            <template #handle>
                                <font-awesome-icon
                                    :icon="
                                        blacklisted_item.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']
                                    "
                                    class="text-primary"
                                />
                            </template>
                        </ToggleSwitch>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Name:</label>
                        <input
                            id="item-name"
                            placeholder="Product Name"
                            class="form-control"
                            v-model="blacklisted_item.productName"
                        />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Provider:</label>
                        <Select
                            v-model="blacklisted_item.providerName"
                            :inputId="'item-provider'"
                            :options="props.providers"
                            class="w-100"
                            optionLabel="name"
                            optionValue="name"
                            placeholder="Select a Provider"
                        />
                    </div>

                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="item-subprovider" class="form-label">Subprovider:</label>
                            <Select
                                v-model="blacklisted_item.connectivitySupplierGroup"
                                :inputId="'item-subprovider'"
                                :options="props.subproviders"
                                class="w-100"
                                optionLabel="value"
                                optionValue="value"
                                placeholder="Select a Subprovider"
                            />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bearer:</label>
                        <Select
                            v-model="blacklisted_item.bearer.operator"
                            :inputId="'item-bearer-operator'"
                            :options="props.operators"
                            class="w-100"
                            optionLabel="description"
                            optionValue="name"
                            placeholder="Select a Operator"
                        />

                        <div class="d-flex mt-2">
                            <div class="d-flex">
                                <div
                                    v-if="
                                        blacklisted_item.bearer.operator === 'between' &&
                                        Array.isArray(blacklisted_item.bearer.value)
                                    "
                                    class="d-flex"
                                >
                                    <input
                                        id="item-bearer-value-0"
                                        placeholder="Value"
                                        type="number"
                                        class="form-control text-center input-qty me-1"
                                        v-model="blacklisted_item.bearer.value[0]"
                                    />
                                    <p class="mx-2 pt-1">to</p>
                                    <input
                                        id="item-bearer-value-1"
                                        placeholder="Value"
                                        type="number"
                                        class="form-control text-center input-qty me-1"
                                        v-model="blacklisted_item.bearer.value[1]"
                                    />
                                </div>
                                <div v-else>
                                    <input
                                        id="item-bearer-value"
                                        placeholder="Value"
                                        type="number"
                                        class="form-control text-center input-qty me-1"
                                        v-model="blacklisted_item.bearer.value"
                                    />
                                </div>
                                <Select
                                    v-model="blacklisted_item.bearer.unit"
                                    :inputId="'item-bearer-unit'"
                                    :options="props.speed_units"
                                    class="w-100"
                                    optionLabel="value"
                                    optionValue="value"
                                    placeholder="Select a Unit"
                                />
                            </div>
                            <select
                                id="item-bearer-unit"
                                class="form-select"
                                v-model="blacklisted_item.bearer.unit"
                                :style="{ width: 'auto' }"
                            >
                                <option :value="null">Units</option>
                                <option v-for="unit in props.speed_units" :value="unit.value">
                                    {{ unit.value }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Speed:</label>
                        <Select
                            v-model="blacklisted_item.speed.operator"
                            :inputId="'item-speed-operator'"
                            :options="props.operators"
                            class="w-100"
                            optionLabel="description"
                            optionValue="name"
                            placeholder="Select a Operator"
                        />
                        <div class="d-flex mt-2">
                            <div
                                v-if="
                                    blacklisted_item.speed.operator === 'between' &&
                                    Array.isArray(blacklisted_item.speed.value)
                                "
                                class="d-flex"
                            >
                                <input
                                    id="item-speed-value-0"
                                    placeholder="Value"
                                    type="number"
                                    class="form-control text-center input-qty me-1"
                                    v-model="blacklisted_item.speed.value[0]"
                                />
                                <input
                                    id="item-speed-value-1"
                                    placeholder="Value"
                                    type="number"
                                    class="form-control text-center input-qty me-1"
                                    v-model="blacklisted_item.speed.value[1]"
                                />
                            </div>
                            <div v-else>
                                <input
                                    id="item-speed-value"
                                    placeholder="Value"
                                    type="number"
                                    class="form-control text-center input-qty me-1"
                                    v-model="blacklisted_item.speed.value"
                                />
                            </div>
                            <select
                                id="item-speed-unit"
                                class="form-select"
                                v-model="blacklisted_item.speed.unit"
                                :style="{ width: 'auto' }"
                            >
                                <option :value="null">Units</option>
                                <option v-for="unit in props.speed_units" :value="unit.value">
                                    {{ unit.value }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contract Term:</label>
                        <select
                            id="item-contract_terms"
                            class="form-select"
                            v-model="blacklisted_item.contractTermMonths"
                        >
                            <option :value="null">Select a Contract Term</option>
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
