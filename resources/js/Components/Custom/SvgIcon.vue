<script setup>
defineProps({
    name: { type: String, required: true },
    class: { type: String, default: 'w-5 h-5' },
    spritePath: { type: String, default: '/build/assets/icons-sprite.svg' }
});
</script>

<template>
    <svg :class="class" fill="currentColor" aria-hidden="true">
        <use :xlink:href="`${spritePath}#icon-${name}`" />
    </svg>
</template>
