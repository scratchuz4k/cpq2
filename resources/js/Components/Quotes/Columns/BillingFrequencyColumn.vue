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

const display = (item) => {
    if (!item.renewal_terms_value || !item.renewal_terms_unit) {
        return '';
    }

    let label = `${item.renewal_terms_value} ${unitLabel(item.renewal_terms_unit)}`;

    if (item.billing_frequency_value && item.billing_frequency_unit) {
        label += ` / every ${item.billing_frequency_value} ${unitLabel(item.billing_frequency_unit)}`;
    }

    return label;
};
</script>
<template>
    <template v-if="!isGroup">
        <p class="small" v-if="!item.is_editing || quote.solution_type.code == 3">
            {{ item.price_plan?.code == 'MONTHLY' && quote ? display(item) : '' }}
        </p>
        <div v-else-if="item.price_plan.code == 'MONTHLY'" class="d-flex flex-column" style="gap: 2px">
            <div class="d-flex" style="gap: 2px">
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
            <div class="d-flex" style="gap: 2px">
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
        </div>
    </template>
</template>
