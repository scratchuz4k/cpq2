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
    <input :id="props.id" type="checkbox" v-model="proxyChecked" :indeterminate="proxyChecked === null" />
</template>
<style scoped>
input[type='checkbox'] {
    width: 20px;
    height: 20px;
    margin: 0;
    border-width: 2px;
    cursor: pointer;
}

input[type='checkbox']:checked {
    background-color: var(--color-purple) !important;
    border-color: var(--color-purple) !important;
}
input[type='checkbox']:focus {
    box-shadow: var(--color-purple) 0px 1px 4px;
    border-color: var(--color-purple-light) !important;
}
</style>
