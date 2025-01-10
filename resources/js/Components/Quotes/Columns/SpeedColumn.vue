<script setup>
import format from "@/Composables/format.js";
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

    <p class="small" v-if="!isParent"> {{ format.bytes(item.data?.speed) }}</p>
    <p v-else class="badge border border-secondary" v-tooltip data-bs-toggle="tooltip" data-bs-placement="top" :title="column.name">
        <font-awesome-icon :icon="['fas', 'signal']"></font-awesome-icon> {{
            format.bytes(item.data?.speed) }}
    </p>
</template>
