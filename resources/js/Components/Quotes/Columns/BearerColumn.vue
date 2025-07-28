<script setup>
import format from '@/Composables/format.js';
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
    <p class="small" v-if="!isGroup">{{ format.speed(item.data?.bearer) }}</p>
    <p
        v-else
        class="badge border border-secondary"
        v-tooltip
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        :title="column.name"
    >
        <font-awesome-icon :icon="['fas', 'tower-cell']"></font-awesome-icon> {{ format.speed(item.data?.bearer) }}
    </p>
</template>
