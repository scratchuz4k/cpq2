<script setup>
import { quote } from '@composables/useQuote';
</script>
<template>
    <div class="quote-summary-panel" v-if="quote.items.length">
        <table
            id="table-final-total"
            class="quote-summary-table quote-summary-totals-table quote-final-total table mb-0"
        >
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
