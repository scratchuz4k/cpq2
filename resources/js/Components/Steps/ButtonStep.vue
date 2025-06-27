<script setup>
import { computed } from 'vue';
import { currentStep, steps } from '@composables/useQuoteSteps';

const props = defineProps({
    modelValue: Object,
    disabled: Boolean
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

const nextStep = () => {
    currentStep.value++;
};

const previousStep = () => {
    currentStep.value--;
};
</script>

<template>
    <div class="d-flex justify-content-between flex-end mt-3">
        <button id="btn-next-step" class="btn" type="button" v-if="currentStep != 0" @click="previousStep()">
            <font-awesome-icon :icon="['fas', 'arrow-left']" class="ms-2"></font-awesome-icon>
            Previous Step
        </button>
        <button
            id="btn-create-quote"
            class="btn btn-primary"
            type="submit"
            v-if="currentStep + 1 == steps.length"
            :disabled="props.disabled"
        >
            Get Prices
            <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
        </button>
        <button
            id="btn-next-step"
            class="btn btn-primary"
            type="button"
            v-else
            @click="nextStep()"
            :disabled="props.disabled"
        >
            Next Step
            <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
        </button>
    </div>
</template>
