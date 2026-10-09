<script setup>
import { Head } from '@inertiajs/vue3';

// Page printed to PDF by headless Chrome (QuoteService::generatePdf), no layout, only the quote document
const props = defineProps({
    quote: Object,
    pricePlans: Object
});
</script>

<template>
    <Head :title="'Quote #' + quote.id" />
    <QuotePreview v-model="props.quote" :pricePlans="pricePlans"></QuotePreview>
</template>

<style>
body {
    background: #fff !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

#sentry-feedback {
    display: none !important;
}
</style>
