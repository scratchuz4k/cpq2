<script setup>
import { usePage } from '@inertiajs/vue3';
import { quote } from '@composables/useQuote';
import format from '@/Composables/format';

const tenant = usePage().props.tenant;
const layout = JSON.parse(tenant.configurations?.calculator_layout ?? '[]');
const show = (key) => !layout.length || layout.includes(key);
</script>
<template>
    <div class="quote-summary-panel quote-summary-sidebar-panel commission-calculator">
        <h3 class="quote-summary-title">Profitability calculator</h3>
        <p class="small mb-3">
            The values for quote commission, overall margin, and contract total are displayed below.
        </p>
        <div class="profitability-values">
            <div v-if="show('MARGIN')" class="margin d-flex justify-content-between">
                <p class="label">
                    Margin
                    <span id="margin-percentage">({{ format.decimalPlaces(quote.margin) }}%)</span>
                </p>
                <p id="margin-value" class="value">{{ format.GBP(quote.total * (quote.margin / 100)) }}</p>
            </div>
            <div v-if="show('COMMISSION')" class="comission d-flex justify-content-between">
                <p class="label">
                    Commission
                    <span id="commission-percentage">({{ format.decimalPlaces(quote.commission_rate) }}%)</span>
                </p>
                <p id="commission-value" class="value">{{ format.GBP(quote.commission_value) }}</p>
            </div>
            <div v-if="show('TOTAL')" class="total d-flex justify-content-between">
                <p class="label">Contract total</p>
                <p id="total-value" class="value">{{ format.GBP(quote.total) }}</p>
            </div>
        </div>
    </div>
</template>
