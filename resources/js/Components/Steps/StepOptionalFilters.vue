<script setup>
import { computed } from 'vue';
import { MultiSelect } from 'primevue';

const props = defineProps({
    solutionType: Array,
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
</script>

<template>
    <div class="row mb-3">
        <div class="col-lg-6">
            <div class="panel">
                <h5 class="font-bold mb-3">Optionally, select some default filters</h5>
                <div class="mb-5 pb-2">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="form-label">Speed</p>
                        <span>
                            <span class="badge text-bg-secondary">Min</span>
                            {{ format.speed(proxyValue.configs.speed[0]) }}
                            <span class="ms-2 badge text-bg-secondary">Max</span>
                            {{ format.speed(proxyValue.configs.speed[1]) }}
                        </span>
                    </div>
                    <div class="d-flex align-items-end">
                        <div class="w-20">
                            <input
                                type="number"
                                min="0"
                                max="10000"
                                class="form-control w-100"
                                @change="(ev) => formatInput(ev, proxyValue.configs.speed, 0)"
                                v-model="proxyValue.configs.speed[0]"
                            />
                        </div>
                        <div class="px-4 mb-3" style="width: 80%">
                            <SpeedRange
                                v-model="proxyValue.configs.speed"
                                :options="[0, 10, 100, 1000, 10000]"
                            ></SpeedRange>
                        </div>
                        <div class="w-20">
                            <input
                                type="number"
                                :min="0"
                                :max="10000"
                                @change="(ev) => formatInput(ev, proxyValue.configs.speed, 1)"
                                class="form-control w-100"
                                v-model="proxyValue.configs.speed[1]"
                            />
                        </div>
                    </div>
                </div>
                <hr class="mb-4" />
                <div class="mb-5 pb-2">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="form-label">Bearer</p>
                        <span>
                            <span class="badge text-bg-secondary">Min</span>
                            {{ format.speed(proxyValue.configs.bearer[0]) }}
                            <span class="ms-2 badge text-bg-secondary">Max</span>
                            {{ format.speed(proxyValue.configs.bearer[1]) }}
                        </span>
                    </div>
                    <div class="d-flex align-items-end">
                        <div class="w-20">
                            <input
                                type="number"
                                min="0"
                                max="10000"
                                class="form-control w-100"
                                @change="(ev) => formatInput(ev, proxyValue.configs.bearer, 0)"
                                v-model="proxyValue.configs.bearer[0]"
                            />
                        </div>
                        <div class="px-4 mb-3" style="width: 80%">
                            <BearerRange
                                v-model="proxyValue.configs.bearer"
                                :options="[0, 10, 100, 1000, 10000]"
                            ></BearerRange>
                        </div>
                        <div class="w-20">
                            <input
                                type="number"
                                :min="0"
                                :max="10000"
                                @change="(ev) => formatInput(ev, proxyValue.configs.bearer, 1)"
                                class="form-control w-100"
                                v-model="proxyValue.configs.bearer[1]"
                            />
                        </div>
                    </div>
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
                <div class="mb-3">
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
            <ButtonStep v-model="proxyValue" :disabled="proxyValue.account == null"></ButtonStep>
        </div>
    </div>
</template>
