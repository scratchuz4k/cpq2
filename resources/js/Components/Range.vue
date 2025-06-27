<template>
    <div :id="id" class="mt-2 slider" :disabled="props.disabled ? true : null"></div>
</template>
<script setup>
import noUiSlider from 'nouislider/dist/nouislider.mjs';
import 'nouislider/dist/nouislider.css';
import { onMounted, computed, watch } from 'vue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`;
        }
    },
    disabled: Boolean,
    options: Array,
    range: Object,
    step: Number,
    mode: String,
    config: Object,
    modelValue: Array
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const range = props.range ? props.range : { min: 0, max: props.options.length - 1 };
const step = props.step ? props.step : 1;
const mode = props.mode ? props.mode : 'steps';
const conf = props.config
    ? props.config
    : {
          start: proxyValue ?? [0, props.options.length - 1],
          connect: true,
          range: range,
          step: step,
          pips: {
              mode: mode
          }
      };

onMounted(() => {
    let slider = document.getElementById(props.id);

    noUiSlider.create(slider, conf);
    slider.noUiSlider.on('change', () => {
        let items = slider.noUiSlider.get();
        Object.keys(items).forEach((key) => {
            items[key] = items[key] * 1;
        });
        proxyValue.value = items;
    });
});

watch(
    () => props.modelValue,
    () => {
        let slider = document.getElementById(props.id);
        proxyValue.value.forEach((value, index) => {
            slider.noUiSlider.setHandle(index, value);
        });
    },
    { deep: true }
);
</script>
<style>
.noUi-connects {
    background: var(--color-purple-light);
}
.noUi-connect {
    background: var(--color-purple);
}
.noUi-value {
    font-size: 0.75rem;
    padding-top: 0.375rem;
}
</style>
