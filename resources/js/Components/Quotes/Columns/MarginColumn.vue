<script setup>
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
    <p class="small" v-if="!isGroup" :id="code + '-' + index + '-margin'" style="white-space: nowrap">
        {{ format.GBP(item[column.value]) }}
    </p>
</template>
