<script setup>
import { ref } from 'vue';
import { setQuote, quote } from '@composables/useQuote';
import { getMarginThreshHoldWarning } from '@composables/useQuoteItem';
import { usePage } from '@inertiajs/vue3';
import WarningNote from '@/Components/WarningNote.vue';
import { checkWarnings } from '@composables/warning.js';
import format from '@/Composables/format.js';
import { can, hasRole } from '@composables/useAuth';

const props = defineProps({
    modelValue: Object,
    pricePlans: Array,
    preview: Boolean
});

const page = usePage();
const tenant = page.props.tenant;
const user = page.props.auth.user;

setQuote(props.modelValue);

const calc_layout = JSON.parse(tenant.configurations?.calculator_layout ?? '[]');

const warningProviders = ref([
    { name: 'pxc', is_present: quote.items.some((item) => item.data?.providerName === 'PXC') }
]);

checkWarnings(warningProviders.value, null);

const items = ref([
    { label: 'Home', url: '/' },
    {
        label: `Quote #${quote?.id}`,
        url: quote ? (['REGISTRY', 'SUBMITTED'].includes(quote.status) ? '#' : `/quotes/${quote.id}/edit`) : '#'
    },
    { label: 'Summary' }
]);
</script>
<template>
    <div class="panel panel-mobile">
        <div class="row justify-content-between">
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
                <ContactInfo />
                <div
                    class="row"
                    v-if="warningProviders.find((x) => x.is_present) || quote.items.some((x) => !x.is_margin_valid)"
                >
                    <div class="col">
                        <InfoBox class="mb-4 py-0" severity="danger">
                            <WarningNote :warnings="warningProviders" />
                            <p v-if="quote.items.some((x) => !x.is_margin_valid)" class="small">
                                {{ getMarginThreshHoldWarning() }}
                            </p>
                        </InfoBox>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 mb-5 price-summary">
                    <TableCustom v-if="quote.solution_type.configurations?.has_custom_table == 1"></TableCustom>
                    <TableDealMaker v-else :pricePlans="pricePlans" :preview="preview"></TableDealMaker>
                </div>
                <div class="col-xl-4 ps-xl-5">
                    <QuoteApprovalsPanel
                        v-if="
                            quote.approvals.find((x) => x.status == 'PENDING') &&
                            (can('update quote_approval') ||
                                hasRole('Super Admin') ||
                                quote.approvals.find((x) => x.flow == 'Client review')) &&
                            quote.approvals.some(
                                (x) => !x.approver_id || x.approver_id === user.id || hasRole('Super Admin')
                            )
                        "
                    ></QuoteApprovalsPanel>
                    <div class="price-mobile-sidebar commission-calculator px-4 pt-4 pb-2 mb-3">
                        <h3 class="mb-3">Profitability Calculator</h3>
                        <div class="px-3 py-2 mb-3 rounded" style="background: var(--lighter-grey)">
                            <div v-if="!calc_layout.length || calc_layout.includes('MARGIN')" class="py-1">
                                <div class="margin d-flex text-muted justify-content-between">
                                    <p class="small label">
                                        <span class="fw-semibold">Margin</span>
                                        <span id="margin-percentage">
                                            ({{ format.decimalPlaces(quote.margin) }}%)
                                        </span>
                                    </p>
                                    <p id="margin-value" class="small value fw-bold text-right">
                                        {{ format.GBP(quote.total * (quote.margin / 100)) }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="!calc_layout.length || calc_layout.includes('COMMISSION')" class="py-1">
                                <div class="comission d-flex text-muted justify-content-between">
                                    <p class="small label">
                                        <span class="fw-semibold">Commission</span>
                                        <span id="commission-percentage">
                                            ({{ format.decimalPlaces(quote.commission_rate) }}%)
                                        </span>
                                    </p>
                                    <p id="commission-value" class="small value fw-bold text-right">
                                        {{ format.GBP(quote.commission_value) }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="!calc_layout.length || calc_layout.includes('TOTAL')" class="py-1">
                                <div class="total d-flex text-muted justify-content-between">
                                    <p class="small total label fw-semibold">Contract total</p>
                                    <p id="total-value" class="small value fw-bold text-right">
                                        {{ format.GBP(quote.total) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style>
/* For Chrome, Safari, Edge, Opera */
input[type='number']::-webkit-outer-spin-button,
input[type='number']::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* For Firefox */
input[type='number'] {
    -moz-appearance: textfield;
    appearance: textfield;
}

[role='button']:hover {
    opacity: 0.75;
}

.reject-reasons-table tr th,
.reject-reasons-table tr td {
    padding: 1rem !important;
}

.commission-calculator .input-group input {
    border-right: none;
    background-color: #dfe3e6;
    border-color: #bdbdbd;
}

.commission-calculator .input-group .input-group-text {
    background-color: #dfe3e6;
    color: #646464;
    border-color: #bdbdbd;
}

.custom-pre {
    font-family: inherit;
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 0;
}
</style>
