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
        <p class="small" v-if="!isGroup">{{ format.GBP(item.price) }}</p>
</template>
