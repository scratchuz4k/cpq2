<script setup>
import { setQuote } from '@composables/useQuote';
import { onErrorCaptured } from 'vue';

const props = defineProps({
    quote: Object,
    pricePlans: Object,
    tenant: Object,
});

setQuote(props.quote);

onErrorCaptured((err) => {
    window.__vue_errors = window.__vue_errors || [];
    window.__vue_errors.push(err.message + ' ' + err.stack?.substring(0, 300));
    return false;
});
</script>

<template>
    <QuotePreview v-model="props.quote" :pricePlans="pricePlans" :tenant="tenant" />
</template>
