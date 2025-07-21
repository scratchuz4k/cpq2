<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: {
        required: true
    },
    value: {
        default: null
    },
    name: String
});

const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});
</script>

<template>
    <label class="card bg-light w-100" :class="{ 'border-primary': proxyValue === value }">
        <input type="radio" class="d-none" :name="name" :value="value" v-model="proxyValue" />
        <div class="card-body p-3 text-decoration-none">
            <slot />
        </div>
    </label>
</template>
<style scoped>
.card {
    cursor: pointer;
    height: 100%;
}
.border-primary {
    background-color: var(--color-purple-light) !important;
    border-color: var(--color-purple) !important;
    box-shadow:
        inset 0 0 1px #fff,
        inset 0 1px 7px #ebebeb,
        0 3px 6px -3px #bbb;
}
</style>
