<script setup>
import { computed } from 'vue';
import { setQuote, quote } from '@composables/useQuote';
import format from '@/Composables/format.js';
import { usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';

// Customer facing quote document, used by the preview page, shared links and the PDF (Quotes/Pdf)
const page = usePage();
const tenant = page.props.tenant;

const props = defineProps({
    modelValue: Object,
    pricePlans: Object
});

setQuote(props.modelValue);

const contact = computed(() => quote.account?.main_contact ?? {});
const hasItems = (code) => quote.items.some((x) => x.price_plan?.code == code);
</script>
<template>
    <div id="quote-preview" class="quote-preview">
        <div class="quote-preview-header">
            <div id="header_left">
                <AppLogo type="logo_docs" class="preview-logo"></AppLogo>
            </div>
            <div id="quote-preview-account" class="text-end">
                <p>{{ quote.account?.name }}</p>
                <p v-if="contact.phone">Tel: {{ contact.phone }}</p>
                <p v-if="contact.email">Email: {{ contact.email }}</p>
            </div>
        </div>
        <div class="quote-preview-details">
            <h1 class="quote-preview-number">Quote #{{ quote.id }}</h1>
            <p id="quote-preview-date">Quotation date: {{ dayjs(quote.created_at).format('DD/MM/YYYY') }}</p>
            <p id="quote-preview-expiry-date">
                Valid date: {{ format.expireDate(quote.created_at, tenant.configurations?.quote_expiration_days) }}
            </p>
        </div>
        <ChargesTable v-if="hasItems('SINGLE')" code="one_off" :preview="true" />
        <ChargesTable v-if="hasItems('MONTHLY')" code="recurring" :preview="true" />
        <ChargesBreakdown v-if="quote.items.length" :preview="true" />
        <FinalTotal :preview="true" />
    </div>
</template>
