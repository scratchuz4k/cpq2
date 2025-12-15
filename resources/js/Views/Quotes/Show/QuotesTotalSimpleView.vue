<script setup>
import _ from 'lodash';
import { ref } from 'vue';
import { setQuote, quote } from '@composables/useQuote';
import { getMarginThreshHoldWarning } from '@composables/useQuoteItem';
import { usePage } from '@inertiajs/vue3';
import { checkWarnings } from '@composables/warning.js';
import WarningNote from '@/Components/WarningNote.vue';

const page = usePage();
const tenant = page.props.tenant;

const props = defineProps({
    modelValue: Object,
    pricePlans: Array,
    preview: Boolean
});

setQuote(props.modelValue);

let temp = quote.items;
const warningProviders = ref([
    { name: 'its', is_present: temp.some((item) => item.data?.providerName === 'ITS') },
    { name: 'pxc', is_present: temp.some((item) => item.data?.providerName === 'PXC') }
]);

checkWarnings(warningProviders.value, null);

const items = ref([
    { label: 'Home', url: '/dashboard' },
    {
        label: `Quote #${quote?.id}`,
        url: quote ? (['REGISTRY', 'SUBMITTED'].includes(quote.status) ? '#' : `/quotes/${quote.id}/edit`) : '#'
    },
    { label: 'Summary' }
]);
</script>
<template>
    <Header title="Create a quote" :breadcrumbs="items"></Header>
    <InfoConnectivity v-if="!preview" :quote="quote"></InfoConnectivity>
    <div class="row" v-if="warningProviders.find((x) => x.is_present) || quote.items.some((x) => !x.is_margin_valid)">
        <div class="col">
            <InfoBox class="mb-4 py-0" severity="danger">
                <WarningNote :warnings="warningProviders" />
                <p v-if="quote.items.some((x) => !x.is_margin_valid)" class="small">
                    {{ getMarginThreshHoldWarning() }}
                </p>
            </InfoBox>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <p class="fs-4 me-3 mb-0"><b>Your quote</b></p>
        </div>
        <div class="d-flex justify-content-between align-items-center">
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
                class="btn btn-outline-primary me-2"
                :href="route('quotes.preview', { quote: quote.id })"
                v-if="!quote.items.some((x) => !x.is_margin_valid)"
            >
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview
            </a>
            <button class="btn btn-outline-primary me-2" v-else disabled="true">
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview
            </button>
            <a :href="route('dashboard')" class="btn btn-primary" type="submit"> Save and close </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <!-- Product List -->
            <!-- In a real application, this would be dynamically generated based on the selected solution type -->
            <TableCustom v-if="quote.solution_type.configurations?.has_custom_table == 1"></TableCustom>
            <TableSimple v-else :preview="preview"></TableSimple>

            <div class="row justify-content-end">
                <div class="col-6">
                    <TableTotal :quote="quote"></TableTotal>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="d-flex justify-content-end align-items-center">
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
                class="btn btn-outline-primary me-2"
                :href="route('quotes.preview', { quote: quote.id })"
                v-if="!quote.items.some((x) => !x.is_margin_valid)"
            >
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview
            </a>
            <button class="btn btn-outline-primary me-2" v-else disabled="true">
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview
            </button>
            <a :href="route('dashboard')" class="btn btn-primary" type="submit"> Save and close </a>
        </div>
    </div>
</template>

<style>
.delete-btn {
    all: unset;
    height: 25px;
    color: var(--purple);

    &:hover {
        color: var(--red) !important;
    }
}

.cpq-qty {
    button {
        border: 2px solid var(--grey);
        border-radius: 8px;

        &:hover {
            background-color: var(--purple);
            border-color: var(--purple);
            color: white;
        }

        &.decrease {
            border-top-right-radius: 0px;
            border-bottom-right-radius: 0px;
        }

        &.increase {
            border-top-left-radius: 0px;
            border-bottom-left-radius: 0px;
        }
    }

    .cpq-qty-input {
        border-width: 2px !important;
        border-color: var(--grey);
    }
}

input[type='number']::-webkit-outer-spin-button,
input[type='number']::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Remove arrows in Firefox */
input[type='number'] {
    -moz-appearance: textfield;
    appearance: textfield;
}
</style>
