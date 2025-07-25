<template>
    <div class="modal" :class="{ 'show-modal': props.modelValue }" @click.self="toggleModal()">
        <div class="content container mt-5 p-0">
            <div class="header">
                <slot name="header"></slot>
            </div>
            <div class="body">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import { watch } from 'vue';

const props = defineProps({
    modelValue: {
        required: true
    },
    title: {
        required: false,
        default: null
    }
});
const emits = defineEmits(['update:modelValue']);

const toggleModal = () => {
    emits('update:modelValue', false);
};
</script>

<style scoped>
.modal {
    display: block;
    visibility: none;
    pointer-events: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1200;
    background-color: rgba(0, 0, 0, 0.4);
    transition: 0.3s all ease-in-out;
    opacity: 0;
}

.show-modal {
    visibility: visible;
    pointer-events: auto;
    opacity: 1;
}

.content {
    background-color: white;
    transition: 0.3s all ease-in-out;
    transform: translateY(-150%);
    max-height: 80%;
    overflow-y: auto;
    overflow-x: hidden;
}

.show-modal .content {
    transform: translateY(0);
    visibility: visible;
    pointer-events: auto;
    scale: 1;
}

.header {
    padding: 1rem 1.5rem 1rem;
    background-color: var(--dark-purple);
    height: 100px;
    display: flex;
    align-items: center;
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
}

.body {
    padding: 20px;
}

.footer {
    background-color: var(--dark-purple);
    padding: 1rem 1.5rem 2rem;
}
</style>
