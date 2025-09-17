<template>
    <QuoteConnectivityOptionsTable
        v-bind="{
            modelValue: props.modelValue,
            currentStep: props.currentStep
        }"
    ></QuoteConnectivityOptionsTable>
    <QuoteConnectivityAssignmentsTable
        v-bind="{
            modelValue: props.modelValue,
            currentStep: props.currentStep
        }"
    ></QuoteConnectivityAssignmentsTable>
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
