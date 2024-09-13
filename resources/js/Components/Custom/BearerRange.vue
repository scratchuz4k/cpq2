<template>
    <Range v-if="options.length" v-bind="{
        id: 'bearerSlider',
        config: bearerSliderConfig,
        options: props.options,

    }" v-model="range" />
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    options: Array,
    modelValue: Object
})
const emit = defineEmits(["update:modelValue"])

const range = ref([])
watch(range, () => {
    emit("update:modelValue", range.value)
})


const bearerSpeedFormatter = (value) => {
    let number = value;
    let suffix = "Mb";
    let rounding = 10;

    if (value < 1000) {
        number = value;
    } else if (value < 10000) {
        number = value / 1000;
        suffix = "Gb";
        rounding = 1;
    } else {
        number = value / 1000;
        suffix = "Gb";
        rounding = 1;
    }

    let result =
        parseInt(Math.ceil(number / rounding) * rounding) +
        " " +
        suffix;
    return result;
}
const bearerSliderConfig = {
    snap: true,
    start: [0, 10000],
    connect: true,
    range: {
        min: [100, 100],
        "70%": [1000, 1000],
        max: [10000, 10000],
    },
    tooltips: [
        { to: bearerSpeedFormatter },
        { to: bearerSpeedFormatter },
    ],
    step: 3,
    pips: {
        mode: "range",
        density: 0,
        format: {
            to: bearerSpeedFormatter,
        },
    },
}
</script>