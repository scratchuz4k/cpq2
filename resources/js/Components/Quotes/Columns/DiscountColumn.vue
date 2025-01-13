<script setup>
import { temp } from '@composables/useQuoteItem'
defineProps({
    code: String,
    column: Object,
    isParent: Boolean,
    preview: Boolean,
    index: Number
})
const item = defineModel()
</script>
<template>
    <div v-if="!isParent">
        <p class="small" v-if="!item.is_editing" :id="code + '-discount-' + index">
            {{ item[column.value] }} %
        </p>
        <input v-else :id="'input-' + code + '-' + index + '-discount'" class="form-control text-center p-0 px-1"
            type="number" v-model="temp.discount" min="0">
    </div>
</template>
