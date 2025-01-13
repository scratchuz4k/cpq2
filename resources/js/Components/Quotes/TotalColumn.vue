<script setup>
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
    <p class="small" v-if="!isGroup" :id="'total-' + index">
        {{ GBP.format(item[column.value]) }}
    </p>
</template>
