<script setup>
const props = defineProps({
    number: String,
    info_text: String
});
</script>

<template>
    <span class="cpq-tooltip border-0">
        <span class="badge badge-primary rounded-pill ms-2">{{ props.number }}</span>
        <span class="cpq-tooltip-text">
            <small>{{ props.info_text }}</small>
        </span>
    </span>
</template>

<style scoped></style>
