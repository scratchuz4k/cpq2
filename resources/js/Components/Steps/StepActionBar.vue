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
    width: 100vw;
    margin-left: calc(50% - 50vw);
    background-color: white;
    border-top: 1px solid #e0e0e0;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    padding: 1rem 0;
}

.step-action-bar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-direction: row;
}

.step-action-bar-left,
.step-action-bar-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
</style>
