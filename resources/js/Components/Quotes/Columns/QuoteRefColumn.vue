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
    <p class="small" v-if="!isParent">{{ item.data?.productCode }}</p>
    <p v-else v-tooltip data-bs-toggle="tooltip" data-bs-placement="top" :title="column.name"
        class="badge border border-secondary">
        #{{ item.data?.productCode }}
    </p>
</template>
