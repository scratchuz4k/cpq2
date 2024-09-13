<template>
    <div :id="id" class="mt-2 slider"></div>
</template>
<script setup>
import noUiSlider from 'nouislider/dist/nouislider.mjs';
import 'nouislider/dist/nouislider.css';
import { onMounted, ref } from 'vue';
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
    config: Object,
    modelValue: Object
})
const emit = defineEmits(['update:modelValue'])

const range = props.range ? props.range : { min: 0, max: props.options.length - 1, }
const step = props.step ? props.step : 1
const mode = props.mode ? props.mode : "steps"

onMounted(() => {
    let slider = document.getElementById(props.id)
    let conf = props.config ? props.config :
        {
            start: [0, props.options.length - 1],
            connect: true,
            range: range,
            step: step,
            pips: {
                mode: mode,
            },
        }
    noUiSlider.create(slider, conf);
    slider.noUiSlider.on('change', () => {
        let items = slider.noUiSlider.get()
        Object.keys(items).forEach((key) => {
            items[key] = items[key] * 1
        })
        emit('update:modelValue', items)
    });
})
</script>
