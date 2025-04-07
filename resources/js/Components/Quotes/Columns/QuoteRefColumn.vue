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
    <p class="small" v-if="!isGroup">{{ item.data?.quoteId }}</p>
    <p v-else v-tooltip data-bs-toggle="tooltip" data-bs-placement="top" :title="column.name"
        class="badge border border-secondary">
        #{{ item.data?.quoteId }}
    </p>
</template>
