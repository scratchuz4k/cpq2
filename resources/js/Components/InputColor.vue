<script setup>
import ColorPicker from 'primevue/colorpicker';
import { computed, ref, watch } from 'vue';
import _ from 'lodash';
import { isValidHexColor } from '../Composables/layouts.js';

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
    if (props.modelValue !== '' && !isValidHexColor(props.modelValue)) {
        notyf.error('Please enter a valid color');
        emit('update:modelValue', null);
        return;
    }
    emit('change');
}, 1000);

const cleanHexValue = (text) => {
    return text
        .replace(/^#/, '')
        .replace(/[^0-9a-fA-F]/g, '')
        .slice(0, 6);
};

const handleClipboardInput = (event) => {
    event.preventDefault();
    const clipboardData =
        event.type === 'drop' ? event.dataTransfer.getData('text') : event.clipboardData.getData('text');
    proxyValue.value = cleanHexValue(clipboardData);
    updateColor();
};

const handleInput = (event) => {
    proxyValue.value = cleanHexValue(event.target.value);
    updateColor();
};

const colorPickerClass = computed(() => ({
    'color-picker-empty': !proxyValue.value
}));
</script>

<template>
    <div class="d-flex">
        <div class="color-picker-wrapper">
            <ColorPicker
                v-model="proxyValue"
                :id="`${props.id}-picker`"
                :class="[colorPickerClass, 'rounded-3 me-2']"
                @change="updateColor"
            />
        </div>
        <div class="input-group">
            <span class="input-group-text" id="hex-hash">#</span>
            <input
                :id="`${props.id}-hex`"
                aria-describedby="hex-hash"
                type="text"
                name="color"
                class="form-control text-uppercase"
                v-model="proxyValue"
                maxlength="6"
                @input="handleInput"
                @paste="handleClipboardInput"
                @drop="handleClipboardInput"
                @dragover.prevent
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

.color-picker-empty .p-colorpicker-preview {
    background-color: transparent !important;
    border: 1px solid var(--bs-border-color) !important;
}
</style>
