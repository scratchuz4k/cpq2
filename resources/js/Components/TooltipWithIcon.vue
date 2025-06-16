<script setup>

const props = defineProps({
    info_text: String
})

</script>

<template>
    <span class="cpq-tooltip border-0">
        <font-awesome-icon :icon="['fas', 'circle-info']"/>
        <span class="cpq-tooltip-text">
            <small>{{ props.info_text }}</small>
        </span>
</span>
</template>

<style scoped>

</style>
