<script setup>

const props = defineProps({
    value: String
})

</script>

<template>
    <span class="badge rounded-pill m-1">
        {{ value }}

    </span>
</template>

<style scoped>
    span {
        background-color: #F5E5F3;
        color: #A34996;
    }
</style>
