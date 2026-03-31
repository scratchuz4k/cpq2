<script setup>
import { computed } from 'vue';
import { MultiSelect } from 'primevue';
import format from '@/Composables/format';
import Select from 'primevue/select';

const props = defineProps({
    showButton: { type: Boolean, default: true },
    modelValue: Object
});

const emit = defineEmits(['update:modelValue', 'nextStep']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

if (Array.isArray(proxyValue.value.configs.speed)) {
    proxyValue.value.configs.speed = Number(proxyValue.value.configs.speed[0]);
}
if (Array.isArray(proxyValue.value.configs.bearer)) {
    proxyValue.value.configs.bearer = Number(proxyValue.value.configs.bearer[0]);
}

const bearers = [
    { value: 100, label: format.speed(100) },
    { value: 1000, label: format.speed(1000) },
    { value: 10000, label: format.speed(10000) },
    { value: 25000, label: format.speed(25000) },
    { value: 100000, label: format.speed(100000) }
];
const speeds = [
    { value: 10, label: format.speed(10) },
    { value: 50, label: format.speed(50) },
    { value: 100, label: format.speed(100) },
    { value: 200, label: format.speed(200) },
    { value: 500, label: format.speed(500) },
    { value: 1000, label: format.speed(1000) },
    { value: 2000, label: format.speed(2000) },
    { value: 3000, label: format.speed(3000) },
    { value: 4000, label: format.speed(4000) },
    { value: 5000, label: format.speed(5000) },
    { value: 6000, label: format.speed(6000) },
    { value: 7000, label: format.speed(7000) },
    { value: 8000, label: format.speed(8000) },
    { value: 9000, label: format.speed(9000) },
    { value: 10000, label: format.speed(10000) },
    { value: 25000, label: format.speed(25000) },
    { value: 100000, label: format.speed(100000) }
];

const formatInput = (ev, field, index) => {
    if (ev.target.value > 10000) {
        field[index] = 10000;
        return;
    }

    if (field == props.modelValue.configs.speed && ev.target.value < 10) {
        field[index] = 10;
        return;
    }

    if (field == props.modelValue.configs.bearer && ev.target.value < 100) {
        field[index] = 100;
        return;
    }

    field[index] = format.simpleSpeed(ev.target.value);

    if (Array.isArray(field) && field.length === 2) {
        if (field[0] > field[1]) {
            if (index === 0) {
                field[0] = field[1];
            } else {
                field[1] = field[0];
            }
        }
    }
};
</script>

<template>
    <div class="row mb-3">
        <div class="col-lg-6">
            <div class="panel">
                <h5 class="font-bold mb-3">Optionally, select some default filters</h5>
                <div class="mb-4 pb-2">
                    <div class="d-flex justify-content-between">
                        <p class="form-label">Min. Speed</p>
                        <span>
                            <span class="badge text-bg-secondary">Min</span>
                            {{ proxyValue.configs.speed ? format.speed(proxyValue.configs.speed) : 'All' }}
                            <!-- <span class="ms-2 badge text-bg-secondary">Max</span>
                            {{ format.speed(proxyValue.configs.speed[1]) }} -->
                        </span>
                    </div>
                    <Select
                        v-model="proxyValue.configs.speed"
                        style="width: 100%"
                        :options="speeds"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="All"
                        class="custom-select"
                    >
                    </Select>
                </div>
                <div class="mb-4 pb-2">
                    <div class="d-flex justify-content-between">
                        <p class="form-label">Min. Bearer</p>
                        <span>
                            <span class="badge text-bg-secondary">Min</span>
                            {{ proxyValue.configs.bearer ? format.speed(proxyValue.configs.bearer) : 'All' }}
                            <!-- <span class="ms-2 badge text-bg-secondary">Max</span>
                            {{ format.speed(proxyValue.configs.bearer[1]) }} -->
                        </span>
                    </div>
                    <Select
                        v-model="proxyValue.configs.bearer"
                        style="width: 100%"
                        :options="bearers"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="All"
                        class="me-3 custom-select"
                    >
                    </Select>
                </div>
                <div class="mb-4 pb-2">
                    <div class="d-flex justify-content-between">
                        <p class="form-label">Contract Length</p>
                    </div>
                    <MultiSelect
                        v-model="proxyValue.configs.contract_length"
                        style="width: 100%"
                        :options="['12', '24', '36', '48', '60']"
                        placeholder="All Terms"
                        class="me-3 custom-multiselect"
                    >
                        <template #option="slotProps">
                            <div style="font-size: 14px; line-height: 1.5rem">{{ slotProps.option }} Months</div>
                        </template>
                    </MultiSelect>
                </div>
                <div class="mb-3" v-show="false">
                    <p class="form-label mb-2">Managed Products:</p>
                    <div class="row row-cols-1 row-cols-md-3 g-3">
                        <div class="col">
                            <RadioCard v-model="proxyValue.configs.managed" :value="'0'" name="managed-products">
                                <div class="text-center">
                                    <font-awesome-icon
                                        class="py-3 opacity-25"
                                        :icon="['fa', 'layer-group']"
                                        size="2x"
                                    ></font-awesome-icon>
                                    <span class="d-block h6">Show All</span>
                                </div>
                            </RadioCard>
                        </div>
                        <div class="col">
                            <RadioCard v-model="proxyValue.configs.managed" :value="'1'" name="managed-products">
                                <div class="text-center">
                                    <font-awesome-icon
                                        class="py-3 opacity-25"
                                        :icon="['fa', 'lock']"
                                        size="2x"
                                    ></font-awesome-icon>
                                    <span class="d-block h6">Show Managed Only</span>
                                </div>
                            </RadioCard>
                        </div>
                        <div class="col">
                            <RadioCard v-model="proxyValue.configs.managed" :value="'2'" name="managed-products">
                                <div class="text-center">
                                    <font-awesome-icon
                                        class="py-3 opacity-25"
                                        :icon="['fa', 'lock-open']"
                                        size="2x"
                                    ></font-awesome-icon>
                                    <span class="d-block h6">Show Unmanaged Only</span>
                                </div>
                            </RadioCard>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
