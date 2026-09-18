<script setup>
import { reactive } from 'vue';
import { quote, getQuote } from '@composables/useQuote';
import { createQuoteItem } from '@/Composables/useQuoteItem';
import TableTotal from './TableTotal.vue';
import { can, hasRole } from '@/Composables/useAuth';

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
    let total = quote.items
        .filter((x) => x.price_plan && x.price_plan.code == key)
        .reduce((acc, item) => (acc += item.quantity * item.price), 0);
    let tempDiscount = quote.discounts.find((x) => x.entity?.code == key);
    return total ? total - (tempDiscount?.value ?? 0) : 0;
};

const quoteTotalMonths = (key) => {
    let total = quote.items
        .filter((x) => x.price_plan && x.price_plan.code == key)
        .reduce((acc, item) => (acc += item.total), 0);
    let tempDiscount = quote.discounts.find((x) => x.entity?.code == key);

    return total ? total - (tempDiscount?.total ?? 0) : 0;
};

const filterColumns = (columns, code) => {
    if (code == 'SINGLE') {
        return columns.filter((x) => x.value != 'renewal_terms' && x.value != 'billing_frequency');
    }
    return columns;
};

const editableColumns = (columns, code) => {
    let temp = filterColumns(columns, code);
    let editable = ['name', 'price', 'discount', 'quantity'];
    temp.forEach((e) => {
        e.is_editable = editable.includes(e.value);
    });
    return temp;
};
</script>
<template>
    <div class="dealmaker-panel" v-for="pricePlan in pricePlans">
        <div class="mobile-price-group-heading d-flex justify-content-between align-items-center">
            <h3>{{ pricePlan.name }} charges</h3>
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
                        <th style="width: 60px" class="text-center" v-if="!preview"></th>
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
                    <QuoteDiscountRow
                        v-if="
                            quote.items.filter((x) => x.price_plan.code == pricePlan.code).length > 0 &&
                            (can('viewAny quote_discount') || hasRole('Super Admin'))
                        "
                        :preview="props.preview"
                        :pricePlan="pricePlan"
                        :columns="filterColumns(columns, pricePlan.code)"
                    ></QuoteDiscountRow>
                </tbody>
                <tfoot>
                    <tr>
                        <th :colspan="filterColumns(columns, pricePlan.code).length - 1">
                            <p class="mb-0">{{ pricePlan.name === 'Recurring' ? 'Monthly Subtotal' : 'Subtotal' }}</p>
                        </th>
                        <th class="text-end" :id="pricePlanskeys[pricePlan.code] + '-total'">
                            <span v-if="props.preview || pricePlan.name != 'Recurring'">
                                {{ format.GBP(quoteTotal(pricePlan.code)) }}
                            </span>

                            <div v-else class="cpq-tooltip">
                                {{ format.GBP(quoteTotal(pricePlan.code)) }}
                                <span class="cpq-tooltip-text">
                                    Overall Cost:<br />{{ format.GBP(quoteTotalMonths(pricePlan.code)) }}
                                </span>
                            </div>
                        </th>
                        <th v-if="!preview"></th>
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
