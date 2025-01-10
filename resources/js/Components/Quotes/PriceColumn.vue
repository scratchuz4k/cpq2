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
        <p class="small" v-if="!isParent">{{ GBP.format(item.price) }}</p>
</template>
