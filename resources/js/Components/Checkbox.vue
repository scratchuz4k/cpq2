<script setup>
import { v4 as uuidv4 } from 'uuid';
import { computed, onMounted, watch } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`;
        }
    },
    checked: {
        type: [Boolean, Number, null],
        required: true
    }
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
    }
});
</script>

<template>
    <input
        :id="props.id"
        type="checkbox"
        v-model="proxyChecked"
        :indeterminate="proxyChecked === null"
        class="form-check-input me-2 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
    />
</template>
