<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <strong>{{ message }}</strong>
    </div>
</template>
