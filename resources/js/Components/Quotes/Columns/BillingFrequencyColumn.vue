<script setup>
import { Select } from 'primevue';
import { temp } from '@composables/useQuoteItem';
import { quote } from '@composables/useQuote';
import { frequencyUnitOptions, abbreviateUnit } from '@/Composables/billingFrequency';
import { integerOnly, checkValue } from '@/Composables/input.js';

defineProps({
    code: String,
    column: Object,
    isGroup: Boolean,
    preview: Boolean,
    index: Number
});
const item = defineModel();
</script>
<template>
    <template v-if="!isGroup">
        <p class="small" v-if="!item.is_editing || quote.solution_type.code == 3">
            {{
                item.price_plan?.code == 'MONTHLY' && quote && item.billing_frequency_value
                    ? `${item.billing_frequency_value}${abbreviateUnit(item.billing_frequency_unit)}`
                    : ''
            }}
        </p>
        <div v-else-if="item.price_plan.code == 'MONTHLY'" class="d-flex" style="gap: 2px">
            <input
                :id="'input-' + code + '-' + index + '-billing_frequency_value'"
                class="form-control text-center p-0 px-1"
                type="number"
                min="1"
                max="999"
                @keydown="integerOnly"
                v-model="temp.billing_frequency_value"
                @input="checkValue"
            />
            <Select
                :id="'input-' + code + '-' + index + '-billing_frequency_unit'"
                v-model="temp.billing_frequency_unit"
                :options="frequencyUnitOptions"
                optionLabel="value"
                optionValue="name"
                class="w-100"
            />
        </div>
    </template>
</template>
