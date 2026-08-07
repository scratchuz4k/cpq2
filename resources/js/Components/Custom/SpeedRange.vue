<template>
    <Range
        v-bind="{
            id: 'speedSlider',
            mode: 'range',
            config: speedsliderConfig,
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

const speedFormatter = (value) => {
    let temp = value ?? 0;
    let number = temp;
    let suffix = 'Mbps';

    if (temp < 100) {
        number = Math.ceil(temp / 10) * 10;
    } else if (temp < 1000) {
        number = Math.ceil(temp / 100) * 100;
    } else {
        number = temp / 1000;
        suffix = 'Gbps';
        number = Math.round(number * 10) / 10;
    }

    return number + ' ' + suffix;
};

const speedsliderConfig = {
    start: props.modelValue ?? [100, 1000],
    connect: true,
    range: {
        min: [10, 10],
        '20%': [100, 100],
        '45%': [500, 100],
        '70%': [1000, 1000],
        max: [10000, 1000]
    },
    // tooltips: [{ to: speedFormatter }, { to: speedFormatter }],
    step: 100,
    pips: {
        mode: 'range',
        density: 5,
        format: {
            to: speedFormatter
        }
    }
};
</script>
