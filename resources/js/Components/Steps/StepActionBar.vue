<script setup>
defineProps({
    show: {
        type: Boolean,
        default: true
    }
});
</script>

<template>
    <div v-if="show" class="step-action-bar">
        <div class="container">
            <div class="step-action-bar-content">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<style scoped>
.step-action-bar {
    position: sticky;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: white;
    border-top: 1px solid #e0e0e0;
    padding: 1rem 0;
    z-index: 999;
}

.step-action-bar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0;
    flex-direction: row;
}
</style>
