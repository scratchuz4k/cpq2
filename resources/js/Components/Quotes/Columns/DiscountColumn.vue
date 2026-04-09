<script setup>
import { temp } from '@composables/useQuoteItem';
import { checkValue } from '@/Composables/input.js';
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
    <div v-if="!isGroup">
        <p class="small" v-if="!item.is_editing" :id="code + '-discount-' + index">{{ item[column.value] }} %</p>
        <input
            v-else
            :id="'input-' + code + '-' + index + '-discount'"
            class="form-control text-center p-0 px-1"
            type="number"
            v-model="temp.discount"
            min="0"
            max="100"
            @input="checkValue"
        />
    </div>
</template>
