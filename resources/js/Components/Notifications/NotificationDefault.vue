<template>
    <div class="w-100 text-start">
        {{ props.notification.description }}
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
