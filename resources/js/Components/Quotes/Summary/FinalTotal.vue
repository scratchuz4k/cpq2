<script setup>
import { computed } from 'vue';
import { quote } from '@composables/useQuote';
import { canViewDiscounts } from '@/Composables/useQuoteDiscount';

const showDiscounts = computed(() => canViewDiscounts());
</script>
<template>
    <div class="quote-summary-panel" v-if="quote.items.length">
        <table
            id="table-final-total"
            class="quote-summary-table quote-summary-totals-table quote-final-total table mb-0"
        >
            <thead>
                <tr>
                    <th class="column-name-header"></th>
                    <template v-if="showDiscounts">
                        <th class="column-discount-header">Discount</th>
                        <th class="column-discount-value-header">Discount value</th>
                    </template>
                    <th class="column-total-header">Total</th>
                    <th class="column-actions-header"></th>
                </tr>
            </thead>
            <tbody>
                <BreakdownRow
                    code="quote"
                    :find="() => quote.discounts.find((x) => x.entity?.uuid == quote.uuid)"
                    :payload="() => ({ code: 'quote-' + quote.id, entity: 'quote', entity_id: quote.id })"
                    :discountValue="(discount) => discount?.total ?? 0"
                    :total="() => quote.total"
                >
                    <template #label>Final total</template>
                </BreakdownRow>
            </tbody>
        </table>
    </div>
</template>
