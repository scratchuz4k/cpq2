<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { setQuote, quote } from '@composables/useQuote';
import { actionBarButtons } from '@composables/useQuoteSteps';

const props = defineProps({
    quote: Object,
    pricePlans: Object
});

setQuote(props.quote);

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
                <Header title="Quote Overview" :breadcrumbs="items">
                    <template #badge>
                        <span
                            :id="'quote-' + quote.id + '-status'"
                            class="badge badge-quote-status rounded-pill ms-2"
                            :class="`${quote.status.toLowerCase()}`"
                        >
                            {{ quote.status }}
                        </span>
                    </template>
                </Header>
            </div>
        </div>
        <div class="panel d-flex justify-content-center">
            <div class="quote-preview-page">
                <QuotePreview v-model="props.quote" :pricePlans="pricePlans"></QuotePreview>
            </div>
        </div>
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
                <a :href="route('quotes.download', { quote: quote.uuid })" class="btn btn-secondary ms-2">
                    <font-awesome-icon :icon="['fas', 'file-pdf']"></font-awesome-icon>
                    Download PDF
                </a>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
