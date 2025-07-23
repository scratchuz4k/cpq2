<script setup>
import InputSelect from '@/Components/InputSelect.vue';
import { usePage } from '@inertiajs/vue3';
import Select from 'primevue/select';
import { watch, computed } from 'vue';

const page = usePage();

const props = defineProps({
    operations: Array,
    rules: Object,
    price_plans: Array,
    modelValue: Object
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const check = (value) => {
    return !!value;
};

const icons = {
    Product: ['far', 'rectangle-list'],
    Account: ['far', 'address-card'],
    Supplier: ['far', 'handshake'],
    Global: ['fas', 'landmark'],
    Group: ['fas', 'users'],
    null: ['fas', 'fa-slash']
};
</script>
<template>
    <div class="col-6">
        <div class="panel">
            <h5>Choose the first Level</h5>
            <ul class="d-flex flex-wrap p-0 m-0 mb-3" style="list-style: none">
                <li v-for="(rule, index) in rules" class="me-1">
                    <RadioCard
                        v-model="proxyValue.entity_1"
                        :value="index"
                        name="spa-entity_1"
                        @click="
                            () => {
                                proxyValue.entity_2 = null;
                                proxyValue.entity_2_value = null;
                            }
                        "
                    >
                        <div class="text-center">
                            <font-awesome-icon :icon="icons[index]" size="2x"></font-awesome-icon>
                            <p>{{ index }}</p>
                        </div>
                    </RadioCard>
                </li>
            </ul>
            <div class="mb-3" v-if="proxyValue.entity_1 == 'Account'">
                <label class="form-label">Select the Account</label>
                <InputSelect
                    id="Account1"
                    url="/accounts"
                    :fields="['name', 'reference']"
                    v-model="proxyValue.entity_1_value"
                    :is-multiple="false"
                    placeholder="Select an Account"
                >
                </InputSelect>
            </div>
            <div class="mb-3" v-if="proxyValue.entity_1 == 'Supplier'">
                <label class="form-label">Select the Supplier</label>
                <InputSelect
                    id="Supplier1"
                    url="/suppliers"
                    :fields="['name']"
                    v-model="proxyValue.entity_1_value"
                    :is-multiple="false"
                    placeholder="Select a Supplier"
                >
                </InputSelect>
            </div>
            <div class="mb-3" v-if="proxyValue.entity_1 == 'Product'">
                <label class="form-label">Select the Product</label>
                <InputSelect
                    id="Product1"
                    url="/products"
                    :fields="['name']"
                    v-model="proxyValue.entity_1_value"
                    :is-multiple="false"
                    placeholder="Select a Product"
                >
                </InputSelect>
            </div>
            <div class="mb-3" v-if="proxyValue.entity_1 == 'Group'">
                <label class="form-label">Select a Group</label>
                <InputSelect
                    id="Group1"
                    url="/groups"
                    :fields="['name']"
                    v-model="proxyValue.entity_1_value"
                    :is-multiple="false"
                    placeholder="Select a Group"
                >
                </InputSelect>
            </div>
            <h5 class="mt-5">Choose the second Level (if needed)</h5>
            <ul class="d-flex flex-wrap p-0 m-0 mb-3" style="list-style: none">
                <li v-for="rule in rules[proxyValue.entity_1]" class="me-1">
                    <RadioCard v-model="proxyValue.entity_2" :value="rule ?? 'None'" name="spa-entity_2">
                        <div class="text-center">
                            <font-awesome-icon :icon="icons[rule]" size="2x"></font-awesome-icon>
                            <p>{{ rule ?? 'None' }}</p>
                        </div>
                    </RadioCard>
                    <!-- <div
                        :class="{ active: proxyValue.entity_2 === rule }"
                        class="rule d-flex flex-column p-2"
                        @click="
                            () => {
                                proxyValue.entity_2 = rule;
                                proxyValue.entity_2_value = null;
                            }
                        "
                    >
                        <font-awesome-icon class="p-2" :icon="icons[rule]" size="2x"></font-awesome-icon>
                        <p>{{ rule ?? 'None' }}</p>
                    </div> -->
                </li>
            </ul>
            <template v-if="proxyValue.entity_2">
                <div class="mb-3" v-if="proxyValue.entity_2 == 'Account'">
                    <label class="form-label">Select the Account</label>
                    <InputSelect
                        id="Account2"
                        url="/accounts"
                        :fields="['name', 'reference']"
                        v-model="proxyValue.entity_2_value"
                        :is-multiple="false"
                        placeholder="Select an Account"
                    >
                    </InputSelect>
                </div>
                <div class="mb-3" v-if="proxyValue.entity_2 == 'Supplier'">
                    <label class="form-label">Select the Supplier</label>
                    <InputSelect
                        id="Supplier2"
                        url="/suppliers"
                        :fields="['name']"
                        v-model="proxyValue.entity_2_value"
                        :is-multiple="false"
                        placeholder="Select a Supplier"
                    >
                    </InputSelect>
                </div>
                <div class="mb-3" v-if="proxyValue.entity_2 == 'Product'">
                    <label class="form-label">Select the Product</label>
                    <InputSelect
                        id="Product2"
                        url="/products"
                        :fields="['name']"
                        v-model="proxyValue.entity_2_value"
                        :is-multiple="false"
                        placeholder="Select a Product"
                    >
                    </InputSelect>
                </div>
            </template>
            <h5 class="mt-5">Apply the Operation</h5>
            <div class="mb-3">
                <label class="form-label">Operation:</label>
                <Select
                    v-model="proxyValue.operation_id"
                    :inputId="'spa-operation'"
                    :options="props.operations"
                    class="w-100"
                    optionLabel="code"
                    optionValue="id"
                    placeholder="Select an Operation"
                />
            </div>
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Percentage:</label>
                    <input
                        id="spa-percentage"
                        type="number"
                        class="form-control"
                        v-model="proxyValue.percentage"
                        :disabled="check(proxyValue.value)"
                        min="0"
                        max="100"
                    />
                </div>
                <div class="col">
                    <label class="form-label">Value:</label>
                    <input
                        id="spa-value"
                        type="number"
                        class="form-control"
                        v-model="proxyValue.value"
                        :disabled="check(proxyValue.percentage)"
                    />
                </div>
            </div>
            <hr class="my-4" />
            <div class="mb-3">
                <label class="form-label">Group: </label>
                <InputSelect
                    id="spa-roup"
                    url="/groups"
                    :fields="['name']"
                    v-model="proxyValue.group"
                    placeholder="Select a Group"
                >
                </InputSelect>
            </div>
            <div class="mb-3">
                <label class="form-label">Price Plan:</label>
                <Select
                    v-model="proxyValue.price_plan_id"
                    :inputId="'spa-price_plan'"
                    :options="props.price_plans"
                    class="w-100"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Select a Price Plan"
                />
            </div>
        </div>
    </div>
</template>
<style>
.rule {
    cursor: pointer;
    margin-right: 5px;
    border: 1px solid;
    border-radius: 20px;
    text-align: center;
}

.rule:hover {
    border: 1px solid var(--primary-light);
    color: var(--primary-light);
    background: whitesmoke;
}

.rule.active {
    border: 1px solid var(--primary-light);
    color: var(--primary-light);
}
</style>
