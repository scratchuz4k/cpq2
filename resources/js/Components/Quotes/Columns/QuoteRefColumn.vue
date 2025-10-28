<script setup>
import { quote } from '@composables/useQuote';
defineProps({
    code: String,
    column: Object,
    isGroup: Boolean,
    preview: Boolean,
    index: Number
});
const item = defineModel();

const getRef = (item) => {
    if (quote.data?.quoteReference) {
        return quote.data?.quoteReference;
    }
    return item.data?.quoteId;
};
</script>
<template>
    <p class="small" v-if="!isGroup">{{ getRef(item) }}</p>
    <p
        v-else
        v-tooltip
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        :title="column.name"
        class="badge border border-secondary"
    >
        #{{ getRef(item) }}
    </p>
</template>
