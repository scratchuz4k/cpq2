<template>
    <div :id="id"></div>
</template>
<script setup>
import noUiSlider from 'nouislider/dist/nouislider.mjs';
import 'nouislider/dist/nouislider.css';
import { onMounted } from 'vue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`
        }
    },
    options: Array,
    range: Object,
    step: Number,
    mode: String,
    modelValue: Object
})
const emit = defineEmits(['update:modelValue'])
const bytesFormatter = (value) => {
    let temp = Math.ceil(value)
    if ((props.options[temp] / 1000) >= 1) {
        return Math.ceil((props.options[temp] / 1000)) + " Gb"
    }
    return props.options[temp] + " Mb"
}
const range = props.range ? props.range : { min: 0, max: props.options.length - 1, }
const step = props.step ? props.step : 1
const mode = props.mode ? props.mode :  "steps"

onMounted(() => {
    let slider = document.getElementById(props.id)
    noUiSlider.create(slider, {
        start: [0, props.options.length - 1],
        connect: true,
        range: range,
        tooltips: [{ to: bytesFormatter }, { to: bytesFormatter }],
        step: step,
        pips: {
            mode: mode,
            format: {
                to: bytesFormatter,
            },
        },
    });
    slider.noUiSlider.on('change', () => {
        let items = slider.noUiSlider.get()
        Object.keys(items).forEach((key) => {
            items[key] = items[key] * 1
        })
        emit('update:modelValue', items)
    });
})
</script>
