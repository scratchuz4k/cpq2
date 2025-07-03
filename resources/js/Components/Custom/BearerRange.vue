<template>
    <Range
        v-bind="{
            id: 'bearerSlider',
            config: bearerSliderConfig,
            options: props.options,
            disabled: props.disabled
        }"
        v-model="range"
    />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    options: Array,
    modelValue: Array,
    disabled: Boolean
});

const emit = defineEmits(['update:modelValue', 'change']);
const range = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
        emit('change', val);
    }
});

const bearerSpeedFormatter = (value) => {
    let temp = value ?? 0;
    let number = temp;
    let suffix = 'Mb';

    if (temp < 100) {
        number = Math.ceil(temp / 10) * 10;
    } else if (temp < 1000) {
        number = Math.ceil(temp / 100) * 100;
    } else {
        number = temp / 1000;
        suffix = 'Gb';
        number = Math.round(number * 10) / 10;
    }

    return number + ' ' + suffix;
};
const bearerSliderConfig = {
    snap: true,
    start: props.modelValue ?? [0, 10000],
    connect: true,
    range: {
        min: [100, 100],
        '70%': [1000, 1000],
        max: [10000, 10000]
    },
    // tooltips: [
    //     { to: bearerSpeedFormatter },
    //     { to: bearerSpeedFormatter },
    // ],
    step: 3,
    pips: {
        mode: 'range',
        density: 5,
        format: {
            to: bearerSpeedFormatter
        }
    }
};
</script>
