<script setup>
import { Select } from 'primevue';
import { temp } from '@composables/useQuoteItem';
import { quote } from '@composables/useQuote';
import { frequencyUnitOptions } from '@/Composables/billingFrequency';
import { integerOnly, checkValue } from '@/Composables/input.js';

defineProps({
    code: String,
    column: Object,
    isGroup: Boolean,
    preview: Boolean,
    index: Number
});
const item = defineModel();

const unitLabel = (unit) => (unit ? unit.charAt(0) + unit.slice(1).toLowerCase() : '');
</script>
<template>
    <template v-if="!isGroup">
        <p class="small" v-if="!item.is_editing || quote.solution_type.code == 3">
            {{
                item.price_plan?.code == 'MONTHLY' && quote && item.renewal_terms_value
                    ? `${item.renewal_terms_value} ${unitLabel(item.renewal_terms_unit)}`
                    : ''
            }}
        </p>
        <div v-else-if="item.price_plan.code == 'MONTHLY'" class="d-flex" style="gap: 2px">
            <input
                :id="'input-' + code + '-' + index + '-renewal_terms_value'"
                class="form-control text-center p-0 px-1"
                type="number"
                min="1"
                max="999"
                @keydown="integerOnly"
                v-model="temp.renewal_terms_value"
                @input="checkValue"
            />
            <Select
                :id="'input-' + code + '-' + index + '-renewal_terms_unit'"
                v-model="temp.renewal_terms_unit"
                :options="frequencyUnitOptions"
                optionLabel="value"
                optionValue="name"
                class="w-100"
            />
        </div>
    </template>
</template>
