<script setup>
import { computed } from 'vue';
import { quote } from '@composables/useQuote';

const props = defineProps({
    // 'one_off' | 'recurring'
    code: String,
    // read-only customer view (preview / shared link / PDF): no margin, no actions
    preview: Boolean
});

const pricePlanCodes = { one_off: 'SINGLE', recurring: 'MONTHLY' };
const titles = { one_off: 'One-off charges', recurring: 'Recurring charges' };

const isRecurring = computed(() => props.code == 'recurring');
const items = computed(() => quote.items.filter((x) => x.price_plan?.code == pricePlanCodes[props.code]));
</script>
<template>
    <div class="quote-summary-panel">
        <h3 class="quote-summary-title">{{ titles[code] }}</h3>
        <div class="table-responsive-lg">
            <table :id="`table-${code}`" class="quote-summary-table table">
                <thead>
                    <tr>
                        <th class="column-name-header">{{ preview ? 'Product name' : 'Product' }}</th>
                        <th class="column-discount-header">Discount</th>
                        <th v-if="!preview" class="column-margin-header">Margin</th>
                        <th class="column-price-header">Unit price</th>
                        <th class="column-quantity-header">Qty</th>
                        <template v-if="isRecurring">
                            <th class="column-billing_frequency-header">Billing freq</th>
                            <th class="column-renewal_terms-header">Term</th>
                        </template>
                        <th class="column-total-header">Total</th>
                        <th v-if="!preview" class="column-actions-header"></th>
                    </tr>
                </thead>
                <tbody>
                    <ChargeRow
                        v-for="(item, index) in items"
                        :key="item.id"
                        :item="item"
                        :index="index"
                        :code="code"
                        :preview="preview"
                    />
                </tbody>
            </table>
        </div>
    </div>
</template>
