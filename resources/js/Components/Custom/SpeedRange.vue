<template>
    <Range v-if="options.length" v-bind="{
        id: 'speedSlider',
        mode: 'range',
        config: speedsliderConfig,
        options: props.options
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


const speedFormatter = (value) => {
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
        suffix = "Gb+";
        rounding = 1;
    }

    let result =
        parseInt(Math.ceil(number / rounding) * rounding) +
        " " +
        suffix;
    return result;
}

const speedsliderConfig = {
    start: [0, 10000],
    connect: true,
    range: {
        min: [0, 10],
        "20%": [100, 100],
        "45%": [500, 100],
        "70%": [1000, 1000],
        max: [10000, 1000],
    },
    tooltips: [{ to: speedFormatter }, { to: speedFormatter }],
    step: 100,
    pips: {
        mode: "range",
        density: 5,
        format: {
            to: speedFormatter,
        },
    },
}
</script>
