<template>
    <QuoteConnectivityAddonsTable
        v-bind="{
            modelValue: props.modelValue,
            currentStep: props.currentStep
        }"
        route="cloudmarket/products/options"
        title="Options"
    ></QuoteConnectivityAddonsTable>
    <QuoteConnectivityAddonsTable
        v-bind="{
            modelValue: props.modelValue,
            currentStep: props.currentStep
        }"
        route="cloudmarket/products/assignments"
        title="IP Assignments"
        backfillContractTermMonths
    ></QuoteConnectivityAddonsTable>
</template>
<script setup>
const props = defineProps({
    modelValue: Object,
    currentStep: Number,
    needsAddons: Boolean
});
</script>
<style>
.blink {
    transition: all ease-in-out;
    animation: blink-animation 1s linear infinite;
}

@keyframes blink-animation {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.3;
    }
}
</style>
