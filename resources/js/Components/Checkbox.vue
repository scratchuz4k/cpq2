<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean, Number],
        required: true,
    },
    value: {
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        if (typeof props.checked === 'number') {
            return props.checked === 1;
        }

        return props.checked;
    },

    set(val) {
        if (typeof props.checked === 'number') {
            emit('update:checked', val ? 1 : 0);
        } else {
            emit('update:checked', val);
        }
    },
});
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
    />
</template>
