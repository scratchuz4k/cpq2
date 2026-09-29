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
                item.price_plan?.code == 'MONTHLY' && quote && item.renewal_terms_value
                    ? formatFrequency(item.renewal_terms_value, item.renewal_terms_unit)
                    : ''
            }}
        </p>
    </template>
</template>
