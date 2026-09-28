<script setup>
import { quote } from '@composables/useQuote';
import { formatFrequency } from '@/Composables/billingFrequency';

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
        <p class="small text-center">
            {{
                item.price_plan?.code == 'MONTHLY' && quote && item.billing_frequency_value
                    ? formatFrequency(item.billing_frequency_value, item.billing_frequency_unit)
                    : ''
            }}
        </p>
    </template>
</template>
