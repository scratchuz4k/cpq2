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
