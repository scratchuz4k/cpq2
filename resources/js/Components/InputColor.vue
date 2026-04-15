<script setup>
import ColorPicker from 'primevue/colorpicker';
import InputText from 'primevue/inputtext';
import { ref } from 'vue';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    modelValue: String,
    id: String
});

const emit = defineEmits(['update:modelValue', 'change']);
const color = ref(props.modelValue ?? null);
</script>

<template>
    <div class="d-flex">
        <div class="color-picker-wrapper">
            <ColorPicker v-model="color" :id="`${props.id}-picker`" class="rounded-3 me-2" @change="" />
        </div>
        <div class="color-input-wrapper form-control d-flex">
            <span>#</span>
            <input
                :id="`${props.id}-hex`"
                type="text"
                name="color"
                class="hex-color-input"
                v-model="color"
                maxlength="6"
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
