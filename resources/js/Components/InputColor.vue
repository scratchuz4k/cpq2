<script setup>
import ColorPicker from 'primevue/colorpicker';
</script>

<template>
    <div class="d-flex justify-center my-3 input-color">
        <ColorPicker
            :pt="{
                preview: { class: 'input-color-preview' }
            }"
        />
    </div>
</template>

<style>
.p-colorpicker-preview.input-color-preview {
    height: 120px !important;
    width: 120px !important;
    align-items: center !important;
    border: none;
    border-radius: 10px !important;
    outline: none !important;
}
</style>
