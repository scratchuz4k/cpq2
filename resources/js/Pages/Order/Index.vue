<template>
    {{ orders }}
</template>

<script setup>

defineProps({
    orders: {
        type: Array,
    },
});
</script>