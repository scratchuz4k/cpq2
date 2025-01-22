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
    <p class="small">{{ item.supplier?.name }}</p>
</template>
