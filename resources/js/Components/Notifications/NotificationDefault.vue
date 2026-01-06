<template>
    <div class="w-100 text-start" style="font-size: 14px">
        <p class="small">{{ props.notification.description }}</p>
    </div>
</template>
<script setup>
const props = defineProps({
    notification: {
        type: Object,
        required: true
    }
});
</script>
<style>
.bg-whitesmoke {
    background-color: whitesmoke;
}
</style>
