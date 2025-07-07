<script setup>
import { toggle_is_active } from '@/Composables/handleActive.js';

const props = defineProps({
    data: Object,
    value: Number,
    route: String
});

</script>
<template>
    <div role="button" @click="toggle_is_active(props.route, props.data, )">
        <font-awesome-icon v-if="props.value" :icon="['fas', 'circle-check']" style="font-size: 1.125rem"
                           class="text-primary" />
        <font-awesome-icon v-else :icon="['far', 'circle-xmark']" style="font-size: 1.125rem"
                           class="text-secondary" />
    </div>
</template>
