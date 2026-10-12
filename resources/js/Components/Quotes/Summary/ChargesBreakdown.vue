<script setup>
import { computed } from 'vue';
import { quote } from '@composables/useQuote';
import { canViewDiscounts } from '@/Composables/useQuoteDiscount';

const billingFrequencies = [
    { unit: 'YEARS', label: 'Annually recurring total' },
    { unit: 'MONTHS', label: 'Monthly recurring total' },
    { unit: 'DAYS', label: 'Daily recurring total' }
];

const oneOffItems = computed(() => quote.items.filter((x) => x.price_plan?.code == 'SINGLE'));
const recurringItems = (unit) =>
    quote.items.filter((x) => x.price_plan?.code == 'MONTHLY' && (x.billing_frequency_unit ?? 'MONTHS') == unit);

// sum of the "Total" column of the charges tables (price per billing period)
const subtotal = (items) => items.reduce((acc, item) => (acc += item.quantity * item.price), 0);

const pricePlanDiscount = (code, unit = null) =>
    quote.discounts.find((x) => x.entity?.code == code && (x.billing_frequency_unit ?? null) == unit);

const pricePlanPayload = (items, unit = null) => {
    let pricePlan = items[0].price_plan;
    return {
        code: pricePlan.code,
        entity: 'price_plans',
        entity_id: pricePlan.id,
        billing_frequency_unit: unit
    };
};

const showDiscounts = computed(() => canViewDiscounts());
const hasRows = computed(
    () => oneOffItems.value.length || billingFrequencies.some((x) => recurringItems(x.unit).length)
);
</script>
<template>
    <div class="quote-summary-panel">
        <h3 class="quote-summary-title">Charges breakdown</h3>
        <div class="table-responsive-lg">
            <table id="table-breakdown" class="quote-summary-table quote-summary-totals-table table">
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
                    <template v-for="frequency in billingFrequencies" :key="frequency.unit">
                        <BreakdownRow
                            v-if="recurringItems(frequency.unit).length"
                            :code="frequency.unit"
                            :label="frequency.label"
                            :find="() => pricePlanDiscount('MONTHLY', frequency.unit)"
                            :payload="() => pricePlanPayload(recurringItems(frequency.unit), frequency.unit)"
                            :discountValue="(discount) => discount?.value ?? 0"
                            :total="(discount) => subtotal(recurringItems(frequency.unit)) - (discount?.value ?? 0)"
                        />
                    </template>
                    <BreakdownRow
                        v-if="oneOffItems.length"
                        code="one_off"
                        label="One-off total"
                        :find="() => pricePlanDiscount('SINGLE')"
                        :payload="() => pricePlanPayload(oneOffItems)"
                        :discountValue="(discount) => discount?.value ?? 0"
                        :total="(discount) => subtotal(oneOffItems) - (discount?.value ?? 0)"
                    />
                </tbody>
            </table>
        </div>
    </div>
</template>
