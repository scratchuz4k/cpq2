<script setup>
import { computed } from 'vue';
import { currentStep, steps, isDisabled } from '@composables/useQuoteSteps';

const props = defineProps({
    modelValue: Object,
    btnName: String
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
    <div class="d-flex justify-content-between flex-row-reverse mt-3">
        <div>
            <slot></slot>
            <button
                id="btn-create-quote"
                class="btn btn-primary"
                type="submit"
                v-if="currentStep + 1 == steps.length"
                :disabled="isDisabled"
            >
                {{ btnName ?? 'Get Prices' }}
                <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
            </button>
            <button
                id="btn-next-step"
                class="btn btn-primary"
                type="button"
                v-else
                @click="nextStep()"
                :disabled="isDisabled"
            >
                Next Step
                <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
            </button>
        </div>
        <button id="btn-previous-step" class="btn" type="button" v-if="currentStep != 0" @click="previousStep()">
            <font-awesome-icon :icon="['fas', 'arrow-left']" class="ms-2"></font-awesome-icon>
            Previous Step
        </button>
    </div>
</template>
