<script setup>
import { reactive } from 'vue';
import { quote, getQuote } from '@composables/useQuote';
import { createQuoteItem } from '@/Composables/useQuoteItem';
import TableTotal from './TableTotal.vue';

const props = defineProps({
    pricePlans: Array,
    preview: Boolean
});

const pricePlanskeys = { SINGLE: 'one_off', MONTHLY: 'recurring' };
let columns = quote.solution_type?.table?.columns ?? [];

if (props.preview) {
    columns = columns.filter((x) => {
        return x.value != 'margin';
    });
}

const quoteTotal = (key) => {
    return quote.items
        .filter((x) => x.price_plan && x.price_plan.code == key)
        .reduce((acc, item) => (acc += item.quantity * item.price), 0);
};

const filterColumns = (columns, code) => {
    if (code == 'SINGLE') {
        return columns.filter((x) => x.value != 'contract_length');
    }
    return columns;
};

const editableColumns = (columns, code) => {
    let temp = filterColumns(columns, code);
    let editable = ['name', 'price', 'discount', 'contract_length', 'quantity'];
    temp.forEach((e) => {
        e.is_editable = editable.includes(e.value);
    });
    return temp;
};

emitter.on('quote-items:updated', () => {
    getQuote(quote.id);
});
</script>
<template>
    <div class="dealmaker-panel" v-for="pricePlan in pricePlans">
        <div class="mobile-price-group-heading d-flex justify-content-between align-items-center">
            <h3>{{ pricePlan.name }} charges</h3>
            <!-- <h3 :id="pricePlanskeys[pricePlan.code] + '-total'">
                {{ format.GBP(quoteTotal(pricePlan.code)) }}
            </h3> -->
        </div>
        <div>
            <table :id="'table-' + pricePlanskeys[pricePlan.code]" class="table-dealmaker table table-bordered">
                <thead>
                    <tr>
                        <th
                            v-for="column in filterColumns(columns, pricePlan.code)"
                            :class="[`column-${column.value}-header`]"
                        >
                            {{ column.name }}
                        </th>
                        <th style="width: 60px" class="text-center" v-if="!preview && !quote.is_blocked"></th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(item, index) in quote.items.filter((x) => x.price_plan.code == pricePlan.code)">
                        <TableItem
                            :item="item"
                            :index="index"
                            :columns="filterColumns(columns, pricePlan.code)"
                            :preview="preview"
                            :groupColumns="[]"
                            :code="pricePlanskeys[pricePlan.code]"
                        >
                        </TableItem>
                    </template>
                </tbody>
                <tfoot>
                    <QuoteDiscountRow
                        v-if="quote.items.filter((x) => x.price_plan.code == pricePlan.code).length > 0"
                        :preview="props.preview"
                        :pricePlan="pricePlan"
                    ></QuoteDiscountRow>
                    <tr>
                        <td :colspan="filterColumns(columns, pricePlan.code).length - 1">
                            <p class="mb-0 fw-bold">Subtotal</p>
                        </td>
                        <td class="text-end fw-bold" :id="pricePlanskeys[pricePlan.code] + '-total'">
                            {{ format.GBP(quoteTotal(pricePlan.code)) }}
                        </td>
                        <td v-if="!props.preview"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="dealmaker-panel">
        <TableTotal :preview="props.preview"></TableTotal>
    </div>
</template>

<style scoped></style>
