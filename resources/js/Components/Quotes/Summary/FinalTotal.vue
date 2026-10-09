<script setup>
import { quote } from '@composables/useQuote';

defineProps({
    // read-only customer view (preview / shared link / PDF): discounts always shown, no actions
    preview: Boolean
});
</script>
<template>
    <div class="quote-summary-panel">
        <table
            id="table-final-total"
            class="quote-summary-table quote-summary-totals-table quote-final-total table mb-0"
        >
            <colgroup>
                <col class="column-name-header text-start" style="width: 72%" />
                <col class="column-discount-header" style="width: 8%" />
                <col class="column-total-header" style="width: 17%" />
                <col v-if="!preview" style="width: 60px" class="text-center" />
            </colgroup>
            <tbody>
                <BreakdownRow
                    code="quote"
                    :preview="preview"
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
