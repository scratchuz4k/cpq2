<script setup>
import ColorPicker from 'primevue/colorpicker';
import { computed, ref } from 'vue';
import _ from 'lodash';

const props = defineProps({
    modelValue: String,
    id: String
});

const emit = defineEmits(['update:modelValue', 'change']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const updateColor = _.debounce(() => {
    const test = isLightColor(proxyValue.value);
    emit('change');
}, 1000);

/**
 * Determine if a color is a light color
 */
const isLightColor = (color) => {
    let r, g, b;

    // Check the format of the color, HEX or RGB?
    if (color.match(/^rgb/)) {
        // If HEX --> store the red, green, blue values in separate variables
        const rgb = color.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);

        r = rgb[1];
        g = rgb[2];
        b = rgb[3];
    } else {
        color = color.replace(/^\s*#|\s*$/g, '');

        // Convert 3 char codes -> 6, e.g. `E0F` -> `EE00FF`
        if (color.length == 3) {
            color = color.replace(/(.)/g, '$1$1');
        }
        // If RGB --> Convert it to HEX: http://gist.github.com/983661
        const hexColor = +('0x' + color.slice(1).replace(color.length < 5 && /./g, '$&$&'));

        r = hexColor >> 16;
        g = (hexColor >> 8) & 255;
        b = hexColor & 255;
    }

    // HSP equation from http://alienryderflex.com/hsp.html
    const hsp = Math.sqrt(0.299 * (r * r) + 0.587 * (g * g) + 0.114 * (b * b));

    // Using the HSP value, determine whether the color is light or dark
    // > 127.5 is 'light', <= 127.5 is 'dark'

    return hsp > 127.5;
};
</script>

<template>
    <div class="d-flex">
        <div class="color-picker-wrapper">
            <ColorPicker v-model="proxyValue" :id="`${props.id}-picker`" class="rounded-3 me-2" @change="updateColor" />
        </div>
        <div class="color-input-wrapper form-control d-flex">
            <span>#</span>
            <input
                :id="`${props.id}-hex`"
                type="text"
                name="color"
                class="hex-color-input"
                v-model="proxyValue"
                maxlength="6"
                @input="updateColor"
            />
        </div>
    </div>
</template>

<style>
.p-colorpicker-preview {
    height: 40px !important;
    width: 40px !important;
    align-items: center !important;
    border: none;
    border-radius: 5px !important;
    outline: none !important;
}

.color-input-wrapper {
    display: flex;
    align-items: center;
    border: 1px solid #ccc;
    padding: 5px;
}

.color-input-wrapper .hex-color-input {
    border: none;
    outline: none;
}
</style>
