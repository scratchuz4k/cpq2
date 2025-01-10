<script setup>
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
    <p class="small" v-if="!isParent" :id="'total-' + index">
        {{ GBP.format(item[column.value]) }}
    </p>
</template>
