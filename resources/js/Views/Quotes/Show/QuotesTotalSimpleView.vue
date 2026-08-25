<script setup>
import _ from 'lodash';
import { ref } from 'vue';
import { setQuote, quote } from '@composables/useQuote';
import { getMarginThreshHoldWarning } from '@composables/useQuoteItem';
import { usePage } from '@inertiajs/vue3';
import StepActionBar from '@/Components/Steps/StepActionBar.vue';

const page = usePage();
const tenant = page.props.tenant;

const props = defineProps({
    modelValue: Object,
    pricePlans: Array,
    preview: Boolean
});

setQuote(props.modelValue);

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
    <div class="row" v-if="quote.items.some((x) => !x.is_margin_valid)">
        <div class="col">
            <InfoBox class="mb-4 py-0" severity="danger">
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
    </div>

    <div class="row mb-4">
        <div class="col">
            <!-- Product List -->
            <!-- In a real application, this would be dynamically generated based on the selected solution type -->
            <TableCustom v-if="quote.solution_type.configurations?.has_custom_table == 1"></TableCustom>
            <TableSimple v-else :preview="preview"></TableSimple>
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
