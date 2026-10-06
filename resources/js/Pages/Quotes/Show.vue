<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { quote, setQuote } from '@composables/useQuote';
import { can, hasRole } from '@composables/useAuth';
import { getMarginThreshHoldWarning } from '@composables/useQuoteItem';

const page = usePage();
const tenant = page.props.tenant;
const user = page.props.auth.user;
const props = defineProps({
    quote: Object,
    pricePlans: Object
});

setQuote(props.quote);

// Simple view swaps the dealmaker summary for the plain items table
const simpleView = tenant.configurations?.quote_simple_view == '1';

const breadcrumbs = [
    { label: 'Home', url: '/' },
    {
        label: `Quote #${quote.id}`,
        url: ['REGISTRY', 'SUBMITTED'].includes(quote.status) ? '#' : `/quotes/${quote.id}/edit`
    },
    { label: 'Summary' }
];

const showApprovals = computed(
    () =>
        quote.approvals.find((x) => x.status == 'PENDING') &&
        (can('update quote_approval') ||
            hasRole('Super Admin') ||
            quote.approvals.find((x) => x.flow == 'Client review')) &&
        quote.approvals.some((x) => !x.approver_id || x.approver_id === user.id || hasRole('Super Admin'))
);
</script>

<template>
    <Head :title="'Quote #' + quote.id" />
    <AuthenticatedLayout :quote="quote" :show-step-action-bar="true">
        <div class="panel panel-mobile quote-summary">
            <Header title="Quote Overview" :breadcrumbs="breadcrumbs">
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
            <InfoBox v-if="quote.items.some((x) => !x.is_margin_valid)" class="mb-4 py-0" severity="danger">
                <p class="small">{{ getMarginThreshHoldWarning() }}</p>
            </InfoBox>
            <template v-if="simpleView">
                <p class="fs-4 mb-4"><b>Your quote</b></p>
                <TableSimple :preview="false"></TableSimple>
            </template>
            <div v-else class="row g-4">
                <div class="col-xl-9 price-summary">
                    <ChargesTable code="one_off" />
                    <ChargesTable code="recurring" />
                    <ChargesBreakdown />
                    <FinalTotal />
                </div>
                <div class="col-xl-3">
                    <QuoteApprovalsPanel v-if="showApprovals"></QuoteApprovalsPanel>
                    <ProfitabilityPanel />
                </div>
            </div>
        </div>
        <template #actionBar>
            <div class="left">
                <a
                    v-if="!quote.is_blocked"
                    id="btn-previous"
                    class="btn btn-outline-dark me-auto"
                    :href="route('quotes.edit', { quote: quote.id })"
                >
                    <font-awesome-icon class="me-2" :icon="['fas', 'arrow-left']"></font-awesome-icon>
                    Edit Quote
                </a>
            </div>
            <div class="right">
                <ActivityDialog
                    :filters="[
                        { entity: 'Quote', values: quote.id },
                        {
                            entity: 'QuoteItem',
                            values:
                                quote.items.map((x) => x.id).join(',') +
                                ',' +
                                quote.items_trashed.map((x) => x.id).join(',')
                        },
                        {
                            entity: 'QuoteDiscount',
                            values: quote.discounts.map((x) => x.id).join(',')
                        }
                    ]"
                ></ActivityDialog>
                <ShareableQuote :disabled="quote.items.some((x) => !x.is_margin_valid)"></ShareableQuote>
                <a
                    class="btn btn-outline-primary ms-2"
                    :href="route('quotes.preview', { quote: quote.id })"
                    v-if="!quote.items.some((x) => !x.is_margin_valid)"
                >
                    <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                    Preview
                </a>
                <button class="btn btn-outline-primary ms-2" v-else disabled="true">
                    <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                    Preview
                </button>
                <button
                    hidden
                    class="btn btn-outline-primary ms-2"
                    :disabled="quote.items.some((x) => !x.is_margin_valid)"
                >
                    <font-awesome-icon class="me-2" :icon="['fas', 'envelope']"> </font-awesome-icon>
                    Email this quote
                </button>

                <QuoteApprovalsModal
                    v-if="can('viewAny quote_approval') || hasRole('Super Admin')"
                ></QuoteApprovalsModal>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
