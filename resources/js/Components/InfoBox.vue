<script setup>
const props = defineProps({
    severity: {
        type: String,
        default: 'default'
    },
})

</script>
<template>
    <div class="info-box-primary d-flex mb-4 py-3 pe-5">
        <div class="px-3">
            <font-awesome-icon :icon="['fas', 'circle-info']" :style="{ fontSize: '20px' }" />
        </div>
        <div class="w-100">
            <slot></slot>
        </div>
    </div>
</template>
<style>
.price_header {
    background: var(--tertiary-light);
    border-radius: var(--radius);
    padding: 15px;
}
</style>
