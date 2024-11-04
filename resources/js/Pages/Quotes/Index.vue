<template>
    {{ quotes }}
</template>

<script setup>

defineProps({
    quotes: {
        type: Array,
    },
});
</script>
