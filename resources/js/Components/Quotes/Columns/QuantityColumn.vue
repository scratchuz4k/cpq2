<script setup>
import { temp } from '@composables/useQuoteItem'
defineProps({
    code: String,
    column: Object,
    isGroup: Boolean,
    preview: Boolean,
    index: Number
})
const item = defineModel()

</script>
<template>
    <template v-if="!isGroup">
        <p class="small" v-if="!item.is_editing" :id="code + '-' + index + '-quantity'">
            x{{ item.quantity }}
        </p>
        <input :id="'input-' + code + '-' + index + '-quantity'" v-else class="form-control text-center p-0 px-1"
            type="number" min="0" max="100" v-model="temp.quantity">
    </template>
</template>
