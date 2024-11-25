<script setup>
import InputSearch from "@/Components/InputSelect.vue";

const props = defineProps({
    operations: Array,
    rules: Object,
    modelValue: Object
})
const check = (value) => {
    return !!value
}

const icons = {
    'Product': ['far', 'rectangle-list'],
    'Account': ['far', 'address-card'],
    'Supplier': ['far', 'handshake'],
    null: ['fas', 'fa-slash']
}
</script>
<template>
    <div class="col-6">
        <div class="panel">
            <h5>Choose the first Entity</h5>
            <ul class="d-flex flex-wrap p-0 m-0 mb-3" style="list-style: none;">
                <li v-for="(rule, index) in rules">
                    <div :id="'spa-entity-' + index" :class="{ 'active': modelValue.entity_1 === index }"
                        class="rule d-flex flex-column p-2"
                        @click="() => { modelValue.entity_1 = index; modelValue.entity_1_value = null; modelValue.entity_2 = null; modelValue.entity_2_value = null }">
                        <font-awesome-icon class="p-2 " :icon="icons[index]" size="2x"></font-awesome-icon>
                        <p>{{ index }}</p>
                    </div>
                </li>
            </ul>
            <div class="mb-3" v-if="modelValue.entity_1 == 'Account'">
                <label class="form-label">Select the Account</label>
                <InputSearch id="Account1" url="/accounts" field="name" v-model="modelValue.entity_1_value"
                    :is-multiple="false">
                </InputSearch>
            </div>
            <div class="mb-3" v-if="modelValue.entity_1 == 'Supplier'">
                <label class="form-label">Select the Supplier</label>
                <InputSearch id="Supplier1" url="/suppliers" field="name" v-model="modelValue.entity_1_value"
                    :is-multiple="false">
                </InputSearch>
            </div>
            <div class="mb-3" v-if="modelValue.entity_1 == 'Product'">
                <label class="form-label">Select the Product</label>
                <InputSearch id="Product1" url="/products" field="name" v-model="modelValue.entity_1_value"
                    :is-multiple="false">
                </InputSearch>
            </div>
            <h5 class="mt-5">Choose the second Entity (if needed)</h5>
            <ul class="d-flex flex-wrap p-0 mb-3" style="list-style: none;">
                <li v-for="rule in rules[modelValue.entity_1]">
                    <div :class="{ 'active': modelValue.entity_2 === rule }" class="rule d-flex flex-column p-2"
                        @click="() => { modelValue.entity_2 = rule; modelValue.entity_2_value = null; }">
                        <font-awesome-icon class="p-2 " :icon="icons[rule]" size="2x"></font-awesome-icon>
                        <p>{{ rule ?? 'None' }}</p>
                    </div>
                </li>
            </ul>
            <template v-if="modelValue.entity_2">
                <div class="mb-3" v-if="modelValue.entity_2 == 'Account'">
                    <label class="form-label">Select the Account</label>
                    <InputSearch id="Account2" url="/accounts" field="name" v-model="modelValue.entity_2_value"
                        :is-multiple="false">
                    </InputSearch>
                </div>
                <div class="mb-3" v-if="modelValue.entity_2 == 'Supplier'">
                    <label class="form-label">Select the Supplier</label>
                    <InputSearch id="Supplier2" url="/suppliers" field="name" v-model="modelValue.entity_2_value"
                        :is-multiple="false">
                    </InputSearch>
                </div>
                <div class="mb-3" v-if="modelValue.entity_2 == 'Product'">
                    <label class="form-label">Select the Product</label>
                    <InputSearch id="Product2" url="/products" field="name" v-model="modelValue.entity_2_value"
                        :is-multiple="false">
                    </InputSearch>
                </div>
            </template>
            <h5 class="mt-5">Apply the Operation</h5>
            <div class="mb-3">
                <label class="form-label">Operation:</label>
                <select class="form-select" v-model="modelValue.operation_id" required>
                    <option v-for="operation in props.operations" :value="operation.id">{{ operation.code }}
                    </option>
                </select>
            </div>
            <div class="row g-3">
                <div class="col">
                    <label class="form-label">Percentage:</label>
                    <input id="spa-percentage" type="number" class="form-control" v-model="modelValue.percentage"
                        :disabled="check(modelValue.value)" min="0" max="100">
                </div>
                <div class="col">
                    <label class="form-label">Value:</label>
                    <input id="spa-value" type="number" class="form-control " v-model="modelValue.value"
                        :disabled="check(modelValue.percentage)">
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
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
    color: var(--primary-light)
}
</style>
