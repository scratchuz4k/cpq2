<script setup>
defineProps({
    text: String
});
</script>
<template>
    <span class="hover-tooltip" :class="{ 'has-tooltip': text }" :tabindex="text ? 0 : undefined">
        <slot></slot>
        <span v-if="text" class="hover-tooltip-text" role="tooltip">{{ text }}</span>
    </span>
</template>
