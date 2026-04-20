<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { setQuote, quote } from '@composables/useQuote';
import { checkWarnings } from '@composables/warning.js';
import { actionBarButtons } from '@composables/useQuoteSteps';

const props = defineProps({
    quote: Object,
    pricePlans: Object
});

setQuote(props.quote);

const warningProviders = ref([
    { name: 'pxc', is_present: quote.items.some((item) => item.data?.providerName === 'PXC') }
]);

//checkWarnings(warningProviders.value, null);

const items = ref([
    { label: 'Home', url: '/dashboard' },
    {
        label: `Quote #${props.quote?.id}`,
        url: props.quote
            ? ['REGISTRY', 'SUBMITTED'].includes(props.quote.status)
                ? '#'
                : `/quotes/${props.quote.id}/edit`
            : '#'
    },
    {
        label: `Summary`,
        url: props.quote
            ? ['REGISTRY', 'SUBMITTED'].includes(props.quote.status)
                ? '#'
                : `/quotes/${props.quote.id}`
            : '#'
    },
    { label: 'Preview PDF' }
]);
</script>

<template>
    <Head :title="'Preview Quote #' + quote.id" />
    <AuthenticatedLayout :quote="quote" :show-step-action-bar="true">
        <div class="row mb-3">
            <div class="col">
                <Header title="Create a quote" :breadcrumbs="items"></Header>
            </div>
            <div class="col" style="text-align: right">
                <template v-if="quote.status === 'EXPIRED'">
                    <div class="badge text-bg-dark h2 mt-4 mx-2">
                        {{ quote.status }}
                    </div>
                </template>
            </div>
        </div>
        <QuotePreview v-model="props.quote" :pricePlans="pricePlans"></QuotePreview>
        <template #actionBar>
            <div class="left">
                <a
                    id="btn-previous"
                    class="btn btn-outline-dark me-auto"
                    :href="route('quotes.show', { quote: quote.id })"
                >
                    <font-awesome-icon class="me-2" :icon="['fas', 'arrow-left']"></font-awesome-icon>
                    Edit Quote
                </a>
            </div>
            <div class="right">
                <a
                    @click="checkWarnings(warningProviders, route('quotes.download', { quote: quote.uuid }))"
                    class="btn btn-secondary ms-2"
                >
                    <font-awesome-icon :icon="['fas', 'file-pdf']"></font-awesome-icon>
                    Download PDF
                </a>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
<style scoped>
.w-5 {
    width: 5% !important;
}
.w-10 {
    width: 10% !important;
}
.w-20 {
    width: 20% !important;
}
</style>
